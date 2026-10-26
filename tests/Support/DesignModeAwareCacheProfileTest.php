<?php

use BagistoPlus\Visual\Support\DesignModeAwareCacheProfile;
use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Symfony\Component\HttpFoundation\Response;

function designModeAwareProfile(?CacheProfile $inner = null): DesignModeAwareCacheProfile
{
    return new DesignModeAwareCacheProfile($inner ?? Mockery::mock(CacheProfile::class));
}

it('decorates the configured cache profile instead of replacing it', function () {
    expect(app(CacheProfile::class))->toBeInstanceOf(DesignModeAwareCacheProfile::class);
});

it('keeps the decoration when another package rebinds the cache profile', function () {
    $replacement = Mockery::mock(CacheProfile::class);
    app()->bind(CacheProfile::class, fn () => $replacement);

    expect(app(CacheProfile::class))->toBeInstanceOf(DesignModeAwareCacheProfile::class);
});

it('disables the response cache for editor requests', function (Request $request) {
    $inner = Mockery::mock(CacheProfile::class);
    $inner->shouldNotReceive('enabled');

    app()->instance('request', $request);

    expect(designModeAwareProfile($inner)->enabled($request))->toBeFalse();
})->with([
    'design mode query' => fn () => Request::create('/', 'GET', ['_designMode' => 'fake-theme']),
    'preview mode query' => fn () => Request::create('/', 'GET', ['_previewMode' => 'fake-theme']),
    'design mode header' => fn () => Request::create('/', 'GET', [], [], [], ['HTTP_X_VISUAL_EDITOR_THEME' => 'fake-theme']),
    'preview mode header' => fn () => Request::create('/', 'GET', [], [], [], ['HTTP_X_VISUAL_PREVIEW_THEME' => 'fake-theme']),
    'draft preview query' => fn () => Request::create('/', 'GET', ['_draftPreview' => 'fake-theme']),
    'draft preview header' => fn () => Request::create('/', 'GET', [], [], [], ['HTTP_X_VISUAL_DRAFT_PREVIEW_THEME' => 'fake-theme']),
]);

it('caches no page the editor asked for, whoever asked', function (string $url) {
    // The permission decides what the page may say, not whether it is cached:
    // a page cached for a visitor who may not edit would be served to the
    // merchant who may, without the editor in it.
    app()->instance('Webkul\\User\\Bouncer', new class
    {
        public function hasPermission(string $permission): bool
        {
            return false;
        }
    });

    $inner = Mockery::mock(CacheProfile::class);
    $inner->shouldNotReceive('enabled');

    $request = Request::create($url);
    app()->instance('request', $request);

    expect(designModeAwareProfile($inner)->enabled($request))->toBeFalse();
})->with([
    'design mode' => ['/?_designMode=default'],
    'draft preview' => ['/?_draftPreview=default'],
    'theme preview' => ['/?_previewMode=default'],
]);

it('defers to the wrapped profile outside the editor', function (bool $enabled) {
    $request = Request::create('/');
    app()->instance('request', $request);

    $inner = Mockery::mock(CacheProfile::class);
    $inner->shouldReceive('enabled')->once()->with($request)->andReturn($enabled);

    expect(designModeAwareProfile($inner)->enabled($request))->toBe($enabled);
})->with([true, false]);

it('delegates the remaining profile decisions untouched', function () {
    $request = Request::create('/');
    $response = new Response;

    $inner = Mockery::mock(CacheProfile::class);
    $inner->shouldReceive('shouldCacheRequest')->once()->with($request)->andReturnTrue();
    $inner->shouldReceive('shouldCacheResponse')->once()->with($response)->andReturnFalse();
    $inner->shouldReceive('useCacheNameSuffix')->once()->with($request)->andReturn('suffix');

    $profile = designModeAwareProfile($inner);

    expect($profile->shouldCacheRequest($request))->toBeTrue()
        ->and($profile->shouldCacheResponse($response))->toBeFalse()
        ->and($profile->useCacheNameSuffix($request))->toBe('suffix');
});
