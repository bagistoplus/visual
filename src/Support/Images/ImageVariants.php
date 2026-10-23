<?php

namespace BagistoPlus\Visual\Support\Images;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * The WebP copies of images uploaded through the editor.
 *
 * Copies are made once, at upload or by `visual:images:variants`. What a
 * template needs about an image (its size and the copies that exist) is read
 * from the original and the disk, then cached until the copies change, so a
 * render costs a cache read. Nothing is recorded anywhere else.
 */
class ImageVariants
{
    /** Formats that are not resized: SVG has no pixels, and a GIF may be animated. */
    protected const KEPT_AS_IS = ['svg', 'gif'];

    /** Bytes read from the start of an original to find its size. */
    protected const HEAD_BYTES = 262144;

    /** @var array<string, array{width: int, height: int, widths: list<int>}|null> */
    protected array $described = [];

    public function __construct(protected ImageResizer $resizer) {}

    /**
     * Makes the copies of an image and returns the widths written.
     *
     * @return list<int>
     */
    public function generate(string $disk, string $path): array
    {
        $this->forget($disk, $path);

        if (! $this->resizable($path) || ! $this->resizer->available()) {
            return [];
        }

        $storage = Storage::disk($disk);
        $contents = $storage->get($path);

        if ($contents === null) {
            return [];
        }

        $result = $this->resizer->resize($contents, $this->widths(), $this->quality());

        foreach ($result['variants'] as $width => $bytes) {
            $storage->put(static::variantPath($path, $width), $bytes);
        }

        $this->forget($disk, $path);

        $widths = array_keys($result['variants']);
        sort($widths);

        return $widths;
    }

    /**
     * The size of an uploaded image and the widths of its copies, or null when
     * it cannot be read. Widths are only those whose copy is on disk.
     *
     * @return array{width: int, height: int, widths: list<int>}|null
     */
    public function describe(string $disk, string $path): ?array
    {
        $key = $this->cacheKey($disk, $path);

        if (array_key_exists($key, $this->described)) {
            return $this->described[$key];
        }

        $description = Cache::rememberForever($key, fn () => $this->read($disk, $path) ?? false);

        return $this->described[$key] = $description ?: null;
    }

    public function forget(string $disk, string $path): void
    {
        $key = $this->cacheKey($disk, $path);

        unset($this->described[$key]);
        Cache::forget($key);
    }

    /**
     * Where the copy of an image at a width is stored, next to the original:
     * `{directory}/variants/{width}/{file name}.webp`.
     */
    public static function variantPath(string $path, int $width): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $name = pathinfo($path, PATHINFO_FILENAME).'.webp';

        return ltrim(($directory === '.' ? '' : $directory).'/variants/'.$width.'/'.$name, '/');
    }

    /**
     * @return array{width: int, height: int, widths: list<int>}|null
     */
    protected function read(string $disk, string $path): ?array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            return null;
        }

        try {
            $storage = Storage::disk($disk);
            $size = $this->size($storage, $path);
        } catch (Throwable) {
            return null;
        }

        if ($size === null) {
            return null;
        }

        [$width, $height] = $size;

        $widths = $this->resizable($path)
            ? array_values(array_filter(
                $this->widths(),
                fn (int $candidate) => $candidate < $width && $storage->exists(static::variantPath($path, $candidate)),
            ))
            : [];

        sort($widths);

        return ['width' => $width, 'height' => $height, 'widths' => $widths];
    }

    /**
     * The size of an original as it displays: read from its first bytes, with
     * width and height swapped when its EXIF orientation turns it a quarter.
     *
     * @return array{0: int, 1: int}|null
     */
    protected function size(Filesystem $storage, string $path): ?array
    {
        $stream = $storage->readStream($path);

        if (! is_resource($stream)) {
            return null;
        }

        try {
            $head = (string) fread($stream, static::HEAD_BYTES);
        } finally {
            fclose($stream);
        }

        $info = @getimagesizefromstring($head);

        if ($info === false) {
            return null;
        }

        [$width, $height] = $info;

        if (in_array($this->orientation($head, $info[2]), [5, 6, 7, 8], true)) {
            [$width, $height] = [$height, $width];
        }

        return [(int) $width, (int) $height];
    }

    protected function orientation(string $head, ?int $type): ?int
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($head));

        return is_array($exif) && isset($exif['Orientation']) ? (int) $exif['Orientation'] : null;
    }

    protected function resizable(string $path): bool
    {
        return ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), static::KEPT_AS_IS, true);
    }

    /**
     * @return list<int>
     */
    protected function widths(): array
    {
        return array_values(array_map('intval', (array) config('bagisto_visual.images.variants.widths', [])));
    }

    protected function quality(): int
    {
        return (int) config('bagisto_visual.images.variants.quality', 80);
    }

    protected function cacheKey(string $disk, string $path): string
    {
        return 'bagisto_visual.image:'.sha1($disk.'|'.$path);
    }
}
