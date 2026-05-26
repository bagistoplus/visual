<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('videos');
    config()->set('bagisto_visual.videos.storage', 'videos');
    config()->set('bagisto_visual.videos.directory', 'bagisto-visual/videos');
    config()->set('bagisto_visual.videos.max_upload_size', 51200);
});

it('uploads web playable videos with mime type metadata', function () {
    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $file = UploadedFile::fake()->create('Hero Demo.mp4', 128, 'video/mp4');
    $request = Request::create('/visual/editor/api/upload-videos', 'POST', [], [], [
        'video' => [$file],
    ]);

    $response = $controller->uploadVideos($request);
    $video = $response->first();

    expect($video)
        ->toMatchArray([
            'name' => 'Hero Demo',
            'mime_type' => 'video/mp4',
        ])
        ->and($video['path'])->toStartWith('bagisto-visual/videos/'.bin2hex('Hero Demo').'_')
        ->and($video['path'])->toEndWith('.mp4');

    Storage::disk('videos')->assertExists($video['path']);
});

it('accepts mp4 files detected as m4v mime type', function () {
    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $file = UploadedFile::fake()->create('Camp Video.mp4', 128, 'video/x-m4v');
    $request = Request::create('/visual/editor/api/upload-videos', 'POST', [], [], [
        'video' => [$file],
    ]);

    $response = $controller->uploadVideos($request);
    $video = $response->first();

    expect($video)
        ->toMatchArray([
            'name' => 'Camp Video',
            'mime_type' => 'video/x-m4v',
        ])
        ->and($video['path'])->toEndWith('.mp4');

    Storage::disk('videos')->assertExists($video['path']);
});

it('rejects unsupported video upload mime types', function () {
    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $file = UploadedFile::fake()->create('demo.mov', 128, 'video/quicktime');
    $request = Request::create('/visual/editor/api/upload-videos', 'POST', [], [], [
        'video' => [$file],
    ]);

    $controller->uploadVideos($request);
})->throws(ValidationException::class);

it('lists uploaded videos with mime type metadata', function () {
    Storage::disk('videos')->put('bagisto-visual/videos/'.bin2hex('Hero Demo').'_123.webm', 'fake-video');

    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $videos = $controller->listVideos();

    expect($videos->first())
        ->toMatchArray([
            'name' => 'Hero Demo',
            'path' => 'bagisto-visual/videos/'.bin2hex('Hero Demo').'_123.webm',
            'mime_type' => 'video/webm',
        ]);
});
