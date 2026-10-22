<?php

use BagistoPlus\Visual\Persistence\SectionStore;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function diskMediaStore(): SectionStore
{
    return new class extends SectionStore
    {
        public function __construct() {}

        protected function themeStorage(): ?object
        {
            return null;
        }
    };
}

beforeEach(function () {
    Storage::fake();

    Storage::put('themes/default/sections/7/older.webp', 'older');
    Storage::put('themes/default/sections/9/newer.webp', 'newer');
    Storage::put('themes/other/sections/1/elsewhere.webp', 'elsewhere');

    touch(Storage::path('themes/default/sections/7/older.webp'), time() - 60);
});

function resolvingMediaStore(): SectionStore
{
    return new class extends SectionStore
    {
        public function __construct() {}

        protected function themeStorage(): ?object
        {
            return new class
            {
                public function normalize(string $path): string
                {
                    return ltrim(str_replace('media/', '', $path), '/');
                }

                public function embedUrl(string $path): string
                {
                    return '/resolved/'.$path;
                }
            };
        }
    };
}

it('lists every file the theme sections hold, newest first', function () {
    expect(diskMediaStore()->media('default'))->toBe([
        [
            'path' => 'storage/themes/default/sections/9/newer.webp',
            'url' => Storage::url('themes/default/sections/9/newer.webp'),
            'section' => 9,
        ],
        [
            'path' => 'storage/themes/default/sections/7/older.webp',
            'url' => Storage::url('themes/default/sections/7/older.webp'),
            'section' => 7,
        ],
    ]);
});

it('leaves another theme files out of the library', function () {
    expect(collect(diskMediaStore()->media('default'))->pluck('path'))
        ->not->toContain('storage/themes/other/sections/1/elsewhere.webp');
});

it('keeps a copy among the media of the section that is to draw it', function () {
    $copy = diskMediaStore()->copyMedia(9, 'themes/default/sections/7/older.webp', 'default');
    $onDisk = Str::chopStart($copy['path'], 'storage/');

    expect($copy['path'])->toStartWith('storage/themes/default/sections/9/')
        ->and($copy['path'])->toEndWith('.webp')
        ->and(Storage::get($onDisk))->toBe('older')
        ->and(Storage::exists('themes/default/sections/7/older.webp'))->toBeTrue();
});

it('answers paths without the public prefix where the release resolves its own', function () {
    $store = resolvingMediaStore();

    expect(collect($store->media('default'))->pluck('path')->all())->toBe([
        'themes/default/sections/9/newer.webp',
        'themes/default/sections/7/older.webp',
    ])->and($store->copyMedia(9, 'themes/default/sections/7/older.webp', 'default')['path'])
        ->toStartWith('themes/default/sections/9/');
});

it('gives the copy a name of its own, so that it cannot overwrite what is there', function () {
    $store = diskMediaStore();

    $first = $store->copyMedia(9, 'themes/default/sections/7/older.webp', 'default');
    $second = $store->copyMedia(9, 'themes/default/sections/7/older.webp', 'default');

    expect($first['path'])->not->toBe($second['path']);
});

it('copies a path stored with the public prefix an earlier release wrote', function () {
    expect(diskMediaStore()->copyMedia(9, 'storage/themes/default/sections/7/older.webp', 'default'))
        ->not->toBeNull();
});

it('reads a stored path the way the installed release reads it', function () {
    expect(resolvingMediaStore()->copyMedia(9, '/media/themes/default/sections/7/older.webp', 'default')['url'])
        ->toStartWith('/resolved/themes/default/sections/9/');
});

it('refuses a path outside the theme own media', function (string $path) {
    expect(diskMediaStore()->copyMedia(9, $path, 'default'))->toBeNull();
})->with([
    'another theme' => 'themes/other/sections/1/elsewhere.webp',
    'a walk out of the directory' => 'themes/default/sections/../../../.env',
    'somewhere else entirely' => '.env',
    'a file that is not there' => 'themes/default/sections/7/gone.webp',
]);
