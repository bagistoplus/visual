<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Support\Images\ImageResizer;
use BagistoPlus\Visual\Support\Images\ImageVariants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class GenerateImageVariantsCommand extends Command
{
    protected $signature = 'visual:images:variants
                            {--force : Remake copies that already exist}';

    protected $description = 'Make the WebP copies of images uploaded through the Bagisto Visual editor';

    public function handle(ImageVariants $variants, ImageResizer $resizer): int
    {
        if (! $resizer->available()) {
            error('No image library that can write WebP is installed, so no copies can be made.');

            return self::FAILURE;
        }

        $disk = Visual::imagesDisk();
        $files = Storage::disk($disk)->files(Visual::imagesDirectory());

        $made = $skipped = $failed = 0;

        foreach ($files as $path) {
            if (! $this->option('force') && ($variants->describe($disk, $path)['widths'] ?? []) !== []) {
                $skipped++;

                continue;
            }

            try {
                // SVG, GIF and images narrower than the smallest width get no copies.
                if ($variants->generate($disk, $path) === []) {
                    $skipped++;
                } else {
                    $made++;
                }
            } catch (Throwable $e) {
                $failed++;
                warning("{$path}: {$e->getMessage()}");
            }
        }

        info("Copies made for {$made} image(s), {$skipped} skipped, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
