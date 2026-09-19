<?php

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Http\Replacers\FpcRegionReplacer;
use BagistoPlus\Visual\Providers\CoreServiceProvider;
use BagistoPlus\Visual\Support\FpcRegion;
use Illuminate\Http\Response;

function bootVisualResponseCache(): void
{
    (new ReflectionMethod(CoreServiceProvider::class, 'bootResponseCache'))
        ->invoke(new CoreServiceProvider(app()));
}

it('registers the replacer with the response cache', function (): void {
    config(['responsecache.replacers' => []]);

    bootVisualResponseCache();

    expect(config('responsecache.replacers'))->toContain(FpcRegionReplacer::class);
});

// boot() can run twice, and a duplicate would rewrite the document twice.
it('registers the replacer once however often the provider boots', function (): void {
    config(['responsecache.replacers' => []]);

    bootVisualResponseCache();
    bootVisualResponseCache();

    expect(array_count_values(config('responsecache.replacers')))
        ->toBe([FpcRegionReplacer::class => 1]);
});

it('keeps replacers the store configured for itself', function (): void {
    config(['responsecache.replacers' => ['App\\Replacers\\Existing']]);

    bootVisualResponseCache();

    expect(config('responsecache.replacers'))
        ->toBe(['App\\Replacers\\Existing', FpcRegionReplacer::class]);
});

// A cache hit renders no page, so the resolver has to be attached to whatever
// instance the container builds during the response, not during a render.
it('rebuilds a region registered through the facade, on a request that renders nothing', function (): void {
    Visual::registerFpcRegion('cart-count', fn (): string => '<span>4</span>');

    $response = new Response('<!--visual-fpc:cart-count--><!--/visual-fpc:cart-count-->');

    app(FpcRegionReplacer::class)->replaceInCachedResponse($response);

    expect($response->getContent())
        ->toBe('<!--visual-fpc:cart-count--><span>4</span><!--/visual-fpc:cart-count-->');
});

it('hands every caller the same region registry within one request', function (): void {
    expect(app(FpcRegion::class))->toBe(app(FpcRegion::class));
});
