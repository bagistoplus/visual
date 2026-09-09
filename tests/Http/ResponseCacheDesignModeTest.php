<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Spatie\ResponseCache\Hasher\RequestHasher;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Serializers\JsonSerializer;

/**
 * Mirrors Bagisto's hasher, which drops the query string, so the editor request and the public
 * request compete for the same cache entry.
 */
class QueryAgnosticRequestHasher implements RequestHasher
{
    public function getHashFor(Request $request): string
    {
        return hash('xxh128', $request->getHost().$request->getPathInfo().$request->getMethod());
    }
}

beforeEach(function () {
    config([
        'responsecache.enabled' => true,
        'responsecache.cache.store' => 'array',
        'responsecache.cache.tag' => '',
        'responsecache.cache.lifetime_in_seconds' => 3600,
        'responsecache.cache_profile' => CacheAllSuccessfulGetRequests::class,
        'responsecache.replacers' => [],
        'responsecache.serializer' => JsonSerializer::class,
        'responsecache.debug.enabled' => false,
        'responsecache.bypass.header_name' => null,
        'responsecache.bypass.header_value' => null,
    ]);

    $this->app->bind(RequestHasher::class, QueryAgnosticRequestHasher::class);

    // The response cache middleware is placed first on purpose: Laravel's middleware priority list
    // can hoist it ahead of the shop group, so the editor bypass must not depend on ordering.
    Route::get('/response-cache-design-mode', fn () => Str::random(16))
        ->middleware([CacheResponse::class]);
});

it('caches the storefront response for regular visitors', function () {
    $first = $this->get('/response-cache-design-mode')->getContent();
    $second = $this->get('/response-cache-design-mode')->getContent();

    expect($second)->toBe($first);
});

it('never serves a cached response to the theme editor', function (array $parameters, array $headers) {
    $cached = $this->get('/response-cache-design-mode')->getContent();

    $editor = $this
        ->withHeaders($headers)
        ->get('/response-cache-design-mode?'.http_build_query($parameters))
        ->getContent();

    expect($editor)->not->toBe($cached);
})->with([
    'design mode' => [['_designMode' => 'fake-theme'], []],
    'preview mode' => [['_previewMode' => 'fake-theme'], []],
    'design mode header' => [[], ['x-visual-editor-theme' => 'fake-theme']],
    'preview mode header' => [[], ['x-visual-preview-theme' => 'fake-theme']],
]);

it('never writes an editor response to the cache', function () {
    $editor = $this->get('/response-cache-design-mode?_designMode=fake-theme')->getContent();
    $visitor = $this->get('/response-cache-design-mode')->getContent();

    expect($visitor)->not->toBe($editor);
});
