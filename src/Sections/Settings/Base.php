<?php

namespace BagistoPlus\Visual\Sections\Settings;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

/**
 * @phpstan-consistent-constructor
 *
 * @method $this id(string $id)
 * @method $this label(string $label)
 * @method $this type(string $type)
 * @method $this default(mixed $value)
 * @method $this info(string $info)
 */
abstract class Base implements Arrayable, JsonSerializable
{
    public string $id;

    public string $label;

    public string $type;

    public mixed $default;

    public string $info;

    public static string $component = 'base-setting';

    public function __construct($id, $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public static function make(string $id, string $label = '')
    {
        $instance = (new static($id, $label ?: Str::title(str_replace('_', ' ', $id))))
            ->default(null)
            ->info('');

        $instance->type(static::$type ?? Str::snake((new \ReflectionClass(static::class))->getShortName()));

        return $instance;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return null;
    }

    public function __call($name, $arguments)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $arguments[0];

            return $this;
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'default' => $this->default,
            'info' => $this->info,
            'component' => static::$component,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
