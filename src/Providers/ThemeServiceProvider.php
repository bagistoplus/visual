<?php

namespace BagistoPlus\Visual\Providers;

use BagistoPlus\Visual\Events\ServingThemeEditor;
use BagistoPlus\Visual\Events\ThemeActivated;
use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use RuntimeException;

abstract class ThemeServiceProvider extends ServiceProvider
{
    protected static array $commands = [];

    /**
     * The base path of the theme.
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * The configuration for the theme.
     */
    protected array $config = [];

    /**
     * Register any services.
     */
    public function register(): void
    {
        //
        $this->registerThemeConfig();
    }

    /**
     * Bootstrap any services.
     */
    public function boot(): void
    {
        $this->bootViewsAndTranslations();
        $this->bootSections();

        if ($this->app->runningInConsole()) {
            $this->bootPublishAssets();
            $this->bootCommands();
        }
    }

    protected function bootViewsAndTranslations()
    {
        $theme = $this->loadThemeConfig();

        $this->loadViewsFrom($this->getBasePath().'/resources/views', $theme['code']);
        $this->loadTranslationsFrom($this->getBasePath().'/resources/lang', $theme['code']);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getBasePath().'/resources/views' => resource_path("themes/{$theme['code']}/views"),
            ], [$theme['code'], $theme['code'].'-views']);
        }
    }

    protected function bootSections()
    {
        $this->whenActive(function (Theme $theme) {
            Visual::discoverSectionsIn("{$theme->basePath}/src/Sections", $theme->code);
        });
    }

    /**
     * Publish the assets of the theme.
     *
     * @return void
     */
    protected function bootPublishAssets()
    {
        $theme = $this->loadThemeConfig();

        $this->publishes(
            [
                $this->getBasePath().'/'.$theme['assets_path'] => base_path($theme['assets_path']),
            ],
            [
                'public',
                $theme['code'],
                "{$theme['code']}-assets",
            ]
        );
    }

    protected function bootCommands()
    {
        $this->commands(static::$commands);
    }

    /**
     * Register the theme configuration.
     *
     * @return void
     */
    protected function registerThemeConfig()
    {
        $config = $this->loadThemeConfig();

        $this->mergeConfigFromArray('themes.shop', [
            $config['code'] => $config,
        ]);

        $this->mergeConfigFromArray('bagisto-vite.viters', [
            $config['code'] => $config['vite'],
        ]);
    }

    protected function loadThemeConfig(): array
    {
        if (! empty($this->config)) {
            return $this->config;
        }

        $config = require $this->getThemeConfigPath();

        $config['base_path'] = $this->getBasePath();
        $config['visual_theme'] = true;
        $config['settings_schema'] = $this->loadSettingsSchema();

        return $this->config = $config;
    }

    protected function loadSettingsSchema(): array
    {
        $schemaPath = $this->getThemeSettingsPath();

        if (! file_exists($schemaPath)) {
            return [];
        }

        $settings = require $schemaPath;

        return collect($settings)->map(function ($group) {
            $group['settings'] = collect($group['settings'])->toArray();

            return $group;
        })->toArray();
    }

    /**
     * Register a callback to be run when the theme is activated
     *
     * @return void
     */
    protected function whenActive(\Closure $callback)
    {
        Event::listen(ServingThemeEditor::class, function () use ($callback) {
            Event::listen(RouteMatched::class, function ($event) use ($callback) {
                $themeConfig = $this->loadThemeConfig();

                if ($themeConfig && $themeConfig['code'] === $event->route->parameters['theme']) {
                    $callback(Theme::make($themeConfig));
                }
            });
        });

        Event::listen(ThemeActivated::class, function (ThemeActivated $event) use ($callback) {
            if ($event->theme->code === $this->loadThemeConfig()['code']) {
                $callback($event->theme);
            }
        });
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
        return $this->getBasePath().'/config/settings.php';
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
