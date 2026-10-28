<?php

namespace BagistoPlus\Visual\View;

use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use Illuminate\View\FileViewFinder;
use Webkul\Theme\ThemeViewFinder;

/**
 * Bagisto's view finder, for theme data read from `visual://` paths.
 *
 * Bagisto prefixes every theme view path with the base path, which turns a
 * stream path into one that does not exist. This undoes that for stream paths
 * and leaves everything else to Bagisto.
 */
class VisualThemeViewFinder extends ThemeViewFinder
{
    public static function from(FileViewFinder $finder): self
    {
        $instance = new self($finder->getFilesystem(), $finder->getPaths(), $finder->getExtensions());

        foreach ($finder->getHints() as $namespace => $hints) {
            $instance->hints[$namespace] = array_map(fn (string $path) => $instance->withoutBasePath($path), $hints);
        }

        return $instance;
    }

    public function addThemeNamespacePaths($namespace)
    {
        return array_map(fn (string $path) => $this->withoutBasePath($path), parent::addThemeNamespacePaths($namespace));
    }

    public function replaceNamespace($namespace, $hints)
    {
        parent::replaceNamespace($namespace, $hints);

        $this->hints[$namespace] = array_map(fn (string $path) => $this->withoutBasePath($path), $this->hints[$namespace]);
    }

    protected function withoutBasePath(string $path): string
    {
        $position = strpos($path, DatabaseThemeDataStore::SCHEME.'://');

        return $position === false ? $path : substr($path, $position);
    }
}
