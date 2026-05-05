<?php

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Settings\Support\ImageTransformer;
use BagistoPlus\Visual\Settings\Support\ImageValue;

beforeEach(function () {
    config()->set('bagisto_visual.images_storage', 'public');
});

it('implements SettingTransformerInterface', function () {
    expect(ImageTransformer::class)
        ->toImplement(SettingTransformerInterface::class);
});

it('returns null for null value', function () {
    $transformer = new ImageTransformer;

    expect($transformer->transform(null))->toBeNull();
});

it('transforms legacy string paths', function () {
    $transformer = new ImageTransformer;
    $path = 'bagisto-visual/images/4865726f_123.jpg';

    $image = $transformer->transform($path);

    expect($image)
        ->toBeInstanceOf(ImageValue::class)
        ->and($image->name)->toBe('Hero')
        ->and($image->path)->toBe($path)
        ->and($image->alt)->toBe('')
        ->and($image->focalPoint)->toBe(['x' => 50, 'y' => 50])
        ->and($image->objectPosition())->toBe('50% 50%');
});

it('transforms url string values', function () {
    $transformer = new ImageTransformer;
    $url = 'https://example.com/image.jpg';

    $image = $transformer->transform($url);

    expect($image)
        ->toBeInstanceOf(ImageValue::class)
        ->and($image->name)->toBe('')
        ->and($image->path)->toBe($url)
        ->and($image->url)->toBe($url)
        ->and((string) $image)->toBe($url);
});

it('transforms structured image values with metadata', function () {
    $transformer = new ImageTransformer;
    $path = 'bagisto-visual/images/4865726f_123.jpg';

    $image = $transformer->transform([
        'path' => $path,
        'alt' => 'A model wearing a blue jacket',
        'focalPoint' => ['x' => 32, 'y' => 68],
    ]);

    expect($image)
        ->toBeInstanceOf(ImageValue::class)
        ->and($image->path)->toBe($path)
        ->and($image->alt)->toBe('A model wearing a blue jacket')
        ->and($image->focalPoint)->toBe(['x' => 32, 'y' => 68])
        ->and($image->objectPosition())->toBe('32% 68%');
});

it('defaults missing structured metadata', function () {
    $transformer = new ImageTransformer;

    $image = $transformer->transform([
        'path' => 'bagisto-visual/images/4865726f_123.jpg',
    ]);

    expect($image)
        ->toBeInstanceOf(ImageValue::class)
        ->and($image->alt)->toBe('')
        ->and($image->focalPoint)->toBe(['x' => 50, 'y' => 50])
        ->and($image->objectPosition())->toBe('50% 50%');
});
