<?php

namespace BagistoPlus\Visual\Tests\Fixtures\EarlyBoot;

use BagistoPlus\Visual\ThemeSettingsLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Boots before the package, the way a blocks package discovered earlier does,
 * and resolves the settings loader before the package has booted.
 */
class EarlyBootServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->make(ThemeSettingsLoader::class);
    }
}
