<?php

namespace BagistoPlus\Visual\Settings\Support;

class RadiusValue
{
    public const SCALE = [
        'none' => '0',
        'xs' => '0.125rem',
        'sm' => '0.25rem',
        'md' => '0.375rem',
        'lg' => '0.5rem',
        'xl' => '0.75rem',
        'full' => 'calc(infinity * 1px)',
    ];

    public readonly string $value;

    public function __construct(public readonly string $key)
    {
        $this->value = self::SCALE[$key] ?? self::SCALE['md'];
    }

    public static function has(mixed $key): bool
    {
        return is_string($key) && array_key_exists($key, self::SCALE);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
