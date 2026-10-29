<?php

use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\Theme\ThemeRenderingSetup;
use Craftile\Laravel\BlockSchemaRegistry;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Http\Request;

it('registers the discovered schemas of a visual theme and shares it with views', function () {
    $theme = Theme::make(['code' => 'fake-visual', 'name' => 'Fake Visual', 'visual_theme' => true]);

    Craftile::spy();

    (new ThemeRenderingSetup)->prepare($theme);

    Craftile::shouldHaveReceived('registerDiscoveredSchemas')->once();
    expect(view()->shared('theme'))->toBe($theme);
});

it('does not register the discovered schemas for a theme edited in bagisto mode', function () {
    fakeSectionSystem(available: true);

    Craftile::spy();

    (new ThemeRenderingSetup)->prepare(Theme::make(['code' => 'default', 'name' => 'Default']));

    Craftile::shouldNotHaveReceived('registerDiscoveredSchemas');
    expect(view()->shared('theme'))->toBeNull();
});

it('registers the section schemas of a bagisto theme while the editor is driving', function () {
    offerTypes(['image_carousel' => carouselType()]);

    app()->instance('request', Request::create('/?_designMode=default'));

    (new ThemeRenderingSetup)->prepare(Theme::make(['code' => 'default', 'name' => 'Default']));

    expect(collect(app(BlockSchemaRegistry::class)->all())->keys())
        ->toContain('image_carousel', 'image_carousel.images.item');
});

it('registers no section schema on a storefront request, which renders through bagisto', function () {
    offerTypes(['image_carousel' => carouselType()]);

    app()->instance('request', Request::create('/'));

    (new ThemeRenderingSetup)->prepare(Theme::make(['code' => 'default', 'name' => 'Default']));

    expect(collect(app(BlockSchemaRegistry::class)->all())->keys())
        ->not->toContain('image_carousel');
});
