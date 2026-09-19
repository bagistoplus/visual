<?php

use BagistoPlus\Visual\Http\Replacers\FpcRegionReplacer;
use BagistoPlus\Visual\Support\FpcRegion;
use Illuminate\Http\Response;

function fpcResponse(string $content): Response
{
    return new Response($content);
}

function fpcReplacer(FpcRegion $regions): FpcRegionReplacer
{
    return new FpcRegionReplacer($regions);
}

it('wraps a region in paired markers', function (): void {
    expect((new FpcRegion)->wrap('cart-count', '<span>3</span>'))
        ->toBe('<!--visual-fpc:cart-count--><span>3</span><!--/visual-fpc:cart-count-->');
});

it('empties every region before the response is cached', function (): void {
    $regions = new FpcRegion;

    $content = '<header>'
        .$regions->wrap('cart-count', '<span>3</span>')
        .'<nav>keep me</nav>'
        .$regions->wrap('wishlist-count', '<span>9</span>')
        .'</header>';

    $response = fpcResponse($content);
    fpcReplacer($regions)->prepareResponseToCache($response);

    expect($response->getContent())
        ->toBe('<header>'
            .'<!--visual-fpc:cart-count--><!--/visual-fpc:cart-count-->'
            .'<nav>keep me</nav>'
            .'<!--visual-fpc:wishlist-count--><!--/visual-fpc:wishlist-count-->'
            .'</header>');
});

it('rebuilds each region from its own resolver when a cached page is served', function (): void {
    $regions = new FpcRegion;
    $regions->resolve('cart-count', fn (): string => '<span>4</span>');
    $regions->resolve('wishlist-count', fn (): string => '<span>2</span>');

    $response = fpcResponse(
        '<!--visual-fpc:cart-count--><!--/visual-fpc:cart-count-->'
        .'<!--visual-fpc:wishlist-count--><!--/visual-fpc:wishlist-count-->'
    );

    fpcReplacer($regions)->replaceInCachedResponse($response);

    expect($response->getContent())
        ->toBe('<!--visual-fpc:cart-count--><span>4</span><!--/visual-fpc:cart-count-->'
            .'<!--visual-fpc:wishlist-count--><span>2</span><!--/visual-fpc:wishlist-count-->');
});

it('resolves a repeated region once', function (): void {
    $calls = 0;

    $regions = new FpcRegion;
    $regions->resolve('cart-count', function () use (&$calls): string {
        $calls++;

        return '<span>4</span>';
    });

    $response = fpcResponse(
        '<!--visual-fpc:cart-count--><!--/visual-fpc:cart-count-->'
        .'<!--visual-fpc:cart-count--><!--/visual-fpc:cart-count-->'
    );

    fpcReplacer($regions)->replaceInCachedResponse($response);

    expect($calls)->toBe(1)
        ->and(substr_count((string) $response->getContent(), '<span>4</span>'))->toBe(2);
});

it('leaves a document without regions untouched', function (): void {
    $replacer = fpcReplacer(new FpcRegion);

    $prepared = fpcResponse('<header>no regions here</header>');
    $replacer->prepareResponseToCache($prepared);

    $served = fpcResponse('<header>no regions here</header>');
    $replacer->replaceInCachedResponse($served);

    expect($prepared->getContent())->toBe('<header>no regions here</header>')
        ->and($served->getContent())->toBe('<header>no regions here</header>');
});

it('empties an unbalanced marker without swallowing the rest of the document', function (): void {
    $content = '<!--visual-fpc:cart-count--><span>3</span><!--/visual-fpc:wishlist-count--><footer>keep me</footer>';

    $response = fpcResponse($content);
    fpcReplacer(new FpcRegion)->prepareResponseToCache($response);

    expect($response->getContent())->toContain('<footer>keep me</footer>');
});

it('empties a region a resolver does not know', function (): void {
    $response = fpcResponse('<!--visual-fpc:cart-count--><span>3</span><!--/visual-fpc:cart-count-->');
    fpcReplacer(new FpcRegion)->replaceInCachedResponse($response);

    expect($response->getContent())
        ->toBe('<!--visual-fpc:cart-count--><!--/visual-fpc:cart-count-->');
});

// A page needs one of these however many blocks ask for it, and a block is its
// own top level render, which is what Blade's @once cannot see.
it('emits a once region a single time, and builds its payload no more', function (): void {
    $calls = 0;

    $regions = new FpcRegion;
    $payload = function () use (&$calls): string {
        $calls++;

        return '<span>3</span>';
    };

    $first = $regions->wrapOnce('cart-count', $payload);
    $second = $regions->wrapOnce('cart-count', $payload);

    expect($first)->toBe('<!--visual-fpc:cart-count--><span>3</span><!--/visual-fpc:cart-count-->')
        ->and($second)->toBe('')
        ->and($calls)->toBe(1);
});

it('keeps each key when one region is opened inside another', function (): void {
    $regions = new FpcRegion;

    $regions->begin('outer');
    echo '<header>';
    $regions->begin('inner');
    echo '<span>3</span>';
    $inner = $regions->end();
    echo $inner.'</header>';
    $outer = $regions->end();

    expect($inner)->toBe('<!--visual-fpc:inner--><span>3</span><!--/visual-fpc:inner-->')
        ->and($outer)->toBe(
            '<!--visual-fpc:outer--><header>'
            .'<!--visual-fpc:inner--><span>3</span><!--/visual-fpc:inner-->'
            .'</header><!--/visual-fpc:outer-->'
        );
});

it('captures nothing for a caller that is not the first to claim a key', function (): void {
    $regions = new FpcRegion;

    expect($regions->beginOnce('cart-count'))->toBeTrue();

    echo '<span>3</span>';

    expect($regions->end())->toBe('<!--visual-fpc:cart-count--><span>3</span><!--/visual-fpc:cart-count-->')
        ->and($regions->beginOnce('cart-count'))->toBeFalse();
});
