<?php

namespace BagistoPlus\Visual\Settings\Support;

class ImageValue
{
    /**
     * @param  array<int, string>  $variants  URLs of the WebP copies, keyed by width
     */
    public function __construct(
        public string $name,
        public string $path,
        public string $url,
        public string $alt = '',
        public array $focalPoint = ['x' => 50, 'y' => 50],
        public ?int $width = null,
        public ?int $height = null,
        public array $variants = [],
    ) {}

    public function __toString()
    {
        return $this->url;
    }

    /**
     * Each copy at its width, then the original at its own, or null when the
     * image has no copies (not an upload, or not resized yet).
     */
    public function srcset(): ?string
    {
        if ($this->variants === []) {
            return null;
        }

        $candidates = collect($this->variants)
            ->sortKeys()
            ->map(fn (string $url, int $width) => "{$url} {$width}w");

        if ($this->width !== null) {
            $candidates->push("{$this->url} {$this->width}w");
        }

        return $candidates->implode(', ');
    }

    public function objectPosition(): string
    {
        $x = $this->normalizeFocalPointValue($this->focalPoint['x'] ?? 50);
        $y = $this->normalizeFocalPointValue($this->focalPoint['y'] ?? 50);

        return "{$x}% {$y}%";
    }

    private function normalizeFocalPointValue(mixed $value): int|float
    {
        if (! is_numeric($value)) {
            return 50;
        }

        $value = max(0, min(100, (float) $value));

        return fmod($value, 1.0) === 0.0 ? (int) $value : $value;
    }
}
