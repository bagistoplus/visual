<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

/**
 * The section's markup, and the styles it pushes, read off a page that prints
 * the stack: a stack is flushed once the outermost view is done rendering.
 */
function renderStaticContent(array $data): array
{
    $page = Blade::render(
        "@include('visual::bagisto.sections.static_content')<!--styles-->@stack('styles')",
        ['data' => $data],
    );

    return explode('<!--styles-->', $page);
}

it('draws the media a section embeds at the url the storefront reaches it by', function () {
    Storage::fake('public');
    config()->set('filesystems.default', 'public');

    [$html, $styles] = renderStaticContent([
        'html' => '<img data-src="__media__/themes/default/sections/8/game.webp">',
        'css' => '.hero{background:url(__media__/themes/default/sections/8/bg.webp)}',
    ]);

    expect($html)->toContain(Storage::url('themes/default/sections/8/game.webp'))
        ->not->toContain('__media__')
        ->and($styles)->toContain(Storage::url('themes/default/sections/8/bg.webp'))
        ->not->toContain('__media__');
});

it('draws the markup as written on a release whose storage cannot resolve it', function () {
    // 2.5.0-beta3 to beta5 ship the storage object, but store urls in the
    // markup and resolve nothing in it.
    app()->instance('Webkul\Theme\ThemeStorage', new class
    {
        public function embedUrl(string $path): string
        {
            return '/storage/'.$path;
        }
    });

    [$html] = renderStaticContent(['html' => '<img src="/storage/themes/default/sections/8/game.webp">']);

    expect(trim($html))->toBe('<img src="/storage/themes/default/sections/8/game.webp">');
});

it('leaves markup holding no media as it was written', function () {
    [$html] = renderStaticContent(['html' => '<h1 class="home-offer">Get 40% off</h1>']);

    expect(trim($html))->toBe('<h1 class="home-offer">Get 40% off</h1>');
});
