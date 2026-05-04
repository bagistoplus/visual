<?php

namespace BagistoPlus\Visual\Settings\Support;

class SpacingValue
{
    public readonly int|string $top;

    public readonly int|string $right;

    public readonly int|string $bottom;

    public readonly int|string $left;

    public function __construct(array $data)
    {
        $this->top = self::normalize($data['top'] ?? 0);
        $this->right = self::normalize($data['right'] ?? 0);
        $this->bottom = self::normalize($data['bottom'] ?? 0);
        $this->left = self::normalize($data['left'] ?? 0);
    }

    private static function normalize(mixed $value): int|string
    {
        if ($value === 'auto') {
            return 'auto';
        }

        return (int) $value;
    }
}
