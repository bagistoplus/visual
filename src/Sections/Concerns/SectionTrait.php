<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Illuminate\Support\Str;

trait SectionTrait
{
    protected static string $wrapper = 'section';

    protected static array $settings = [];

    protected static string $previewImageUrl = '';

    protected static string $previewDescription = '';

    protected static array $default = [];

    protected static array $enabledOn = ['*'];

    protected static array $disabledOn = [];

    protected static string $view = '';

    protected static array $blocks = [];

    protected static int $maxBlocks = 16;

    public static function blocks(): array
    {
        return static::$blocks;
    }

    public static function maxBlocks(): int
    {
        return static::$maxBlocks;
    }

    protected function getViewData(): array
    {
        return [];
    }

    public function render(): mixed
    {
        return view(static::$view, $this->getViewData());
    }

    public static function wrapper(): string
    {
        if (! empty(static::$wrapper)) {
            return static::$wrapper;
        }

        return 'div';
    }

    public static function settings(): array
    {
        return static::properties();
    }

    public static function previewImageUrl(): string
    {
        return static::$previewImageUrl ? url(static::$previewImageUrl) : '';
    }

    public static function previewDescription(): string
    {
        return static::$previewDescription ? static::$previewDescription : static::description();
    }

    public static function default(): array
    {
        return static::$default;
    }

    public static function enabledOn(): array
    {
        return static::$enabledOn;
    }

    public static function disabledOn(): array
    {
        return static::$disabledOn;
    }
}
