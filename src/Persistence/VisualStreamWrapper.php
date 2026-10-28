<?php

namespace BagistoPlus\Visual\Persistence;

/**
 * Serves `visual://{scope}/themes/{theme}/{mode}/...` paths from the database
 * store, so that the view finder, Craftile and the editor data store read
 * theme data the way they read files.
 *
 * Read only: writes go through the store. Only the theme modes are served,
 * `editor` and `live`; a mode directory exists once it holds a document.
 */
class VisualStreamWrapper
{
    /** @var resource|null */
    public $context;

    protected static ?DatabaseThemeDataStore $store = null;

    protected string $contents = '';

    protected int $position = 0;

    /** @var array<int, string> */
    protected array $listing = [];

    public static function register(DatabaseThemeDataStore $store): void
    {
        static::$store = $store;

        if (! in_array(DatabaseThemeDataStore::SCHEME, stream_get_wrappers(), true)) {
            stream_wrapper_register(DatabaseThemeDataStore::SCHEME, static::class);
        }
    }

    public function url_stat(string $path, int $flags): array|false
    {
        $target = $this->parse($path);

        if (! $target) {
            return false;
        }

        $files = static::$store->index($target['scope'], $target['theme'], $target['mode']);

        if (isset($files[$target['path']])) {
            return $this->stat(0100444, $files[$target['path']]['size'], $files[$target['path']]['stamp']);
        }

        if ($this->children($files, $target['path']) !== []) {
            return $this->stat(0040555, 0, 0);
        }

        return false;
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        $target = $this->parse($path);

        if (! $target || ! str_starts_with($mode, 'r')) {
            return false;
        }

        $contents = static::$store->contents($target['scope'], $target['theme'], $target['mode'], $target['path']);

        if ($contents === null) {
            return false;
        }

        $this->contents = $contents;
        $this->position = 0;

        return true;
    }

    public function stream_read(int $count): string
    {
        $chunk = substr($this->contents, $this->position, $count);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen($this->contents);
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_seek(int $offset, int $whence): bool
    {
        $position = match ($whence) {
            SEEK_CUR => $this->position + $offset,
            SEEK_END => strlen($this->contents) + $offset,
            default => $offset,
        };

        if ($position < 0) {
            return false;
        }

        $this->position = $position;

        return true;
    }

    public function stream_stat(): array
    {
        return $this->stat(0100444, strlen($this->contents), 0);
    }

    public function stream_set_option(int $option, int $arg1, ?int $arg2): bool
    {
        return false;
    }

    public function stream_close(): void {}

    public function dir_opendir(string $path, int $options): bool
    {
        $target = $this->parse($path);

        if (! $target) {
            return false;
        }

        $this->listing = $this->children(
            static::$store->index($target['scope'], $target['theme'], $target['mode']),
            $target['path'],
        );

        return $this->listing !== [];
    }

    public function dir_readdir(): string|false
    {
        $entry = current($this->listing);
        next($this->listing);

        return $entry;
    }

    public function dir_rewinddir(): bool
    {
        reset($this->listing);

        return true;
    }

    public function dir_closedir(): bool
    {
        $this->listing = [];

        return true;
    }

    /**
     * @return array{scope: string, theme: string, mode: string, path: string}|null
     */
    protected function parse(string $path): ?array
    {
        $rest = substr($path, strlen(DatabaseThemeDataStore::SCHEME) + 3);
        $segments = array_values(array_filter(explode('/', str_replace('\\', '/', $rest)), fn ($segment) => $segment !== ''));

        if (count($segments) < 4 || $segments[1] !== 'themes' || in_array('..', $segments, true)) {
            return null;
        }

        return [
            'scope' => static::$store->scopeFromSegment($segments[0]),
            'theme' => $segments[2],
            'mode' => $segments[3],
            'path' => implode('/', array_slice($segments, 4)),
        ];
    }

    /**
     * The names directly inside a directory of the index.
     *
     * @param  array<string, mixed>  $files
     * @return array<int, string>
     */
    protected function children(array $files, string $directory): array
    {
        $prefix = $directory === '' ? '' : $directory.'/';
        $children = [];

        foreach (array_keys($files) as $file) {
            if ($prefix === '' || str_starts_with($file, $prefix)) {
                $children[explode('/', substr($file, strlen($prefix)))[0]] = true;
            }
        }

        return array_keys($children);
    }

    protected function stat(int $mode, int $size, int $mtime): array
    {
        $values = [0, 0, $mode, 1, 0, 0, 0, $size, $mtime, $mtime, $mtime, -1, -1];
        $keys = ['dev', 'ino', 'mode', 'nlink', 'uid', 'gid', 'rdev', 'size', 'atime', 'mtime', 'ctime', 'blksize', 'blocks'];

        return array_merge($values, array_combine($keys, $values));
    }
}
