<?php

namespace BagistoPlus\Visual\Settings\Support;

class SpacingValue
{
    public readonly int $top;
    public readonly int $right;
    public readonly int $bottom;
    public readonly int $left;

    public function __construct(array $data)
    {
        $this->top = $data['top'] ?? 0;
        $this->right = $data['right'] ?? 0;
        $this->bottom = $data['bottom'] ?? 0;
        $this->left = $data['left'] ?? 0;
    }
}
