<?php

namespace BagistoPlus\Visual\Support;

use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;

final class IconMapFilesystemAdapter implements FilesystemAdapter
{
    private const PREFIX = 'icon-';

    private const FALLBACK_ICON = 'lucide-file-question';

    public function __construct(
        private readonly ConfigRepository $config,
        private readonly Closure $resolveIconContents,
    ) {}

    public function fileExists(string $path): bool
    {
        return Str::endsWith($path, '.svg');
    }

    public function directoryExists(string $path): bool
    {
        return trim($path, '/') === '';
    }

    public function write(string $path, string $contents, Config $config): void
    {
        throw UnableToWriteFile::atLocation($path, 'The visual icon map disk is read-only.');
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        throw UnableToWriteFile::atLocation($path, 'The visual icon map disk is read-only.');
    }

    public function read(string $path): string
    {
        if (! Str::endsWith($path, '.svg')) {
            throw UnableToReadFile::fromLocation($path, 'Only SVG icon paths can be read.');
        }

        $icon = $this->iconMap()[$this->aliasFromPath($path)] ?? self::FALLBACK_ICON;
        $resolver = $this->resolveIconContents;

        try {
            return $resolver($icon);
        } catch (\Throwable $exception) {
            throw UnableToReadFile::fromLocation($path, "Unable to resolve icon [$icon].", $exception);
        }
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw UnableToReadFile::fromLocation($path, 'Unable to open temporary stream.');
        }

        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        throw UnableToDeleteFile::atLocation($path, 'The visual icon map disk is read-only.');
    }

    public function deleteDirectory(string $path): void
    {
        throw UnableToDeleteDirectory::atLocation($path, 'The visual icon map disk is read-only.');
    }

    public function createDirectory(string $path, Config $config): void
    {
        throw UnableToCreateDirectory::atLocation($path, 'The visual icon map disk is read-only.');
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'The visual icon map disk is read-only.');
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        if (! Str::endsWith($path, '.svg')) {
            throw UnableToRetrieveMetadata::mimeType($path, 'Only SVG icon paths have a mime type.');
        }

        return new FileAttributes($path, null, null, null, 'image/svg+xml');
    }

    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path, null, null, 0);
    }

    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes($path, strlen($this->read($path)));
    }

    public function listContents(string $path, bool $deep): iterable
    {
        foreach (array_keys($this->iconMap()) as $alias) {
            if (! str_starts_with($alias, self::PREFIX)) {
                continue;
            }

            yield new FileAttributes($this->pathFromAlias($alias));
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        throw UnableToMoveFile::because('The visual icon map disk is read-only.', $source, $destination);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        throw UnableToCopyFile::because('The visual icon map disk is read-only.', $source, $destination);
    }

    /**
     * @return array<string, string>
     */
    private function iconMap(): array
    {
        return array_filter(
            (array) $this->config->get('bagisto_visual_iconmap', []),
            fn ($icon, $alias): bool => is_string($alias) && is_string($icon),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function aliasFromPath(string $path): string
    {
        return self::PREFIX.str_replace('/', '.', Str::beforeLast(trim($path, '/'), '.svg'));
    }

    private function pathFromAlias(string $alias): string
    {
        return str_replace('.', '/', Str::after($alias, self::PREFIX)).'.svg';
    }
}
