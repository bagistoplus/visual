<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Illuminate\Support\Str;

trait SectionTrait
{
    protected static string $slug = '';

    protected static string $name = '';

    protected static string $wrapper = 'section';

    protected static array $settings = [];

    protected static array $blocks = [];

    protected static int $maxBlocks = 16;

    protected static string $description = '';

    protected static string $previewImageUrl = '';

    protected static string $previewDescription = '';

    protected static array $default = [];

    protected static array $enabledOn = ['*'];

    protected static array $disabledOn = [];

    protected static string $view = '';

    protected function getViewData(): array
    {
        return [];
    }

    public function render()
    {
        return view(static::$view, $this->getViewData());
    }

    protected static function className(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }

    public static function slug(): string
    {
        if (! empty(static::$slug)) {
            return static::$slug;
        }

        return Str::kebab(self::className());
    }

    public static function name(): string
    {
        if (! empty(static::$name)) {
            return static::$name;
        }

        return Str::of(self::slug())->headline();
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
        return static::$settings;
    }

    public static function blocks(): array
    {
        return static::$blocks;
    }

    public static function maxBlocks(): int
    {
        return static::$maxBlocks;
    }

    public static function description(): string
    {
        return static::$description;
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
