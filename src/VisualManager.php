<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\LivewireFeatures\BlockDataSynth;
use BagistoPlus\Visual\LivewireFeatures\SupportsBlockData;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Webkul\Shop\Http\Middleware\Currency;
use Webkul\Shop\Http\Middleware\Locale;
use Webkul\Shop\Http\Middleware\Theme;

class VisualManager
{
    protected array $livewireContextFilters = [];

    public function __construct(protected ThemeSettingsLoader $themeSettingsLoader) {}

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
            Theme::class,
        ]);

        Livewire::propertySynthesizer(BlockDataSynth::class);

        Livewire::componentHook(SupportsBlockData::class);
    }
}
