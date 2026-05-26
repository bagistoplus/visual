<?php

use BagistoPlus\Visual\Settings\Base;
use BagistoPlus\Visual\Settings\Video;

it('extends Base', function () {
    expect(get_parent_class(Video::class))->toBe(Base::class);
});

it('is upload only by default', function () {
    expect(Video::make('hero_video', 'Hero video')->toArray())->toMatchArray([
        'id' => 'hero_video',
        'type' => 'video',
        'label' => 'Hero video',
        'acceptsExternal' => false,
        'externalSources' => [],
    ]);
});

it('accepts youtube and vimeo external sources by default when enabled', function () {
    expect(Video::make('hero_video', 'Hero video')->acceptsExternal()->toArray())
        ->toMatchArray([
            'acceptsExternal' => true,
            'externalSources' => [
                ['host' => 'youtube', 'label' => 'YouTube', 'kind' => 'embed'],
                ['host' => 'vimeo', 'label' => 'Vimeo', 'kind' => 'embed'],
            ],
        ]);
});

it('normalizes custom external video sources', function () {
    $schema = Video::make('hero_video', 'Hero video')
        ->acceptsExternal([
            [
                'host' => 'cdn',
                'pattern' => '#^https://cdn\.example\.com/.+\.(mp4|webm|ogg)(\?.*)?$#i',
            ],
        ])
        ->toArray();

    expect($schema['externalSources'][0])
        ->toMatchArray([
            'host' => 'cdn',
            'label' => 'CDN',
            'kind' => 'video',
            'pattern' => '#^https://cdn\.example\.com/.+\.(mp4|webm|ogg)(\?.*)?$#i',
            'jsPattern' => '^https://cdn\.example\.com/.+\.(mp4|webm|ogg)(\?.*)?$',
            'jsFlags' => 'i',
        ]);
});

it('throws for invalid external source definitions', function ($sources) {
    Video::make('hero_video', 'Hero video')->acceptsExternal($sources);
})->with([
    [[]],
    [['wistia']],
    [[['host' => 'bad-host', 'pattern' => '#\.mp4$#']]],
    [[['host' => 'cdn', 'pattern' => '[invalid']]],
    [[['host' => 'cdn', 'pattern' => '#(?<=video)\.mp4$#']]],
])->throws(InvalidArgumentException::class);
