<?php

use BagistoPlus\Visual\Facades\Visual;

it('reads grouped image media settings', function () {
    config()->set('bagisto_visual.images.storage', 'images');
    config()->set('bagisto_visual.images.directory', 'visual-images');

    expect(Visual::imagesDisk())->toBe('images')
        ->and(Visual::imagesDirectory())->toBe('visual-images');
});

it('falls back to flat image media settings', function () {
    config()->set('bagisto_visual.images', []);
    config()->set('bagisto_visual.images_storage', 'legacy-images');
    config()->set('bagisto_visual.images_directory', 'legacy-images-directory');

    expect(Visual::imagesDisk())->toBe('legacy-images')
        ->and(Visual::imagesDirectory())->toBe('legacy-images-directory');
});

it('reads grouped video media settings', function () {
    config()->set('bagisto_visual.videos.storage', 'videos');
    config()->set('bagisto_visual.videos.directory', 'visual-videos');
    config()->set('bagisto_visual.videos.max_upload_size', 1024);

    expect(Visual::videosDisk())->toBe('videos')
        ->and(Visual::videosDirectory())->toBe('visual-videos')
        ->and(Visual::videosMaxUploadSize())->toBe(1024);
});
