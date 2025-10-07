<?php

namespace BagistoPlus\Visual\Concerns;

use Craftile\Core\Concerns\IsBlock;

trait HasBlockBehavior
{
    use IsBlock;

    protected static string $view = '';

    protected static array $settings = [];

    /**
     * Get block settings from static property.
     */
    public static function settings(): array
    {
        return static::$settings;
    }

    /**
     * Get block properties from.
     */
    public static function properties(): array
    {
        return static::settings();
    }

    protected function getViewData(): array
    {
        return [];
    }

    public function share(): array
    {
        return [];
    }

    public function render(): mixed
    {
        if (empty(static::$view)) {
            throw new \RuntimeException('View not specified for block '.static::class);
        }

        return view(static::$view, $this->getViewData());
    }
}
