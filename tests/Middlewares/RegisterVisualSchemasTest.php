<?php

use BagistoPlus\Visual\Middlewares\RegisterVisualSchemas;
use BagistoPlus\Visual\Theme\Theme;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Http\Request;
use Webkul\Theme\Facades\Themes as ThemesFacade;

function bindThemeForVisualSchemaRegistration(mixed $theme): void
{
    ThemesFacade::swap(new class($theme)
    {
        public function __construct(protected mixed $theme) {}

        public function current(): mixed
        {
            return $this->theme;
        }
    });
}

it('registers deferred schemas before handling a visual storefront request', function () {
    bindThemeForVisualSchemaRegistration(Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]));

    Craftile::spy();

    $response = (new RegisterVisualSchemas)->handle(Request::create('/'), fn () => 'next');

    expect($response)->toBe('next');
    Craftile::shouldHaveReceived('registerDiscoveredSchemas')->once();
});

it('does not register deferred schemas when the current theme is not visual', function () {
    bindThemeForVisualSchemaRegistration(Theme::make([
        'code' => 'default',
        'name' => 'Default',
        'visual_theme' => false,
    ]));

    Craftile::spy();

    $response = (new RegisterVisualSchemas)->handle(Request::create('/'), fn () => 'next');

    expect($response)->toBe('next');
    Craftile::shouldNotHaveReceived('registerDiscoveredSchemas');
});
