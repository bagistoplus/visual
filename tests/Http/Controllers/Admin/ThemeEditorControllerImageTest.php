<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
use BagistoPlus\Visual\Support\Images\ImageVariants;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('images');
    config()->set('bagisto_visual.images.storage', 'images');
    config()->set('bagisto_visual.images.directory', 'bagisto-visual/images');
    config()->set('bagisto_visual.images.variants.widths', [320, 640, 1024]);
});

it('makes the copies of an uploaded image', function () {
    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $request = Request::create('/visual/editor/api/upload-images', 'POST', [], [], [
        'image' => [UploadedFile::fake()->image('Hero.jpg', 800, 600)],
    ]);

    $image = $controller->uploadImages($request)->first();

    Storage::disk('images')->assertExists($image['path']);
    Storage::disk('images')->assertExists(ImageVariants::variantPath($image['path'], 320));
    Storage::disk('images')->assertExists(ImageVariants::variantPath($image['path'], 640));
})->skip(! function_exists('imagewebp'), 'GD cannot write WebP here');
