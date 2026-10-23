<?php

use BagistoPlus\Visual\Settings\Support\ImageTransformer;
use BagistoPlus\Visual\Support\Images\ImageVariants;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

function imageVariantsJpeg(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 40));

    ob_start();
    imagejpeg($image, null, 90);

    return (string) ob_get_clean();
}

function imageVariantsUpload(string $name = 'Hero', int $width = 800, int $height = 600, string $extension = 'jpg'): string
{
    $path = 'bagisto-visual/images/'.bin2hex($name).'_6538a1f2.'.$extension;
    Storage::disk('images')->put($path, imageVariantsJpeg($width, $height));

    return $path;
}

beforeEach(function () {
    Storage::fake('images');
    config()->set('bagisto_visual.images.storage', 'images');
    config()->set('bagisto_visual.images.directory', 'bagisto-visual/images');
    config()->set('bagisto_visual.images.variants.widths', [320, 640, 1024]);
});

it('makes a webp copy at each width below the original, keeping its shape', function () {
    $path = imageVariantsUpload();

    $widths = app(ImageVariants::class)->generate('images', $path);

    expect($widths)->toBe([320, 640]);

    $copy = Storage::disk('images')->get('bagisto-visual/images/variants/320/'.pathinfo($path, PATHINFO_FILENAME).'.webp');
    $size = getimagesizefromstring($copy);

    expect($size['mime'])->toBe('image/webp')
        ->and([$size[0], $size[1]])->toBe([320, 240]);

    Storage::disk('images')->assertMissing('bagisto-visual/images/variants/1024/'.pathinfo($path, PATHINFO_FILENAME).'.webp');
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('leaves svg and gif images as they are', function (string $extension) {
    $path = 'bagisto-visual/images/'.bin2hex('Logo').'_1.'.$extension;
    Storage::disk('images')->put($path, 'not resized');

    expect(app(ImageVariants::class)->generate('images', $path))->toBe([]);
})->with(['svg', 'gif']);

it('describes an image from the original and the copies on disk', function () {
    $path = imageVariantsUpload();
    $variants = app(ImageVariants::class);

    expect($variants->describe('images', $path))->toBe(['width' => 800, 'height' => 600, 'widths' => []]);

    $variants->generate('images', $path);

    expect($variants->describe('images', $path))->toBe(['width' => 800, 'height' => 600, 'widths' => [320, 640]]);
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('lists only the copies that exist', function () {
    $path = imageVariantsUpload();
    $variants = app(ImageVariants::class);
    $variants->generate('images', $path);

    Storage::disk('images')->delete(ImageVariants::variantPath($path, 640));
    $variants->forget('images', $path);

    expect($variants->describe('images', $path)['widths'])->toBe([320]);
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('serves a description from the cache until the copies change', function () {
    $path = imageVariantsUpload();
    $variants = app(ImageVariants::class);
    $variants->describe('images', $path);

    Storage::disk('images')->delete($path);

    expect($variants->describe('images', $path)['width'])->toBe(800);
});

it('has nothing to describe for an svg or a missing file', function () {
    $variants = app(ImageVariants::class);

    expect($variants->describe('images', 'bagisto-visual/images/logo.svg'))->toBeNull()
        ->and($variants->describe('images', 'bagisto-visual/images/missing.jpg'))->toBeNull();
});

it('gives an uploaded image its size and copies', function () {
    $path = imageVariantsUpload();
    app(ImageVariants::class)->generate('images', $path);

    $image = (new ImageTransformer)($path);
    $copy = fn (int $width) => Storage::disk('images')->url(ImageVariants::variantPath($path, $width));

    expect($image->width)->toBe(800)
        ->and($image->height)->toBe(600)
        ->and($image->variants)->toBe([320 => $copy(320), 640 => $copy(640)])
        ->and($image->srcset())->toBe("{$copy(320)} 320w, {$copy(640)} 640w, {$image->url} 800w");
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('skips the lookup when only listing images', function () {
    $path = imageVariantsUpload();

    $image = (new ImageTransformer)($path, describe: false);

    expect($image->width)->toBeNull()
        ->and($image->srcset())->toBeNull();
});

it('has no srcset for an image set by url', function () {
    $image = (new ImageTransformer)('https://cdn.example.com/hero.jpg');

    expect($image->srcset())->toBeNull()
        ->and($image->width)->toBeNull();
});

it('renders a lazy responsive image', function () {
    $path = imageVariantsUpload();
    app(ImageVariants::class)->generate('images', $path);
    $image = (new ImageTransformer)(['path' => $path, 'alt' => 'A hero']);

    $html = Blade::render('<x-visual::image :image="$image" class="w-full" />', ['image' => $image]);

    expect($html)
        ->toContain('src="'.$image->url.'"')
        ->toContain('srcset="'.e($image->srcset()).'"')
        ->toContain('sizes="auto, 100vw"')
        ->toContain('width="800"')
        ->toContain('height="600"')
        ->toContain('alt="A hero"')
        ->toContain('loading="lazy"')
        ->toContain('decoding="async"')
        ->toContain('class="w-full"')
        ->not->toContain('fetchpriority');
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('renders a priority image eagerly with the sizes it is given', function () {
    $path = imageVariantsUpload();
    app(ImageVariants::class)->generate('images', $path);
    $image = (new ImageTransformer)($path);

    $html = Blade::render('<x-visual::image :image="$image" priority sizes="50vw" />', ['image' => $image]);

    expect($html)
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="50vw"')
        ->not->toContain('loading="lazy"');
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('renders a plain image without copies and nothing without an image', function () {
    $image = (new ImageTransformer)('https://cdn.example.com/hero.jpg');

    expect(Blade::render('<x-visual::image :image="$image" />', ['image' => $image]))
        ->toContain('src="https://cdn.example.com/hero.jpg"')
        ->not->toContain('srcset')
        ->not->toContain('width=')
        ->and(trim(Blade::render('<x-visual::image :image="null" />')))->toBe('');
});

it('makes copies for images that have none, and skips the others', function () {
    $first = imageVariantsUpload('First');
    $second = imageVariantsUpload('Second');
    app(ImageVariants::class)->generate('images', $first);

    $this->artisan('visual:images:variants')->assertSuccessful();

    Storage::disk('images')->assertExists(ImageVariants::variantPath($second, 640));
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');

it('caches the description of every image', function () {
    $path = imageVariantsUpload();

    $this->artisan('visual:images:cache')->assertSuccessful();

    Storage::disk('images')->delete($path);

    expect(app(ImageVariants::class)->describe('images', $path)['width'])->toBe(800);
});
