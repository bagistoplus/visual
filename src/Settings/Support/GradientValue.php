<?php

namespace BagistoPlus\Visual\Settings\Support;

class GradientValue
{
    public function __construct(
        public array $data
    ) {}

    public function toCss(): string
    {
        $type = $this->data['type'] ?? 'linear';
        $stops = $this->data['stops'] ?? [];

        if (empty($stops)) {
            return 'none';
        }

        if ($type === 'linear') {
            $angle = $this->data['angle'] ?? 90;
            $stopsStr = $this->buildStopsString($stops);

            return "linear-gradient({$angle}deg, {$stopsStr})";
        }

        if ($type === 'radial') {
            $stopsStr = $this->buildStopsString($stops);

            return "radial-gradient(circle, {$stopsStr})";
        }

        return 'none';
    }

    private function buildStopsString(array $stops): string
    {
        return collect($stops)
            ->map(function ($stop) {
                $color = $stop['color'] ?? '#000000ff';
                $position = $stop['position'] ?? 0;

                // Convert hexa to rgba
                $color = $this->hexaToRgba($color);

                return "{$color} {$position}%";
            })
            ->join(', ');
    }

    private function hexaToRgba(string $hexa): string
    {
        $hex = ltrim($hexa, '#');

        // Handle 3-character hex codes
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2].'ff';
        }

        // Handle 6-character hex codes (no alpha)
        if (strlen($hex) === 6) {
            $hex .= 'ff';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $a = strlen($hex) === 8 ? hexdec(substr($hex, 6, 2)) / 255 : 1;

        return "rgba({$r}, {$g}, {$b}, {$a})";
    }

    public function __toString(): string
    {
        return $this->toCss();
    }
}
