<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\LivewireFeatures\BlockDataSynth;
use BagistoPlus\Visual\LivewireFeatures\SupportsBlockData;
use BagistoPlus\Visual\Middlewares\RegisterVisualSchemas;
use BagistoPlus\Visual\Middlewares\UseShopThemeFromRequest;
use BagistoPlus\Visual\Support\FpcRegion;
use Closure;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Webkul\Shop\Http\Middleware\Currency;
use Webkul\Shop\Http\Middleware\Locale;

class VisualManager
{
    protected array $livewireContextFilters = [];

    public function __construct(protected ThemeSettingsLoader $themeSettingsLoader) {}

    /**
     * Rebuild one personalised part of a page after the full page cache has
     * served it to somebody else.
     *
     * The resolver runs for the visitor being served, which on a cache hit is a
     * request that renders no page at all, so it must reach for what it needs
     * rather than receive it.
     *
     * @param  Closure(): string  $resolver
     */
    public function registerFpcRegion(string $key, Closure $resolver): void
    {
        app()->resolving(
            FpcRegion::class,
            fn (FpcRegion $regions) => $regions->resolve($key, $resolver)
        );
    }

    public function themeSettingsLoader(): ThemeSettingsLoader
    {
        return $this->themeSettingsLoader;
    }

    public function imagesDisk(): string
    {
        return (string) config('bagisto_visual.images.storage', config('bagisto_visual.images_storage'));
    }

    public function imagesDirectory(): string
    {
        return (string) config('bagisto_visual.images.directory', config('bagisto_visual.images_directory'));
    }

    public function videosDisk(): string
    {
        return (string) config('bagisto_visual.videos.storage');
    }

    public function videosDirectory(): string
    {
        return (string) config('bagisto_visual.videos.directory');
    }

    public function videosMaxUploadSize(): int
    {
        return (int) config('bagisto_visual.videos.max_upload_size', 51200);
    }

    public function templateAssignmentsEnabled(): bool
    {
        return (bool) config('bagisto_visual.template_assignments', false);
    }

    public function discoverSectionsIn(string $path, string $namespace = 'App\\Sections'): void
    {
        Craftile::discoverBlocksIn($namespace, $path);
    }

    public function discoverBlocksIn(string $path, string $namespace = 'App\\Blocks'): void
    {
        Craftile::discoverBlocksIn($namespace, $path);
    }

    public function discoverPresetsIn(string $path, string $namespace = 'App\\Presets'): void
    {
        Craftile::discoverPresetsIn($namespace, $path);
    }

    /**
     * Register a single section.
     *
     * @param  string  $sectionClass  The section class to register
     */
    public function registerSection(string $sectionClass): void
    {
        Craftile::registerBlock($sectionClass);
    }

    /**
     * Register multiple sections.
     *
     * @param  array  $sectionClasses  Array of section classes to register
     */
    public function registerSections(array $sectionClasses): void
    {
        Craftile::registerBlocks($sectionClasses);
    }

    /**
     * Register a single block.
     *
     * @param  string  $blockClass  The block class to register
     */
    public function registerBlock(string $blockClass): void
    {
        Craftile::registerBlock($blockClass);
    }

    /**
     * Register multiple blocks.
     *
     * @param  array  $blockClasses  Array of block classes to register
     */
    public function registerBlocks(array $blockClasses): void
    {
        Craftile::registerBlocks($blockClasses);
    }

    /**
     * Register a custom setting transformer.
     *
     * @param  string  $type  The setting type to transform
     * @param  SettingTransformerInterface  $transformerClass  The transformer class
     */
    public function registerSettingTransformer(string $type, SettingTransformerInterface $transformerClass): void
    {
        Craftile::registerPropertyTransformer($type, $transformerClass);
    }

    /**
     * Register a custom filter for Livewire block context.
     *
     * @param  callable(Collection): Collection  $filter
     */
    public function filterLivewireContextUsing(callable $filter): void
    {
        $this->livewireContextFilters[] = $filter;
    }

    /**
     * Get all registered Livewire context filters.
     *
     * @return array<callable>
     */
    public function getLivewireContextFilters(): array
    {
        return $this->livewireContextFilters;
    }

    /**
     * Enable Livewire support by adding persistent middleware.
     */
    public function supportLivewire(): void
    {
        if (! class_exists(Livewire::class)) {
            throw new \RuntimeException('Livewire is not installed. Please install it first: composer require livewire/livewire');
        }

        Livewire::addPersistentMiddleware([
            Locale::class,
            Currency::class,
            UseShopThemeFromRequest::class,
            RegisterVisualSchemas::class,
        ]);

        Livewire::propertySynthesizer(BlockDataSynth::class);

        Livewire::componentHook(SupportsBlockData::class);
    }
}
