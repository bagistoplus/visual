<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Enums\ThemeEditorMode;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Support\BagistoThemeColors;
use BagistoPlus\Visual\Support\SectionSystem;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PropertyBag;
use Webkul\Theme\Theme as BagistoTheme;

/**
 * @property ?PropertyBag $settings
 */
class Theme extends BagistoTheme
{
    private ?PropertyBag $settings = null;

    public $viewsNamespace = null;

    public function __get(string $name): mixed
    {
        if ($name === 'settings') {
            return $this->settings ??= app(ThemeSettingsLoader::class)->loadThemeSettings($this);
        }

        throw new \Error('Undefined property: '.static::class.'::$'.$name);
    }

    public function __set(string $name, mixed $value): void
    {
        if ($name === 'settings') {
            $this->settings = $value;

            return;
        }

        throw new \Error('Cannot create dynamic property '.static::class.'::$'.$name);
    }

    public function __isset(string $name): bool
    {
        return $name === 'settings' && $this->settings !== null;
    }

    /**
     * The theme registered under a code, or null when none is.
     *
     * A theme's entry in `config/themes.php` is keyed by its code and need not
     * repeat it, as the stock theme's does not, so the key is what says which
     * theme this is.
     */
    public static function forCode(string $themeCode): ?self
    {
        $attributes = config("themes.shop.{$themeCode}");

        if (! is_array($attributes)) {
            return null;
        }

        $theme = self::make(['code' => $themeCode] + $attributes);
        $parentCode = $attributes['parent'] ?? null;

        if (filled($parentCode) && $parentCode !== $themeCode && $parent = self::forCode($parentCode)) {
            $theme->setParent($parent);
        }

        return $theme;
    }

    public static function make(array $attributes): self
    {
        return new self(
            code: $attributes['code'],
            name: $attributes['name'] ?? $attributes['code'],
            basePath: $attributes['base_path'] ?? $attributes['code'],
            assetsPath: $attributes['assets_path'] ?? $attributes['code'],
            viewsPath: $attributes['views_path'] ?? $attributes['code'],
            viewsNamespace: $attributes['views_namespace'] ?? null,
            vite: $attributes['vite'] ?? [],
            version: $attributes['version'] ?? '1.0.0',
            author: $attributes['author'] ?? '',
            previewImage: $attributes['preview_image'] ?? '',
            documentationUrl: $attributes['documentation_url'] ?? '',
            description: $attributes['description'] ?? null,
            isVisualTheme: $attributes['visual_theme'] ?? false,
            settingsSchema: self::resolveSettingsSchema($attributes),
            customize: $attributes['customize'] ?? []
        );
    }

    public function __construct(
        public $code,
        public $name,
        public $basePath,
        public $assetsPath = null,
        public $viewsPath = null,
        $viewsNamespace = null,
        public $vite = [],
        public ?string $version = '0.0.0',
        public ?string $author = '',
        public ?string $previewImage = '',
        public ?string $documentationUrl = '',
        public ?string $description = null,
        public bool $isVisualTheme = false,
        public array $settingsSchema = [],
        public array $customize = []
    ) {
        parent::__construct(
            code: $code,
            name: $name,
            assetsPath: $assetsPath,
            viewsPath: $viewsPath,
            vite: $vite
        );

        // Assigned after the parent constructor: Bagisto >= 2.4 promotes this property and would reset it,
        // Bagisto 2.3 does not declare it at all.
        $this->viewsNamespace = $viewsNamespace;
    }

    /**
     * The schema a theme is edited with.
     *
     * A visual theme carries one, loaded from its own settings file. A theme
     * Bagisto draws the sections of has no such file, so its colours stand in:
     * the ones it declared under `customize.colors`, or the built-in two.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<int, array<string, mixed>>
     */
    protected static function resolveSettingsSchema(array $attributes): array
    {
        $schema = $attributes['settings_schema'] ?? [];

        if (! empty($schema) || ($attributes['visual_theme'] ?? false)) {
            return $schema;
        }

        return (new BagistoThemeColors)->schema($attributes['customize'] ?? []);
    }

    public function isVisualTheme(): bool
    {
        return $this->isVisualTheme;
    }

    /**
     * How this theme is edited, which decides where its page structure is read
     * from and written to.
     */
    public function editorMode(): ThemeEditorMode
    {
        if ($this->isVisualTheme) {
            return ThemeEditorMode::Visual;
        }

        if (app(SectionSystem::class)->available()) {
            return ThemeEditorMode::Bagisto;
        }

        return ThemeEditorMode::Unsupported;
    }

    /**
     * Whether the visual editor is the one this theme is customized with, which
     * decides where Bagisto's own customize button leads.
     *
     * A theme states its choice as `customize.editor`. Left unsaid, a visual
     * theme is customized in the visual editor and any other theme in Bagisto's
     * appearance editor.
     */
    public function usesVisualEditor(): bool
    {
        if (! $this->editorMode()->isEditable()) {
            return false;
        }

        $declared = $this->customize['editor'] ?? null;

        if ($declared !== null) {
            return $declared === ThemeEditorMode::Visual->value;
        }

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
