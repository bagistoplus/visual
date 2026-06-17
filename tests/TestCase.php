<?php

namespace BagistoPlus\Visual\Tests;

use BagistoPlus\Visual\Providers\VisualServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\FakeThemeServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Craftile\Laravel\CraftileServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use MallardDuck\LucideIcons\BladeLucideIconsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected static $latestResponse;

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
            BladeIconsServiceProvider::class,
            BladeLucideIconsServiceProvider::class,
            LivewireServiceProvider::class,
            CraftileServiceProvider::class,
            VisualServiceProvider::class,
            FakeThemeServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testbench');
            $config->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
            $config->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
            $config->set('cache.default', 'array');
        });
    }
}
