<?php

namespace BagistoPlus\Visual\Tests\Fixtures\EarlyBoot;

use BagistoPlus\Visual\Providers\VisualServiceProvider;
use BagistoPlus\Visual\Tests\TestCase;

class EarlyBootTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        $providers = parent::getPackageProviders($app);
        $position = array_search(VisualServiceProvider::class, $providers, true);

        array_splice($providers, $position, 0, [EarlyBootServiceProvider::class]);

        return $providers;
    }
}
