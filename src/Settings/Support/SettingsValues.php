<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 */
final class SettingsValues implements Arrayable, IteratorAggregate, JsonSerializable
{
    /**
     * All of the settings values.
     *
     * @var array<TKey, TValue>
     */
    protected $values = [];

    /**
     * All of the settings schemas.
     *
     * @var array<TKey, TValue>
     */
    protected $schemas = [];

    /**
     * Resolved values cache.
     *
     * @var array<TKey, TValue>
     */
    protected array $resolved = [];

    /**
     * All registered transformers
     *
     * @var array<string, callable>
     */
    protected static array $transformers = [];

    /**
     * Register a transformer for a specific type.
     */
    public static function registerTransformer(string $type, callable $transformer): void
    {
        self::$transformers[$type] = $transformer;
    }

    /**
     * Create a new SettingsValues instance.
     *
     * @param  iterable<TKey, TValue>  $values
     * @param  iterable<TKey, TValue>  $schemas
     * @return void
     */
    public function __construct($values = [], $schemas = [])
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = $value;
        }

        foreach ($schemas as $key => $schema) {
            $this->schemas[$key] = $schema;
        }
    }

    /**
     * Magic getter for accessing values.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Get a value by key, with transformation applied.
     *
     * @param  TKey  $key
     * @return TValue|null
     */
    public function get($key)
    {
        // Use cache if the value has already been resolved
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        if (! array_key_exists($key, $this->values)) {
            return null;
        }

        $value = $this->values[$key];
        $schema = $this->schemas[$key] ?? null;

        $transformedValue = $this->transformValue($value, $schema['type']);

        $this->resolved[$key] = $transformedValue;

        return $transformedValue;
    }

    /**
     * Transform a value based on its type.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function transformValue($value, ?string $type)
    {
        // Use custom transformer if registered
        if (isset(self::$transformers[$type])) {
            $transformer = self::$transformers[$type];

            return $transformer($value);
        }

        return match ($type) {
            'color' => (new ColorTransformer)($value),
            'image' => (new ImageTransformer)($value),
            'category' => (new CategoryTransformer)($value),
            'product' => (new ProductTransformer)($value),
            'cms_page' => (new CmsPageTransformer)($value),
            'link' => (new LinkTransformer)($value),
            'font' => (new FontTransformer)($value),
            'icon' => (new IconTransformer)($value),
            'color_scheme' => (new ColorSchemeTransformer)($value),
            'color_scheme_group' => (new ColorSchemeGroupTransformer)($value),
            default => $value,
        };
    }

    /**
     * Check if a key exists in the values array.
     *
     * @param  TKey  $key
     */
    public function has(string|int $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Only include the given setting from the attribute array.
     *
     * @param  mixed  $keys
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            $values = $this->values;
            $schemas = $this->schemas;
        } else {
            $keys = Arr::wrap($keys);

            $values = Arr::only($this->values, $keys);
            $schemas = Arr::only($this->schemas, $keys);
        }

        return new self($values, $schemas);
    }

    /**
     * Exclude the given attribute from the attribute array.
     *
     * @param  mixed|array  $keys
     * @return static
     */
    public function except($keys)
    {
        if (is_null($keys)) {
            $values = $this->values;
            $schemas = $this->schemas;
        } else {
            $keys = Arr::wrap($keys);

            $values = Arr::except($this->values, $keys);
            $schemas = Arr::except($this->schemas, $keys);
        }

        return new self($values, $schemas);
    }

    /**
     * Filter the settings, returning a bag of settings that pass the filter.
     *
     * @param  callable  $callback
     */
    public function filter($callback): static
    {
        return new self(collect($this->values)->filter($callback)->all(), $this->schemas);
    }

    /**
     * Return a bag of attributes that have keys starting with the given value / pattern.
     *
     * @param  string|string[]  $needles
     * @return static
     */
    public function whereStartsWith($needles)
    {
        return $this->filter(function ($value, $key) use ($needles) {
            return Str::startsWith($key, $needles);
        });
    }

    /**
     * Return all raw values.
     *
     * @return array<TKey, TValue>
     */
    public function toArray()
    {
        return $this->values;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Get an iterator for traversing the settings values.
     *
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->values as $key => $value) {
            yield $key => $this->get($key); // Yield transformed values
        }
    }
}
