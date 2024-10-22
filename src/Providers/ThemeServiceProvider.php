<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\JsonSchemaValidator;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use RuntimeException;

abstract class ThemeServiceProvider extends ServiceProvider
{
    /**
     * The base path of the theme.
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * Register any services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any services.
     */
    public function boot(): void
    {
        $this->registerThemeConfig();
    }

    /**
     * Register the theme configuration.
     *
     * @return void
     */
    protected function registerThemeConfig()
    {
        $config = require $this->getThemeConfigPath();
        $config['visual_theme'] = true;
        $config['settings_schema'] = $this->loadSettingsSchema();

        $this->mergeConfigFromArray('themes.shop', [
            $config['code'] => $config,
        ]);
    }

    protected function loadSettingsSchema(): array
    {
        $schemaPath = $this->getThemeSettingsPath();

        if (! file_exists($schemaPath)) {
            return [];
        }

        JsonSchemaValidator::validateThemeSettingsSchema($schemaPath);

        return json_decode(file_get_contents($schemaPath), true);
    }

    /**
     * Get the base directory of the package by traversing upwards to find composer.json.
     *
     * @throws RuntimeException If composer.json is not found.
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

        return $this->basePath = $dir;
    }

    /**
     * Get the path to the theme configuration file.
     */
    public function getThemeConfigPath(): string
    {
        return $this->getBasePath().'/config/theme.php';
    }

    /**
     * Get the path to the theme settings schema file.
     */
    public function getThemeSettingsPath(): string
    {
        return $this->getBasePath().'/config/settings.json';
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFromArray($key, array $config)
    {
        // Only merge configuration if the app's configuration isn't cached.
        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $configBag = $this->app->make('config');

            $configBag->set($key, array_merge(
                $config,
                $configBag->get($key, [])
            ));
        }
    }
}
