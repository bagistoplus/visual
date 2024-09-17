<?php

namespace BagistoPlus\Visual\Providers;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use RuntimeException;

abstract class ThemeServiceProvider extends ServiceProvider
{
    protected $basePath = '';

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerThemeConfig();
    }

    protected function registerThemeConfig()
    {
        $config = require $this->getThemeConfigPath();
        $config['visual_theme'] = true;

        $this->mergeConfigFromArray('themes.shop', [
            $config['code'] => $config,
        ]);
    }

    /**
     * Get the base directory of the package by traversing upwards to find composer.json.
     *
     * @throws RuntimeException
     */
    public function getBasePath(): string
    {
        if ($this->basePath) {
            return $this->basePath;
        }

        // Start from the current directory (where the ServiceProvider is located)
        $reflector = new ReflectionClass(get_class($this));
        $dir = dirname($reflector->getFileName());

        // Traverse upwards until we find composer.json
        while (! file_exists($dir.'/composer.json')) {
            // Move one level up
            $dir = dirname($dir);

            // If we reach the root directory and still don't find composer.json, throw an error
            if ($dir === '/') {
                throw new RuntimeException('Unable to locate the base directory of the package.');
            }
        }

        // Return the directory where composer.json is found (the package root)
        return $this->basePath = $dir;
    }

    public function getThemeConfigPath(): string
    {
        return $this->getBasePath().'/config/theme.php';
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFromArray($key, array $config)
    {
        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $configBag = $this->app->make('config');

            $configBag->set($key, array_merge(
                $config,
                $configBag->get($key, [])
            ));
        }
    }
}
