<?php

namespace BagistoPlus\Visual\Settings;

class ColorSchemeGroup extends Base
{
    protected static string $type = 'color-scheme-group';

    public const REQUIRED_TOKENS = [
        'background',
        'on-background',
        'primary',
        'on-primary',
        'secondary',
        'on-secondary',
        'accent',
        'on-accent',
        'neutral',
        'on-neutral',
        'surface',
        'on-surface',
        'surface-alt',
        'on-surface-alt',
        'success',
        'on-success',
        'warning',
        'on-warning',
        'danger',
        'on-danger',
        'info',
        'on-info',
    ];

    protected array $schemes = [];

    public function schemes(array $schemes): static
    {
        foreach ($schemes as $name => $tokens) {
            $missing = array_diff(self::REQUIRED_TOKENS, array_keys($tokens));

            if (! empty($missing)) {
                throw new \InvalidArgumentException("Color scheme '{$name}' is missing tokens: " . implode(', ', $missing));
            }
        }

        $this->meta['schemes'] = $schemes;
        $this->default($schemes);

        return $this;
    }
}
