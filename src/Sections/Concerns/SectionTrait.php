<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use Illuminate\Support\Str;

trait SectionTrait
{
    protected static string $slug = '';

    protected static string $schema = '';

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
        return Str::of(self::slug())->replace('-', ' ')->title();
    }

    public static function getSchemaPath(): string
    {
        return static::$schema;
    }

    public static function getSchema(): array
    {
        $schemaPath = self::getSchemaPath();

        if (empty($schemaPath)) {
            return [];
        }

        return json_decode(file_get_contents($schemaPath), true);
    }
}
