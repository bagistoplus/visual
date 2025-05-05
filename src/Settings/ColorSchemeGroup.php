<?php

namespace BagistoPlus\Visual\Settings;

class ColorSchemeGroup extends Base
{
    public static string $component = 'color-scheme-group-setting';

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
                throw new \InvalidArgumentException("Color scheme '{$name}' is missing tokens: ".implode(', ', $missing));
            }
        }

        $this->schemes = $schemes;
        $this->default($schemes);

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'schemes' => $this->schemes,
        ]);
    }
}
