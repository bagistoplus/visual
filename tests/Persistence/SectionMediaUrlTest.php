<?php

use BagistoPlus\Visual\Persistence\SectionStore;

/**
 * @param  object|null  $storage  What the installed Bagisto resolves a path with, if anything.
 */
function mediaUrlOf(string $path, ?object $storage = null): ?string
{
    $store = new class($storage) extends SectionStore
    {
        public function __construct(protected ?object $storage) {}

        public function url(string $path): ?string
        {
            return $this->mediaUrl($path);
        }

        protected function themeStorage(): ?object
        {
            return $this->storage;
        }
    };

    return $store->url($path);
}

function themeStorage(): object
{
    return new class
    {
        public function embedUrl(string $path): string
        {
            return '/resolved/'.$path;
        }
    };
}

it('leaves a release that resolves its own paths to do so', function () {
    expect(mediaUrlOf('themes/default/sections/7/a.webp', themeStorage()))
        ->toBe('/resolved/themes/default/sections/7/a.webp');
});

it('serves a path an earlier release rooted in the public directory', function () {
    expect(mediaUrlOf('storage/themes/default/sections/7/a.webp'))
        ->toBe(url('storage/themes/default/sections/7/a.webp'));
});

it('serves a path on the disk through the disk', function () {
    expect(mediaUrlOf('themes/default/sections/7/a.webp'))
        ->toBe(Storage::url('themes/default/sections/7/a.webp'));
});

it('leaves a url a remote disk answered alone', function () {
    expect(mediaUrlOf('https://cdn.example.com/a.webp'))->toBe('https://cdn.example.com/a.webp');
});

it('answers nothing for a path that is not one', function () {
    expect(mediaUrlOf(''))->toBeNull();
});
