<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\LivewireFeatures\BlockDataSynth;
use BagistoPlus\Visual\LivewireFeatures\SupportsBlockData;
use BagistoPlus\Visual\LivewireFeatures\SupportsComponentAttributes;
use Craftile\Laravel\Facades\Craftile;

class VisualManager
{
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
     * @param  SettingTransformerInterface  $transformerClass  The transformer class
     */
    public function registerSettingTransformer(string $type, SettingTransformerInterface $transformerClass): void
    {
        Craftile::registerPropertyTransformer($type, $transformerClass);
    }

    /**
     * Enable Livewire support by adding persistent middleware.
     * Call this method in your theme's service provider boot method if you want to use Livewire components.
     *
     * Example usage in your theme's service provider:
     * ```php
     * public function boot(): void
     * {
     *     Visual::supportLivewire();
     * }
     * ```
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
