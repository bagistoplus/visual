<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Facades\Visual;
use Webkul\Theme\Theme as BagistoTheme;

class Theme extends BagistoTheme
{
    public static function make(array $attributes): self
    {
        return new self(
            code: $attributes['code'],
            name: $attributes['name'],
            assetsPath: $attributes['assets_path'] ?? $attributes['code'],
            viewsPath: $attributes['view_path'] ?? $attributes['code'],
            vite: $attributes['vite'] ?? [],
            version: $attributes['version'] ?? '1.0.0',
            author: $attributes['author'] ?? '',
            previewImage: $attributes['preview_image'] ?? '',
            documentationUrl: $attributes['documentation_url'] ?? '',
            isVisualTheme: $attributes['visual_theme'] ?? false
        );
    }

    public function __construct(
        public $code,
        public $name,
        public $assetsPath = null,
        public $viewsPath = null,
        public $vite = [],
        public ?string $version = '0.0.0',
        public ?string $author = '',
        public ?string $previewImage = '',
        public ?string $documentationUrl = '',
        public bool $isVisualTheme = false
    ) {
        parent::__construct($code, $name, $assetsPath, $viewsPath, $vite);
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

        $visualPaths = Visual::getVisualThemePaths($this->code);

        return array_merge($visualPaths, $paths);
    }
}
