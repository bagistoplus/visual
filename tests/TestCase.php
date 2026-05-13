<?php

namespace BagistoPlus\Visual\Tests;

use BagistoPlus\Visual\Providers\VisualServiceProvider;
use BagistoPlus\Visual\Tests\Fixtures\FakeTheme\FakeThemeServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Konekt\Concord\ConcordServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Webkul\Core\Core;

require_once __DIR__.'/../vendor/bagisto/bagisto/packages/Webkul/Core/src/Http/helpers.php';
require_once __DIR__.'/../vendor/bagisto/bagisto/packages/Webkul/Theme/src/Http/helpers.php';

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
            ConcordServiceProvider::class,
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
        $app->instance(Core::class, new class
        {
            public function version(): string
            {
                return '2.4.0';
            }
        });

        tap($app['config'], function (Repository $config) {
            $config->set('app.admin_url', 'admin');
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
        return parent::applicationBasePath();
    }
}
