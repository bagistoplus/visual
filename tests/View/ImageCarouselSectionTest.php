<?php

use Webkul\Theme\Sections\ImageCarousel;

/**
 * The options the section hands Bagisto's carousel, read off a stand-in that
 * prints them.
 */
function carouselOptions(array $data): array
{
    view()->addNamespace('shop', __DIR__.'/../Fixtures/shop');

    $section = new class
    {
        public function getTypeInstance(): ImageCarousel
        {
            return new ImageCarousel;
        }
    };

    $html = view('visual::bagisto.sections.image_carousel', ['section' => $section, 'data' => $data])->render();

    return json_decode(trim($html), true);
}

it('blanks a slide link that would run script, as bagisto does when it draws one', function () {
    $options = carouselOptions(['images' => [
        ['image' => 'themes/default/sections/1/a.webp', 'link' => 'javascript://%0aalert(1)'],
        ['image' => 'themes/default/sections/1/b.webp', 'link' => 'formal-wear-female'],
    ]]);

    expect($options['images'][0]['link'])->toBe('')
        ->and($options['images'][1]['link'])->toBe('formal-wear-female');
});
