<?php

namespace BagistoPlus\Visual\Tests;

use BagistoPlus\Visual\Providers\VisualServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\FakeThemeServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'BagistoPlus\\Visual\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            VisualServiceProvider::class,
            FakeThemeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Load Bagisto's concord configuration BEFORE providers boot
        $concordConfig = require dirname(__DIR__).'/vendor/bagisto/bagisto/config/concord.php';
        $app['config']->set('concord', $concordConfig);
    }

    public function getEnvironmentSetUp($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        });
    }

    public static function applicationBasePath()
    {
        return dirname(__DIR__).'/vendor/bagisto/bagisto';
    }
}
