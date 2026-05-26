<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Settings\Support\ImageValue;
use BagistoPlus\Visual\Settings\Support\VideoTransformer;
use BagistoPlus\Visual\Settings\Support\VideoValue;

beforeEach(function () {
    config()->set('bagisto_visual.videos.storage', 'public');
});

it('implements SettingTransformerInterface', function () {
    expect(VideoTransformer::class)->toImplement(SettingTransformerInterface::class);
});

it('returns null for null and unknown url values', function () {
    $transformer = new VideoTransformer;

    expect($transformer->transform(null))->toBeNull()
        ->and($transformer->transform('https://example.com/not-a-video'))->toBeNull();
});

it('transforms uploaded structured values and path string defaults', function () {
    $transformer = new VideoTransformer;
    $path = 'bagisto-visual/videos/4865726f_123.mp4';

    $video = $transformer->transform(['mode' => 'upload', 'path' => $path]);
    $pathDefault = $transformer->transform($path);

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('video')
        ->and($video->host)->toBeNull()
        ->and($video->path)->toBe($path)
        ->and($video->name)->toBe('Hero')
        ->and($video->sources[0]['mime_type'])->toBe('video/mp4')
        ->and((string) $video)->toContain($path)
        ->and($pathDefault?->path)->toBe($path);
});

it('uses retained uploaded source when upload mode is active', function () {
    $video = (new VideoTransformer)->transform([
        'mode' => 'upload',
        'upload' => ['path' => 'bagisto-visual/videos/4865726f_123.mp4'],
        'external' => ['url' => 'https://youtu.be/abc123XYZ', 'host' => 'youtube'],
    ]);

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('video')
        ->and($video->path)->toBe('bagisto-visual/videos/4865726f_123.mp4')
        ->and($video->host)->toBeNull();
});

it('uses retained external source when external mode is active', function () {
    $video = (new VideoTransformer)->transform([
        'mode' => 'external',
        'upload' => ['path' => 'bagisto-visual/videos/4865726f_123.mp4'],
        'external' => ['url' => 'https://youtu.be/abc123XYZ', 'host' => 'youtube'],
    ]);

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('external_video')
        ->and($video->host)->toBe('youtube')
        ->and($video->url)->toBe('https://www.youtube.com/embed/abc123XYZ');
});

it('does not fall back to inactive retained sources', function () {
    $transformer = new VideoTransformer;

    expect($transformer->transform([
        'mode' => 'upload',
        'external' => ['url' => 'https://youtu.be/abc123XYZ', 'host' => 'youtube'],
    ]))->toBeNull()
        ->and($transformer->transform([
            'mode' => 'external',
            'upload' => ['path' => 'bagisto-visual/videos/4865726f_123.mp4'],
        ]))->toBeNull();
});

it('normalizes youtube urls and creates a preview image', function () {
    $video = (new VideoTransformer)->transform('https://youtu.be/abc123XYZ?t=1m30s&si=noise&end=120');

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('external_video')
        ->and($video->host)->toBe('youtube')
        ->and($video->url)->toBe('https://www.youtube.com/embed/abc123XYZ?end=120&start=90')
        ->and($video->original_url)->toBe('https://youtu.be/abc123XYZ?t=1m30s&si=noise&end=120')
        ->and($video->preview_image)->toBeInstanceOf(ImageValue::class)
        ->and($video->preview_image?->url)->toBe('https://img.youtube.com/vi/abc123XYZ/hqdefault.jpg');
});

it('supports youtube watch embed and shorts urls', function ($url) {
    expect((new VideoTransformer)->transform($url)?->url)
        ->toBe('https://www.youtube.com/embed/abc123XYZ');
})->with([
    'https://www.youtube.com/watch?v=abc123XYZ',
    'https://www.youtube.com/embed/abc123XYZ',
    'https://www.youtube.com/shorts/abc123XYZ',
]);

it('rejects urls with spoofed youtube hosts', function ($url) {
    expect((new VideoTransformer)->transform($url))->toBeNull();
})->with([
    'https://youtube.com.evil.test/watch?v=abc123XYZ',
    'https://evil-youtube.com/watch?v=abc123XYZ',
    'https://evil.test/youtube.com/watch?v=abc123XYZ',
]);

it('normalizes vimeo urls and preserves unlisted hashes and time fragments', function () {
    $video = (new VideoTransformer)->transform('https://vimeo.com/123456789/abcdef#t=1m30s');

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('external_video')
        ->and($video->host)->toBe('vimeo')
        ->and($video->url)->toBe('https://player.vimeo.com/video/123456789?h=abcdef#t=1m30s')
        ->and($video->preview_image)->toBeNull();
});

it('supports vimeo player urls with h query', function () {
    $video = (new VideoTransformer)->transform('https://player.vimeo.com/video/123456789?h=abcdef&controls=1');

    expect($video?->url)->toBe('https://player.vimeo.com/video/123456789?controls=1&h=abcdef');
});

it('rejects urls with spoofed vimeo hosts', function ($url) {
    expect((new VideoTransformer)->transform($url))->toBeNull();
})->with([
    'https://vimeo.com.evil.test/123456789',
    'https://evil-vimeo.com/123456789',
]);

it('classifies custom external video urls using schema regex', function () {
    $schema = [
        'externalSources' => [
            [
                'host' => 'cdn',
                'kind' => 'video',
                'pattern' => '#^https://cdn\.example\.com/videos/.+$#i',
            ],
        ],
    ];

    $video = (new VideoTransformer)->transform([
        'mode' => 'external',
        'url' => 'https://cdn.example.com/videos/demo',
        'host' => 'cdn',
    ], $schema);

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('video')
        ->and($video->host)->toBe('cdn')
        ->and($video->url)->toBe('https://cdn.example.com/videos/demo');
});

it('transforms direct web playable urls without schema', function () {
    $video = (new VideoTransformer)->transform('https://example.com/demo.webm?token=123');

    expect($video)
        ->toBeInstanceOf(VideoValue::class)
        ->and($video->media_type)->toBe('video')
        ->and($video->sources[0]['mime_type'])->toBe('video/webm');
});

it('renders native video html', function () {
    $video = (new VideoTransformer)->transform('https://example.com/demo.mp4');

    expect((string) $video?->render(['class' => 'w-full']))
        ->toContain('<video')
        ->toContain('class="w-full"')
        ->toContain('controls')
        ->toContain('playsinline')
        ->toContain('<source')
        ->toContain('type="video/mp4"');
});

it('renders external iframe html and maps player attributes', function () {
    $video = (new VideoTransformer)->transform('https://youtu.be/abc123XYZ?controls=1');
    $html = (string) $video?->render([
        'class' => 'aspect-video',
        'controls' => false,
        'autoplay' => true,
        'muted' => true,
        'loop' => true,
    ]);

    expect($html)
        ->toContain('<iframe')
        ->toContain('class="aspect-video"')
        ->toContain('title="YouTube video player"')
        ->toContain('controls=0')
        ->toContain('autoplay=1')
        ->toContain('mute=1')
        ->toContain('loop=1')
        ->toContain('playlist=abc123XYZ')
        ->and($video?->url)->toBe('https://www.youtube.com/embed/abc123XYZ?controls=1');
});
