<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Data\ThemeSnapshot;
use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Theme data kept in the database, for hosts whose instances share no
 * filesystem.
 *
 * Editor documents are rows, and each publish is a version holding every
 * document it published; the newest version is live. Everything reads theme
 * data from `visual://` paths, which `VisualStreamWrapper` answers from here.
 *
 * Instances learn of each other's writes through the cache, which must be one
 * they share. A theme's editor version and live version id are kept there for
 * a few minutes. What they point at never changes, so it is kept for a day:
 * the index of a version, and every document's contents under their hash.
 *
 * A write puts what it wrote into the cache and into what this request has
 * read, so neither this request nor the next has to read it back.
 */
class DatabaseThemeDataStore implements ThemeDataStore
{
    public const SCHEME = 'visual';

    /**
     * How long instances may go on trusting a cached pointer. Writers replace
     * it at once, so this only bounds how long a lost race can last.
     */
    protected const POINTER_TTL = 300;

    protected const ENTRY_TTL = 86400;

    /** @var array<string, array{version: int, files: array<string, array{size: int, stamp: int, hash: string}>}> */
    protected array $indexes = [];

    /** @var array<string, string> Contents by hash. */
    protected array $contents = [];

    public function put(string $theme, string $relativePath, string $contents): void
    {
        $scope = Visual::storageScope();
        $now = now();
        $entry = ['size' => strlen($contents), 'stamp' => $now->getTimestamp(), 'hash' => md5($contents)];

        $state = $this->connection()->transaction(function () use ($scope, $theme, $relativePath, $contents, $entry, $now) {
            $this->connection()->table('visual_theme_documents')->upsert(
                [[
                    'scope' => $scope,
                    'theme' => $theme,
                    'path' => $relativePath,
                    'content' => $contents,
                    'created_at' => $now,
                    'updated_at' => $now,
                ] + $entry],
                ['scope', 'theme', 'path'],
                ['content', 'hash', 'size', 'stamp', 'updated_at'],
            );

            return $this->recordEdit($scope, $theme, ['edited_at' => $now]);
        });

        $this->editorWritten($scope, $theme, (int) $state->editor_version, function (array $files) use ($relativePath, $entry) {
            $files[$relativePath] = $entry;

            return $files;
        }, [$entry['hash'] => $contents]);
    }

    public function delete(string $theme, string $relativePath): void
    {
        $scope = Visual::storageScope();

        $state = $this->connection()->transaction(function () use ($scope, $theme, $relativePath) {
            $this->documents($scope, $theme)->where('path', $relativePath)->delete();

            return $this->recordEdit($scope, $theme, ['edited_at' => now()]);
        });

        $this->editorWritten($scope, $theme, (int) $state->editor_version, function (array $files) use ($relativePath) {
            unset($files[$relativePath]);

            return $files;
        });
    }

