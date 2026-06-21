<?php

namespace BagistoPlus\Visual\Concerns;

use Craftile\Core\Concerns\IsBlock;

trait HasBlockBehavior
{
    use IsBlock;

    protected static string $view = '';

    protected static array $settings = [];

    protected static array $enabledOn = [];

    protected static array $disabledOn = [];

    protected static array $meta = [];

    public static function enabledOn(): array
    {
        return static::$enabledOn;
    }

    public static function disabledOn(): array
    {
        return static::$disabledOn;
    }

    public static function meta(): array
    {
        return static::$meta;
    }

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

    protected static function t(string $key): string
    {
        return \BagistoPlus\Visual\t($key);
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

        return view()->make(static::$view, $this->getViewData());
    }
}
