<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Support\Facades\Cache;

/**
 * The themes made by duplicating a visual theme, as the data store holds them
 * for the current storage scope.
 *
 * A copy has no package of its own. It is registered under its own code with
 * what its parent's config declares, so it renders with the parent's code and
 * its own data. A copy whose parent is not registered is left out, and a
 * channel set to it falls back to the default theme.
 */
class ThemeCopies
{
    protected const TTL = 86400;

    /**
     * What each copy's data says about it, keyed by the copy's code.
     *
     * @return array<string, array{parent: string, name: string, description?: ?string, created_at?: string}>
     */
    public function all(): array
    {
        return Cache::remember($this->cacheKey(), self::TTL, function () {
            $store = app(ThemeDataStore::class);

            return collect($store->themes())
                ->mapWithKeys(fn (string $code) => [$code => $store->meta($code)])
                ->filter(fn (?array $meta) => filled($meta['parent'] ?? null) && filled($meta['name'] ?? null))
                ->all();
        });
    }

    public function isCopy(string $code): bool
    {
        return array_key_exists($code, $this->all());
    }

    /**
     * Copies whose parent is not registered, so they cannot be rendered.
     *
     * @return array<string, array{parent: string, name: string, description?: ?string, created_at?: string}>
     */
    public function unavailable(): array
    {
        return array_filter($this->all(), fn (array $meta) => ! $this->parentIsRegistered($meta['parent']));
    }

    /**
     * Set every copy into the config, which stays as cached when the config is,
     * so this runs on every boot.
     */
    public function register(): void
    {
        foreach ($this->all() as $code => $meta) {
            if (! $this->parentIsRegistered($meta['parent'])) {
                continue;
            }

            config()->set("themes.shop.{$code}", array_merge(config("themes.shop.{$meta['parent']}"), [
                'code' => $code,
                'name' => $meta['name'],
                'description' => $meta['description'] ?? null,
                'parent' => $meta['parent'],
            ]));

            $viter = config("bagisto-vite.viters.{$meta['parent']}");

            if ($viter !== null) {
                config()->set("bagisto-vite.viters.{$code}", $viter);
            }
        }
    }

    public function forget(): void
    {
        Cache::forget($this->cacheKey());
    }

    protected function parentIsRegistered(string $parent): bool
    {
        $config = config("themes.shop.{$parent}");

        return is_array($config) && ($config['visual_theme'] ?? false) && empty($config['parent']);
    }

    /**
     * Keyed by storage driver too, so switching drivers never serves the
     * copies the other one holds.
     */
    protected function cacheKey(): string
    {
        $scope = Visual::storageScope();

        return 'visual:theme-copies:'.config('bagisto_visual.storage').':'.($scope === '' ? '_' : $scope);
    }
}
