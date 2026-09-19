<?php

use BagistoPlus\Visual\Support\FpcRegion;
use Illuminate\Support\Facades\Blade;

it('wraps the body of a region in its markers', function (): void {
    $html = Blade::render(<<<'BLADE'
        @fpcRegion('cart-count')<span>3</span>@endFpcRegion
    BLADE);

    expect(trim($html))
        ->toBe('<!--visual-fpc:cart-count--><span>3</span><!--/visual-fpc:cart-count-->');
});

it('takes the key from an expression, not only a literal', function (): void {
    $html = Blade::render(
        '@fpcRegion($key)<span>3</span>@endFpcRegion',
        ['key' => 'wishlist-count']
    );

    expect(trim($html))
        ->toBe('<!--visual-fpc:wishlist-count--><span>3</span><!--/visual-fpc:wishlist-count-->');
});

it('emits a once region for the first caller and nothing for the next', function (): void {
    $html = Blade::render(<<<'BLADE'
        @fpcRegionOnce('shopper-state')<span>3</span>@endFpcRegionOnce
        @fpcRegionOnce('shopper-state')<span>3</span>@endFpcRegionOnce
    BLADE);

    expect(substr_count($html, '<!--visual-fpc:shopper-state-->'))->toBe(1);
});

// The whole point of a directive over a component: the second caller's body is
// never reached, rather than built and thrown away.
it('does not run the body of a once region for a later caller', function (): void {
    $calls = 0;

    $count = function () use (&$calls): string {
        $calls++;

        return '3';
    };

    Blade::render(<<<'BLADE'
        @fpcRegionOnce('shopper-state')<span>{{ $count() }}</span>@endFpcRegionOnce
        @fpcRegionOnce('shopper-state')<span>{{ $count() }}</span>@endFpcRegionOnce
    BLADE, ['count' => $count]);

    expect($calls)->toBe(1);
});

it('shares its ledger with the php api, so neither emits twice', function (): void {
    app(FpcRegion::class)->wrapOnce('shopper-state', fn (): string => '<span>3</span>');

    $html = Blade::render(<<<'BLADE'
        @fpcRegionOnce('shopper-state')<span>3</span>@endFpcRegionOnce
    BLADE);

    expect(trim($html))->toBe('');
});
