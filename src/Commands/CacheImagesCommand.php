<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Support\Images\ImageVariants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\info;

class CacheImagesCommand extends Command
{
    protected $signature = 'visual:images:cache';

    protected $description = 'Cache the size and copies of every image uploaded through the Bagisto Visual editor';

    public function handle(ImageVariants $variants): int
    {
        $disk = Visual::imagesDisk();
        $files = Storage::disk($disk)->files(Visual::imagesDirectory());

        foreach ($files as $path) {
            $variants->forget($disk, $path);
            $variants->describe($disk, $path);
        }

        info('Cached '.count($files).' image(s).');

        return self::SUCCESS;
    }
}
