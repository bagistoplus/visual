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
use Spatie\ResponseCache\ResponseCacheServiceProvider;
use Webkul\Core\Facades\Core;

class TestCase extends Orchestra
{
    protected static $latestResponse;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'BagistoPlus\\Visual\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        Core::swap(core());

        $this->allowEditing();
    }

    /**
     * Answer yes to every permission question.
     *
     * The editor is offered to whoever may edit a theme, which Bagisto answers
     * through its own guard against an administrator session this suite has
     * none of. A test that cares about the answer binds its own.
     */
    protected function allowEditing(): void
    {
        $this->app->instance('Webkul\\User\\Bouncer', new class
        {
            public function hasPermission(string $permission): bool
            {
                return true;
            }
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeIconsServiceProvider::class,
            BladeLucideIconsServiceProvider::class,
            LivewireServiceProvider::class,
            CraftileServiceProvider::class,
            ResponseCacheServiceProvider::class,
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
            $config->set('blade-icons.components.disabled', true);
        });
    }
}
