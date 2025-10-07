<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\LivewireFeatures\BlockDataSynth;
use BagistoPlus\Visual\LivewireFeatures\SupportsBlockData;
use BagistoPlus\Visual\LivewireFeatures\SupportsComponentAttributes;
use Craftile\Laravel\Facades\Craftile;

class VisualManager
{
    protected array $livewireContextFilters = [];

    public function __construct(protected ThemeSettingsLoader $themeSettingsLoader) {}

    public function themeSettingsLoader(): ThemeSettingsLoader
    {
        return $this->themeSettingsLoader;
    }

    public function discoverSectionsIn(string $path, string $namespace = 'App\\Sections'): void
    {
        Craftile::discoverBlocksIn($namespace, $path);
    }

    public function discoverBlocksIn(string $path, string $namespace = 'App\\Blocks'): void
    {
        Craftile::discoverBlocksIn($namespace, $path);
    }

    /**
     * Register a custom setting transformer.
     *
     * @param  string  $type  The setting type to transform
     * @param  \BagistoPlus\Visual\Contracts\SettingTransformerInterface  $transformerClass  The transformer class
     */
    public function registerSettingTransformer(string $type, SettingTransformerInterface $transformerClass): void
    {
        Craftile::registerPropertyTransformer($type, $transformerClass);
    }

    /**
     * Register a custom filter for Livewire block context.
     *
     * @param  callable(\Illuminate\Support\Collection): \Illuminate\Support\Collection  $filter
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
        if (! class_exists(\Livewire\Livewire::class)) {
            throw new \RuntimeException('Livewire is not installed. Please install it first: composer require livewire/livewire');
        }

        \Livewire\Livewire::addPersistentMiddleware([
            \Webkul\Shop\Http\Middleware\Locale::class,
            \Webkul\Shop\Http\Middleware\Currency::class,
            \Webkul\Shop\Http\Middleware\Theme::class,
        ]);

        \Livewire\Livewire::propertySynthesizer(BlockDataSynth::class);

        \Livewire\Livewire::componentHook(SupportsBlockData::class);
        \Livewire\Livewire::componentHook(SupportsComponentAttributes::class);
    }
}
