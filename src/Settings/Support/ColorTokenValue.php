<?php

namespace BagistoPlus\Visual\Settings\Support;

class ColorTokenValue
{
    public const EMPTY_VALUE = '__none__';

    public const TOKENS = [
        'default',
        'primary',
        'secondary',
        'accent',
        'neutral',
        'success',
        'warning',
        'danger',
        'info',
    ];

    public const CSS_VAR_ALIASES = [
        'default' => 'on-background',
    ];

    public function __construct(public readonly ?string $token = null) {}

    public static function empty(): self
    {
        return new self(null);
    }

    public function isEmpty(): bool
    {
        return $this->token === null;
    }

    public function isToken(): bool
    {
        return $this->token !== null;
    }

    public function token(): ?string
    {
        return $this->token;
    }

    public function cssVar(): ?string
    {
        if ($this->token === null) {
            return null;
        }

        $name = self::CSS_VAR_ALIASES[$this->token] ?? $this->token;

        return "var(--color-{$name})";
    }

    public function __toString(): string
    {
        return $this->token ?? '';
    }
}
