<?php

use BagistoPlus\Visual\Persistence\RenderPreview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A store installed under a path answers with a redirect to the address it
 * would render, which shows what that address carries.
 */
function renderedAddress(string $url, ?string $channel = null, ?string $locale = null): Request
{
    config()->set('app.url', 'http://localhost/shop');

    $response = app(RenderPreview::class)->execute($url, channel: $channel, locale: $locale);

    expect($response)->toBeInstanceOf(RedirectResponse::class);

    return Request::create($response->getTargetUrl());
}

it('renders in the locale and channel the edit was made in', function () {
    $request = renderedAddress('http://localhost/shop?_designMode=fake-theme', 'default', 'ar');

    expect($request->query('locale'))->toBe('ar')
        ->and($request->query('channel'))->toBe('default')
        ->and($request->query('_designMode'))->toBe('fake-theme');
});

it('overrides a locale the preview address already carries', function () {
    $request = renderedAddress('http://localhost/shop?_designMode=fake-theme&locale=en', 'default', 'ar');

    expect($request->query('locale'))->toBe('ar');
});

it('leaves the address alone when no locale or channel is given', function () {
    $request = renderedAddress('http://localhost/shop?_designMode=fake-theme');

    expect($request->query->has('locale'))->toBeFalse()
        ->and($request->query->has('channel'))->toBeFalse();
});