    /**
     * Insert a version and make it the live one, in one transaction, so the
     * storefront never sees half a publish, then put it in the cache, so the
     * storefront never has to read it back.
     *
     * The version is stamped later than the one it replaces even within the
     * same second, since the stamp is what tells compiled views and parsed
     * templates that the storefront's documents changed.
     */
    public function publish(string $theme, array $documents): void
    {
        $scope = Visual::storageScope();

        [$versionId, $stamp] = $this->connection()->transaction(function () use ($scope, $theme, $documents) {
            $this->lockState($scope, $theme);

            $previousStamp = (int) $this->connection()->table('visual_theme_states as state')
                ->leftJoin('visual_theme_versions as version', 'version.id', '=', 'state.live_version_id')
                ->where('state.scope', $scope)
                ->where('state.theme', $theme)
                ->value('version.stamp');

            $stamp = max(now()->getTimestamp(), $previousStamp + 1);

            $versionId = $this->connection()->table('visual_theme_versions')->insertGetId([
                'scope' => $scope,
                'theme' => $theme,
                'documents' => json_encode($documents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'stamp' => $stamp,
                'created_at' => now(),
            ]);

            $this->states($scope, $theme)->update([
                'live_version_id' => $versionId,
                'edited_at' => null,
                'updated_at' => now(),
            ]);

            $this->pruneVersions($scope, $theme);

            return [$versionId, $stamp];
        });

        $files = $this->rememberVersion($scope, $theme, $versionId, $documents, $stamp);
        $this->indexes[$this->cacheKey($scope, $theme, 'live')] = ['version' => $versionId, 'files' => $files];

        $this->cache()->put($this->cacheKey($scope, $theme, 'live'), $versionId, self::POINTER_TTL);
    }

    public function discard(string $theme): void
    {
        $scope = Visual::storageScope();

        $this->replaceEditor($scope, $theme, function () use ($scope, $theme) {
            $liveVersionId = $this->states($scope, $theme)->value('live_version_id');

            return $liveVersionId
                ? (array) json_decode((string) $this->connection()->table('visual_theme_versions')->where('id', $liveVersionId)->value('documents'), true)
                : [];
        }, edited: false);
    }

    public function hasUnpublishedEdits(string $theme): bool
    {
        return $this->states(Visual::storageScope(), $theme)->whereNotNull('edited_at')->exists();
    }

    public function scopes(): array
    {
        return $this->connection()->table('visual_theme_states')
            ->distinct()
            ->orderBy('scope')
            ->pluck('scope')
            ->map(fn ($scope) => (string) $scope)
            ->all();
    }

    public function themes(): array
    {
        return $this->connection()->table('visual_theme_states')
            ->where('scope', Visual::storageScope())
            ->orderBy('theme')
            ->pluck('theme')
            ->all();
    }

    public function snapshot(string $theme): ThemeSnapshot
    {
        $scope = Visual::storageScope();
        $state = $this->states($scope, $theme)->first(['live_version_id', 'edited_at']);

        $live = $state?->live_version_id
            ? (array) json_decode((string) $this->connection()->table('visual_theme_versions')->where('id', $state->live_version_id)->value('documents'), true)
            : null;

        return new ThemeSnapshot(
            editor: $this->documents($scope, $theme)->orderBy('path')->pluck('content', 'path')->all(),
            live: $live === null ? null : collect($live)->sortKeys()->all(),
            edited: $state?->edited_at !== null,
        );
    }

    public function restore(string $theme, ThemeSnapshot $snapshot): void
    {
        if ($snapshot->live !== null) {
            $this->publish($theme, $snapshot->live);
        }

        $this->replaceEditor(Visual::storageScope(), $theme, fn () => $snapshot->editor, $snapshot->edited);
    }

    /**
     * The themes that have something published, in every scope unless one is
     * given.
     *
     * @return array<int, array{scope: string, theme: string}>
     */
    public function publishedThemes(?string $scope = null, ?string $theme = null): array
    {
        return $this->connection()->table('visual_theme_states')
            ->whereNotNull('live_version_id')
            ->when($scope !== null, fn (Builder $query) => $query->where('scope', $scope))
            ->when($theme !== null, fn (Builder $query) => $query->where('theme', $theme))
            ->orderBy('scope')
            ->orderBy('theme')
            ->get(['scope', 'theme'])
            ->map(fn (object $state) => ['scope' => (string) $state->scope, 'theme' => (string) $state->theme])
            ->all();
    }

    /**
     * Put what a theme serves in the cache, so the storefront does not have to
     * read it from the database. The number of documents it serves.
     *
     * Its version and contents never change, so they are written again, which
     * also renews how long they are kept. The live pointer is only added: a
     * publish made while this runs has already put a newer one.
     */
    public function warm(string $scope, string $theme): int
    {
        $versionId = (int) $this->states($scope, $theme)->value('live_version_id');

        if ($versionId === 0) {
            return 0;
        }

        $this->flush();
        $files = $this->loadVersion($scope, $theme, $versionId)['files'];
        $this->cache()->add($this->cacheKey($scope, $theme, 'live'), $versionId, self::POINTER_TTL);

        return count($files);
    }

    public function themesPath(string $scope): string
    {
        return self::SCHEME.'://'.$this->scopeSegment($scope).'/themes';
    }

    public function clear(): void
    {
        $this->connection()->table('visual_theme_documents')->delete();
        $this->connection()->table('visual_theme_versions')->delete();
        $this->connection()->table('visual_theme_states')->delete();

        $this->flush();
    }

    /**
     * The documents of a theme mode, by relative path, with their size, the
     * time they last changed and the hash of their contents.
     *
     * @return array<string, array{size: int, stamp: int, hash: string}>
     */
    public function index(string $scope, string $theme, string $mode): array
    {
        return $this->loadIndex($scope, $theme, $mode)['files'];
    }

    public function contents(string $scope, string $theme, string $mode, string $relativePath): ?string
    {
        $index = $this->loadIndex($scope, $theme, $mode);
        $hash = $index['files'][$relativePath]['hash'] ?? null;

        if ($hash === null) {
            return null;
        }

        $prefetched = $this->prefetch($index['files'], $relativePath);

        if (isset($this->contents[$hash])) {
            return $this->contents[$hash];
        }

        $contents = $prefetched ? null : $this->cache()->get($this->contentKey($hash));

        if ($contents === null) {
            $contents = $mode === 'live'
                ? ($this->loadVersion($scope, $theme, $index['version'])['documents'][$relativePath] ?? null)
                : $this->documents($scope, $theme)->where('path', $relativePath)->value('content');

            if ($contents === null) {
                return null;
            }

            $this->cache()->put($this->contentKey(md5($contents)), $contents, self::ENTRY_TTL);
        }

        return $this->contents[md5($contents)] = $contents;
    }

    /**
     * Read every document of the folder a document is in, `{channel}/{locale}`,
     * in one cache call, since rendering a page reads several of them.
     *
     * Whether the document asked for was among those read.
     *
     * @param  array<string, array{size: int, stamp: int, hash: string}>  $files
     */
    protected function prefetch(array $files, string $relativePath): bool
    {
        $folder = implode('/', array_slice(explode('/', $relativePath), 0, 2)).'/';
        $keys = [];

        foreach ($files as $path => $file) {
            if (str_starts_with($path, $folder) && ! isset($this->contents[$file['hash']])) {
                $keys[$file['hash']] = $this->contentKey($file['hash']);
            }
        }

        if (count($keys) < 2) {
            return false;
        }

        $found = $this->cache()->getMultiple(array_values($keys));

        foreach ($keys as $hash => $key) {
            if (($found[$key] ?? null) !== null) {
                $this->contents[$hash] = $found[$key];
            }
        }

        return true;
    }

    /**
     * The scope a `visual://` path segment names.
     */
    public function scopeFromSegment(string $segment): string
    {
        return $segment === '_' ? '' : $segment;
    }

    /**
     * Forget everything read in this request.
     */
    public function flush(): void
    {
        $this->indexes = [];
        $this->contents = [];
    }

    /**
     * @return array{version: int, files: array<string, array{size: int, stamp: int, hash: string}>}
     */
    protected function loadIndex(string $scope, string $theme, string $mode): array
    {
        $key = $this->cacheKey($scope, $theme, $mode);

        if (isset($this->indexes[$key])) {
            return $this->indexes[$key];
        }

        if ($mode === 'editor') {
            return $this->indexes[$key] = $this->editorIndex($scope, $theme);
        }

        if ($mode === 'live') {
            return $this->indexes[$key] = $this->liveIndex($scope, $theme);
        }

        return $this->indexes[$key] = ['version' => 0, 'files' => []];
    }

    /**
     * @return array{version: int, files: array<string, array{size: int, stamp: int, hash: string}>}
     */
    protected function editorIndex(string $scope, string $theme): array
    {
        $version = $this->pointer($scope, $theme, 'editor', fn () => (int) $this->states($scope, $theme)->value('editor_version'));

        $files = $this->cache()->remember($this->indexKey($scope, $theme, 'editor', $version), self::ENTRY_TTL, fn () => $this->documents($scope, $theme)
            ->get(['path', 'hash', 'size', 'stamp'])
            ->mapWithKeys(fn (object $document) => [$document->path => [
                'size' => (int) $document->size,
                'stamp' => (int) $document->stamp,
                'hash' => $document->hash,
            ]])
            ->all());

        return ['version' => $version, 'files' => $files];
    }

    /**
     * @return array{version: int, files: array<string, array{size: int, stamp: int, hash: string}>}
     */
    protected function liveIndex(string $scope, string $theme): array
    {
        $versionId = $this->pointer($scope, $theme, 'live', fn () => (int) $this->states($scope, $theme)->value('live_version_id'));

        if ($versionId === 0) {
            return ['version' => 0, 'files' => []];
        }

        $files = $this->cache()->get($this->indexKey($scope, $theme, 'live', $versionId))
            ?? $this->loadVersion($scope, $theme, $versionId)['files'];

        return ['version' => $versionId, 'files' => $files];
    }

    /**
     * Read a version from the database and keep it in the cache.
     *
     * @return array{files: array<string, array{size: int, stamp: int, hash: string}>, documents: array<string, string>}
     */
    protected function loadVersion(string $scope, string $theme, int $versionId): array
    {
        $version = $this->connection()->table('visual_theme_versions')->where('id', $versionId)->first(['documents', 'stamp']);
        $documents = $version ? (array) json_decode($version->documents, true) : [];

        return [
            'files' => $this->rememberVersion($scope, $theme, $versionId, $documents, $version ? (int) $version->stamp : 0),
            'documents' => $documents,
        ];
    }

    /**
     * Keep a version's index and documents in the cache, since a version never
     * changes.
     *
     * @param  array<string, string>  $documents
     * @return array<string, array{size: int, stamp: int, hash: string}>
     */
    protected function rememberVersion(string $scope, string $theme, int $versionId, array $documents, int $stamp): array
    {
        $files = [];

        foreach ($documents as $path => $contents) {
            $hash = md5($contents);
            $files[$path] = ['size' => strlen($contents), 'stamp' => $stamp, 'hash' => $hash];
            $this->rememberContents($hash, $contents);
        }

        $this->cache()->put($this->indexKey($scope, $theme, 'live', $versionId), $files, self::ENTRY_TTL);

        return $files;
    }

    /**
     * Carry what a write changed into the cache and into what this request has
     * read, as the index of the editor version the write produced.
     *
     * The index read earlier can only be carried forward when it is the one the
     * write came after; another writer may have come in between otherwise, and
     * readers then build the new index from the database instead.
     *
     * @param  callable(array<string, array{size: int, stamp: int, hash: string}>): array<string, array{size: int, stamp: int, hash: string}>  $change
     * @param  array<string, string>  $contents  New contents by hash.
     */
    protected function editorWritten(string $scope, string $theme, int $version, callable $change, array $contents = [], bool $replacesEverything = false): void
    {
        $key = $this->cacheKey($scope, $theme, 'editor');
        $previous = $this->indexes[$key] ?? $this->cachedEditorIndex($scope, $theme, $version - 1);

        foreach ($contents as $hash => $content) {
            $this->rememberContents($hash, $content);
        }

        if ($replacesEverything || ($previous && $previous['version'] === $version - 1)) {
            $files = $change($previous['files'] ?? []);
            $this->indexes[$key] = ['version' => $version, 'files' => $files];
            $this->cache()->put($this->indexKey($scope, $theme, 'editor', $version), $files, self::ENTRY_TTL);
        } else {
            unset($this->indexes[$key]);
        }

        $this->cache()->put($key, $version, self::POINTER_TTL);
    }

    /**
     * Put contents in the cache unless this request already knows it holds
     * them: everything remembered here was read from it or written to it.
     *
     * Each put goes on its own, which costs no more round trips than
     * `putMany`, since that sends a transaction one command at a time.
     */
    protected function rememberContents(string $hash, string $contents): void
    {
        if (isset($this->contents[$hash])) {
            return;
        }

        $this->cache()->put($this->contentKey($hash), $contents, self::ENTRY_TTL);
        $this->contents[$hash] = $contents;
    }

    /**
     * The index of an editor version, when the cache holds it. A theme holds
     * no documents before its first edit.
     *
     * @return array{version: int, files: array<string, array{size: int, stamp: int, hash: string}>}|null
     */
    protected function cachedEditorIndex(string $scope, string $theme, int $version): ?array
    {
        if ($version === 0) {
            return ['version' => 0, 'files' => []];
        }

        $files = $this->cache()->get($this->indexKey($scope, $theme, 'editor', $version));

        return $files === null ? null : ['version' => $version, 'files' => $files];
    }

    /**
     * Replace every editor document of a theme, counted as one edit, and carry
     * the new set into the cache.
     *
     * @param  callable(): array<string, string>  $documents  Read inside the transaction, contents keyed by relative path.
     */
    protected function replaceEditor(string $scope, string $theme, callable $documents, bool $edited): void
    {
        $now = now();

        [$state, $written] = $this->connection()->transaction(function () use ($scope, $theme, $documents, $edited, $now) {
            $state = $this->recordEdit($scope, $theme, ['edited_at' => $edited ? $now : null]);

            $this->documents($scope, $theme)->delete();

            $written = $documents();

            foreach (array_chunk($written, 50, true) as $chunk) {
                $this->connection()->table('visual_theme_documents')->insert(collect($chunk)->map(fn (string $contents, string $path) => [
                    'scope' => $scope,
                    'theme' => $theme,
                    'path' => $path,
                    'content' => $contents,
                    'hash' => md5($contents),
                    'size' => strlen($contents),
                    'stamp' => $now->getTimestamp(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all());
            }

            return [$state, $written];
        });

        $files = collect($written)
            ->map(fn (string $contents) => ['size' => strlen($contents), 'stamp' => $now->getTimestamp(), 'hash' => md5($contents)])
            ->all();

        $this->editorWritten(
            $scope,
            $theme,
            (int) $state->editor_version,
            fn () => $files,
            collect($written)->mapWithKeys(fn (string $contents) => [md5($contents) => $contents])->all(),
            replacesEverything: true,
        );
    }

    /**
     * Read a theme's editor version or live version id through the cache.
     *
     * Filling it uses `add`, so a reader holding a value older than a writer's
     * cannot overwrite the writer's.
     */
    protected function pointer(string $scope, string $theme, string $mode, callable $fromDatabase): int
    {
        $key = $this->cacheKey($scope, $theme, $mode);
        $value = $this->cache()->get($key);

        if ($value !== null) {
            return (int) $value;
        }

        $value = $fromDatabase();
        $this->cache()->add($key, $value, self::POINTER_TTL);

        return $value;
    }

    /**
     * Count an edit to a theme's editor documents, creating its state on the
     * first one. The row stays locked until the transaction ends, so the
     * version read back is the one this edit produced.
     *
     * @param  array<string, mixed>  $changes
     */
    protected function recordEdit(string $scope, string $theme, array $changes): object
    {
        $now = now();

        $this->connection()->table('visual_theme_states')->upsert(
            [['scope' => $scope, 'theme' => $theme, 'editor_version' => 1, 'created_at' => $now, 'updated_at' => $now] + $changes],
            ['scope', 'theme'],
            ['editor_version' => $this->connection()->raw('editor_version + 1'), 'updated_at' => $now] + $changes,
        );

        return $this->states($scope, $theme)->first(['editor_version', 'live_version_id']);
    }

    /**
     * Create a theme's state if it has none, and lock it until the transaction
     * ends.
     */
    protected function lockState(string $scope, string $theme): void
    {
        $now = now();

        $this->connection()->table('visual_theme_states')->upsert(
            [['scope' => $scope, 'theme' => $theme, 'created_at' => $now, 'updated_at' => $now]],
            ['scope', 'theme'],
            ['updated_at' => $now],
        );
    }

    protected function pruneVersions(string $scope, string $theme): void
    {
        $keep = max(1, (int) config('bagisto_visual.versions_to_keep', 5));

        $stale = $this->versions($scope, $theme)
            ->orderByDesc('id')
            ->offset($keep)
            ->limit(1000)
            ->pluck('id');

        if ($stale->isNotEmpty()) {
            $this->connection()->table('visual_theme_versions')->whereIn('id', $stale)->delete();
        }
    }

    protected function cacheKey(string $scope, string $theme, string $mode): string
    {
        return "visual:theme-data:{$this->scopeSegment($scope)}:{$theme}:{$mode}";
    }

    protected function indexKey(string $scope, string $theme, string $mode, int $version): string
    {
        return $this->cacheKey($scope, $theme, $mode).":{$version}:index";
    }

    /**
     * Contents are cached by what they are, so a document that did not change
     * is not read again when its theme's version moves on.
     */
    protected function contentKey(string $hash): string
    {
        return "visual:theme-data:content:{$hash}";
    }

    /**
     * An empty scope cannot be a path segment, and `_` cannot be a scope since
     * scopes start with a letter or number.
     */
    protected function scopeSegment(string $scope): string
    {
        return $scope === '' ? '_' : $scope;
    }

    /**
     * Resolved on each call rather than kept, since the singleton outlives
     * a config change, as tests and tenant hosts make.
     */
    protected function connection(): ConnectionInterface
    {
        return DB::connection(config('bagisto_visual.storages.database.connection') ?: null);
    }

    protected function cache(): Repository
    {
        return Cache::store(config('bagisto_visual.storages.database.cache') ?: null);
    }

    protected function documents(string $scope, string $theme): Builder
    {
        return $this->connection()->table('visual_theme_documents')->where('scope', $scope)->where('theme', $theme);
    }

    protected function versions(string $scope, string $theme): Builder
    {
        return $this->connection()->table('visual_theme_versions')->where('scope', $scope)->where('theme', $theme);
    }

    protected function states(string $scope, string $theme): Builder
    {
        return $this->connection()->table('visual_theme_states')->where('scope', $scope)->where('theme', $theme);
    }
}
