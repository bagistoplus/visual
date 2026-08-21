<?php

use BagistoPlus\Visual\Middlewares\RedirectVisualThemeCustomization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    config()->set('app.admin_url', 'admin');

    config()->set('themes.shop.visual-demo', [
        'code' => 'visual-demo',
        'name' => 'Visual Demo',
        'visual_theme' => true,
    ]);

    config()->set('themes.shop.classic', [
        'code' => 'classic',
        'name' => 'Classic',
    ]);
});

function handleCustomizationRequest(string $uri, string $method = 'GET')
{
    return app(RedirectVisualThemeCustomization::class)->handle(
        Request::create($uri, $method),
        fn () => new Response('next')
    );
}

it('redirects the bagisto customize link of a visual theme to the visual editor', function () {
    $response = handleCustomizationRequest('/admin/appearance/themes/visual-demo/sections');

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('location'))
        ->toBe(route('visual.admin.editor', ['theme' => 'visual-demo']));
});

it('lets bagisto handle the customize link of a classic theme', function () {
    $response = handleCustomizationRequest('/admin/appearance/themes/classic/sections');

    expect($response->getContent())->toBe('next');
});

it('ignores unknown theme codes', function () {
    $response = handleCustomizationRequest('/admin/appearance/themes/unknown/sections');

    expect($response->getContent())->toBe('next');
});

it('ignores section write requests', function () {
    $response = handleCustomizationRequest('/admin/appearance/themes/visual-demo/sections', 'POST');

    expect($response->getContent())->toBe('next');
});

it('ignores nested section routes', function () {
    $response = handleCustomizationRequest('/admin/appearance/sections/12/fields');

    expect($response->getContent())->toBe('next');
});

it('ignores requests outside the admin prefix', function () {
    $response = handleCustomizationRequest('/appearance/themes/visual-demo/sections');

    expect($response->getContent())->toBe('next');
});
