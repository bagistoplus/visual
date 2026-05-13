<?php

use BagistoPlus\Visual\Providers\ViewServiceProvider;
use Illuminate\Foundation\Application;

it('registers Livewire features without resolving the Livewire container binding', function () {
    $app = new Application(dirname(__DIR__, 2));
    $provider = new ViewServiceProvider($app);

    expect($app->bound('livewire'))->toBeFalse();

    $registerLivewireFeatures = new ReflectionMethod($provider, 'registerLivewireFeatures');
    $registerLivewireFeatures->setAccessible(true);
    $registerLivewireFeatures->invoke($provider);

    expect($app->bound('livewire'))->toBeFalse();
});
