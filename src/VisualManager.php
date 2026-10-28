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
use InvalidArgumentException;
use Livewire\Livewire;
use Webkul\Shop\Http\Middleware\Currency;
use Webkul\Shop\Http\Middleware\Locale;

class VisualManager
{
    protected array $livewireContextFilters = [];

    protected ?Closure $storageScopeResolver = null;

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

    /**
     * Keep theme data, its cached settings and uploaded media apart per scope,
     * such as a tenant.
     *
     * Visual never decides what a scope is. The resolver is asked every time a
     * scope is needed, so it follows whatever the host app has switched to.
     * Returning null or an empty string means no scope.
     *
     * @param  (Closure(): (string|int|null))|null  $resolver
     */
    public function resolveStorageScopeUsing(?Closure $resolver): void
    {
        $this->storageScopeResolver = $resolver;
    }

    /**
     * The scope theme data is kept under, or an empty string for none.
     *
     * It becomes a directory name and part of cache keys, so it may only hold
     * letters, numbers, dots, dashes and underscores, and must start with a
     * letter or number.
     */
    public function storageScope(): string
    {
        if (! $this->storageScopeResolver) {
            return '';
        }

        $scope = (string) (($this->storageScopeResolver)() ?? '');

        $this->ensureValidStorageScope($scope);

        return $scope;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function ensureValidStorageScope(string $scope): void
    {
        if ($scope !== '' && ! $this->isValidStorageScope($scope)) {
            throw new InvalidArgumentException("The storage scope [{$scope}] may only contain letters, numbers, dots, dashes and underscores, and must start with a letter or number.");
        }
    }

    public function isValidStorageScope(string $scope): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $scope) === 1;
    }

    /**
     * Run a callback under a given scope, then put back the resolver the host
     * app set, as a command copying one tenant's data needs.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function withStorageScope(string $scope, callable $callback): mixed
    {
        $previous = $this->storageScopeResolver;
        $this->storageScopeResolver = fn () => $scope;

        try {
            return $callback();
        } finally {
            $this->storageScopeResolver = $previous;
        }
    }

    public function imagesDisk(): string
    {
        return (string) config('bagisto_visual.images.storage', config('bagisto_visual.images_storage'));
    }

    public function imagesDirectory(): string
    {
        return $this->scopedDirectory((string) config('bagisto_visual.images.directory', config('bagisto_visual.images_directory')));
    }

    public function videosDisk(): string
    {
        return (string) config('bagisto_visual.videos.storage');
    }

    public function videosDirectory(): string
    {
        return $this->scopedDirectory((string) config('bagisto_visual.videos.directory'));
    }

    protected function scopedDirectory(string $directory): string
    {
        $scope = $this->storageScope();

        if ($scope === '') {
            return $directory;
        }

        return $scope.'/'.ltrim($directory, '/');
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
