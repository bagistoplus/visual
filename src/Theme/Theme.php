<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Settings\Support\SettingsValues;
use Webkul\Theme\Theme as BagistoTheme;

class Theme extends BagistoTheme
{
    public SettingsValues $settings;

    public static function make(array $attributes): self
    {
        return new self(
            code: $attributes['code'],
            name: $attributes['name'],
            basePath: $attributes['base_path'] ?? $attributes['code'],
            assetsPath: $attributes['assets_path'] ?? $attributes['code'],
            viewsPath: $attributes['views_path'] ?? $attributes['code'],
            viewsNamespace: $attributes['views_namespace'] ?? null,
            vite: $attributes['vite'] ?? [],
            version: $attributes['version'] ?? '1.0.0',
            author: $attributes['author'] ?? '',
            previewImage: $attributes['preview_image'] ?? '',
            documentationUrl: $attributes['documentation_url'] ?? '',
            isVisualTheme: $attributes['visual_theme'] ?? false,
            settingsSchema: $attributes['settings_schema'] ?? []
        );
    }

    public function __construct(
        public $code,
        public $name,
        public $basePath,
        public $assetsPath = null,
        public $viewsPath = null,
        public $viewsNamespace = null,
        public $vite = [],
        public ?string $version = '0.0.0',
        public ?string $author = '',
        public ?string $previewImage = '',
        public ?string $documentationUrl = '',
        public bool $isVisualTheme = false,
        public array $settingsSchema = []
    ) {
        if (version_compare(core()->version(), '2.3.6', '<=')) {
            parent::__construct(
                code: $code,
                name: $name,
                assetsPath: $assetsPath,
                viewsPath: $viewsPath,
                vite: $vite,
            );
        } else {
            parent::__construct(
                code: $code,
                name: $name,
                assetsPath: $assetsPath,
                viewsPath: $viewsPath,
                vite: $vite,
                // @phpstan-ignore-next-line
                viewsNamespace: $viewsNamespace
            );
        }
    }

    public function isVisualTheme(): bool
    {
        return $this->isVisualTheme;
    }

    /**
     * Return all the possible view paths.
     */
    public function getViewPaths(): array
    {
        $paths = parent::getViewPaths();

        if (! $this->isVisualTheme) {
            return $paths;
        }

        $visualPaths = ThemePathsResolver::resolveThemeViewsPaths($this->code);

        return array_merge($visualPaths, $paths);
    }
}
