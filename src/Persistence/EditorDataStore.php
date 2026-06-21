<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

final class EditorDataStore
{
    public function __construct(
        protected ThemePathsResolver $themePathsResolver,
        protected Filesystem $files,
    ) {}

    public function relativePath(string $channel, string $locale, string $logicalPath): string
    {
        return "{$channel}/{$locale}/".ltrim($logicalPath, '/');
    }

    public function logicalPathFromRelative(string $relativePath): string
    {
        return implode('/', array_slice(explode('/', $relativePath), 2));
    }

    public function localeFromRelative(string $relativePath): ?string
    {
        return explode('/', $relativePath)[1] ?? null;
    }

    /**
     * @param  array<int, string>  $sources
     */
    public function parentFromSources(string $theme, string $logicalPath, array $sources, ?string $excludeRelativePath = null): ?string
    {
        foreach ($sources as $sourcePath) {
            $relativePath = $this->relativePathFromAbsolute($theme, $sourcePath);

            if (
                $relativePath
                && $relativePath !== $excludeRelativePath
                && $this->logicalPathFromRelative($relativePath) === $logicalPath
                && $this->exists($theme, $relativePath)
            ) {
                return $relativePath;
            }
        }

        return null;
    }

    public function nearestFallbackParent(string $theme, string $channel, string $locale, string $logicalPath): ?string
    {
        $current = $this->relativePath($channel, $locale, $logicalPath);

        foreach ($this->themePathsResolver->resolveFallbackPaths($theme, 'editor', $channel, $locale) as $basePath) {
            $candidate = $this->relativePathFromBasePath($theme, $basePath, $logicalPath);

            if ($candidate && $candidate !== $current && $this->exists($theme, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function storedParent(string $theme, string $relativePath): ?string
    {
        return $this->storedParentFromRaw($theme, $this->loadRaw($theme, $relativePath));
    }

    public function loadRaw(string $theme, string $relativePath): array
    {
        $path = $this->path($theme, $relativePath);

        if (! $this->files->exists($path)) {
            return [];
        }

        $data = json_decode($this->files->get($path), true);

        return is_array($data) ? $data : [];
    }

    public function loadResolved(string $theme, string $relativePath): array
    {
        $raw = $this->loadRaw($theme, $relativePath);
        $parent = $this->storedParentFromRaw($theme, $raw);

        if ($parent) {
            return $this->merge($this->loadResolved($theme, $parent), $raw);
        }

        unset($raw['parent']);

        return $raw;
    }

    public function merge(array $parent, array $child): array
    {
        unset($parent['parent'], $child['parent']);

        return $this->mergeRecursive($parent, $child);
    }

    protected function mergeRecursive(array $parent, array $child): array
    {
        foreach ($child as $key => $value) {
            if (
                isset($parent[$key])
                && is_array($parent[$key])
                && is_array($value)
                && $this->isAssociative($parent[$key])
                && $this->isAssociative($value)
            ) {
                $parent[$key] = $this->mergeRecursive($parent[$key], $value);
            } else {
                $parent[$key] = $value;
            }
        }

        return $parent;
    }

    public function diff(array $current, array $parent): array
    {
        unset($current['parent'], $parent['parent']);

        return $this->diffRecursive($current, $parent);
    }

    protected function diffRecursive(array $current, array $parent): array
    {
        $diff = [];

        foreach ($current as $key => $value) {
            if (! array_key_exists($key, $parent)) {
                $diff[$key] = $value;

                continue;
            }

            if (
                is_array($value)
                && is_array($parent[$key])
                && $this->isAssociative($value)
                && $this->isAssociative($parent[$key])
            ) {
                $branch = $this->diffRecursive($value, $parent[$key]);

                if ($branch !== []) {
                    $diff[$key] = $branch;
                }

                continue;
            }

            if ($value !== $parent[$key]) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }

    public function save(string $theme, string $relativePath, array $diff, ?string $parent): void
    {
        if ($parent && $diff === []) {
            $this->delete($theme, $relativePath);

            return;
        }

        $data = $parent ? ['parent' => $parent] + $diff : $diff;
        $path = $this->path($theme, $relativePath);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->touchLastEdit($theme);
    }

    public function delete(string $theme, string $relativePath): void
    {
        $path = $this->path($theme, $relativePath);

        if ($this->files->exists($path)) {
            $this->files->delete($path);
        }

        $this->touchLastEdit($theme);
    }

    public function path(string $theme, string $relativePath): string
    {
        return $this->themePathsResolver->getThemeBaseDataPath($theme, 'editor').'/'.ltrim($relativePath, '/');
    }

    public function exists(string $theme, string $relativePath): bool
    {
        return $this->files->exists($this->path($theme, $relativePath));
    }

    public function parentExists(string $theme, string $parent): bool
    {
        return $this->safeParent($parent) && $this->exists($theme, $parent);
    }

    public function relativePathFromAbsolute(string $theme, string $path): ?string
    {
        $base = rtrim($this->normalizePath($this->themePathsResolver->getThemeBaseDataPath($theme, 'editor')), '/').'/';
        $path = $this->normalizePath($path);

        if (! str_starts_with($path, $base)) {
            return null;
        }

        $relative = substr($path, strlen($base));

        return $this->safeParent($relative) ? $relative : null;
    }

    protected function relativePathFromBasePath(string $theme, string $basePath, string $logicalPath): ?string
    {
        return $this->relativePathFromAbsolute($theme, rtrim($basePath, '/').'/'.ltrim($logicalPath, '/'));
    }

    protected function storedParentFromRaw(string $theme, array $raw): ?string
    {
        $parent = (string) ($raw['parent'] ?? '');

        return $this->parentExists($theme, $parent) ? $parent : null;
    }

    protected function touchLastEdit(string $theme): void
    {
        $path = $this->themePathsResolver->getThemeBaseDataPath($theme, 'editor/.last-edit');

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, (string) time());
    }

    protected function safeParent(string $parent): bool
    {
        return $parent !== ''
            && ! str_starts_with($parent, '/')
            && ! preg_match('#(^|/)\.\.($|/)#', $parent);
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function isAssociative(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
