<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\TemplateFile;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TemplateDiscovery
{
    public const ASSIGNABLE_TYPES = ['product', 'category', 'page'];

    protected const EXTENSIONS = ['json', 'yaml', 'yml', 'visual.php'];

    protected array $cache = [];

    public function forType(
        Theme|string $theme,
        string $type,
        ?string $channel = null,
        ?string $locale = null,
        bool $includeEditorDrafts = false
    ): Collection {
        if (! in_array($type, self::ASSIGNABLE_TYPES, true)) {
            return collect();
        }

        return $this->all($theme, $channel, $locale, $includeEditorDrafts)
            ->where('type', $type)
            ->values();
    }

    public function all(
        Theme|string $theme,
        ?string $channel = null,
        ?string $locale = null,
        bool $includeEditorDrafts = false
    ): Collection {
        $theme = $this->theme($theme);

        if (! $theme?->isVisualTheme()) {
            return collect();
        }

        $channel ??= core()->getRequestedChannelCode();
        $locale ??= core()->getRequestedLocaleCode();

        $cacheKey = implode('|', [
            $theme->code,
            $channel,
            $locale,
            $includeEditorDrafts ? 'editor' : 'live',
        ]);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $templates = collect($this->defaults());

        foreach ($this->packageTemplateFiles($theme) as $file) {
            if ($template = $this->createFromFile($file, 'package')) {
                $templates->put($template->key, $template);
            }
        }

        foreach ($this->storageTemplateFiles($theme->code, 'live', $channel, $locale) as $file) {
            if ($template = $this->createFromFile($file, 'live')) {
                $templates->put($template->key, $template);
            }
        }

        if ($includeEditorDrafts) {
            foreach ($this->storageTemplateFiles($theme->code, 'editor', $channel, $locale) as $file) {
                if ($template = $this->createFromFile($file, 'editor')) {
                    $templates->put($template->key, $template);
                }
            }
        }

        return $this->cache[$cacheKey] = $templates->values();
    }

    public function exists(
        Theme|string $theme,
        string $key,
        string $type,
        ?string $channel = null,
        ?string $locale = null,
        bool $includeEditorDrafts = false
    ): bool {
        return $this->forType($theme, $type, $channel, $locale, $includeEditorDrafts)
            ->contains(fn (TemplateFile $template) => $template->key === $key);
    }

    public function find(
        Theme|string $theme,
        string $key,
        string $type,
        ?string $channel = null,
        ?string $locale = null,
        bool $includeEditorDrafts = false
    ): ?TemplateFile {
        return $this->forType($theme, $type, $channel, $locale, $includeEditorDrafts)
            ->first(fn (TemplateFile $template) => $template->key === $key);
    }

    public function typeForKey(string $key): ?string
    {
        $type = Str::contains($key, '.')
            ? Str::before($key, '.')
            : $key;

        return in_array($type, self::ASSIGNABLE_TYPES, true) ? $type : null;
    }

    public function templateStoragePath(string $key): string
    {
        $type = $this->typeForKey($key);

        if ($type && $key !== $type) {
            return 'templates/'.str_replace('.', '/', $key).'.json';
        }

        return "templates/{$key}.json";
    }

    protected function defaults(): array
    {
        return collect(self::ASSIGNABLE_TYPES)
            ->mapWithKeys(fn ($type) => [
                $type => new TemplateFile(
                    key: $type,
                    type: $type,
                    name: $type,
                    label: 'Default '.Str::headline($type),
                    source: 'default'
                ),
            ])
            ->all();
    }

    protected function packageTemplateFiles(Theme $theme): array
    {
        $paths = [];
        $currentTheme = $theme;

        do {
            if (! $currentTheme instanceof Theme || ! $currentTheme->isVisualTheme()) {
                break;
            }

            $basePath = $currentTheme->basePath;
            $path = Str::startsWith($basePath, DIRECTORY_SEPARATOR) ? $basePath : base_path($basePath);
            $paths[] = rtrim($path, '/').'/resources/views/templates';
        } while ($currentTheme = $currentTheme->getParent());

        return $this->filesIn($paths);
    }

    protected function storageTemplateFiles(string $themeCode, string $mode, string $channel, string $locale): array
    {
        return $this->filesIn(
            collect(ThemePathsResolver::resolveFallbackPaths($themeCode, $mode, $channel, $locale))
                ->map(fn ($path) => $path.'/templates')
                ->all()
        );
    }

    protected function filesIn(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                foreach (self::EXTENSIONS as $extension) {
                    if (Str::endsWith($file->getFilename(), '.'.$extension)) {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }

        return $files;
    }

    protected function createFromFile(string $path, string $source): ?TemplateFile
    {
        $extension = collect(self::EXTENSIONS)
            ->first(fn ($extension) => Str::endsWith($path, '.'.$extension));

        if (! $extension) {
            return null;
        }

        $name = Str::beforeLast(basename($path), '.'.$extension);
        $parent = basename(dirname($path));
        $type = null;
        $key = null;
        $isDefaultTemplate = false;

        if (in_array($parent, self::ASSIGNABLE_TYPES, true)) {
            $type = $parent;

            if ($name === 'index') {
                $key = $type;
                $name = $type;
                $isDefaultTemplate = true;
            } else {
                $key = "{$type}.{$name}";
            }
        } elseif (in_array($name, self::ASSIGNABLE_TYPES, true)) {
            $type = $name;
            $key = $type;
            $isDefaultTemplate = true;
        }

        if (
            ! $type
            || ! $key
            || $name === ''
            || (! $isDefaultTemplate && Str::contains($name, ['/', '\\', '.']))
        ) {
            return null;
        }

        return new TemplateFile(
            key: $key,
            type: $type,
            name: $name,
            label: $isDefaultTemplate
                ? 'Default '.Str::headline($type)
                : Str::headline(str_replace(['-', '_', '.'], ' ', $name)),
            path: $path,
            extension: $extension,
            source: $source,
            isJsonTemplate: true,
        );
    }

    protected function theme(Theme|string $theme): ?Theme
    {
        if ($theme instanceof Theme) {
            return $theme;
        }

        if (! isset(config('themes.shop', [])[$theme])) {
            return null;
        }

        return Theme::make(config("themes.shop.{$theme}"));
    }
}
