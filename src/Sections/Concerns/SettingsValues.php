<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TValue
 */
class SettingsValues implements JsonSerializable
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

    protected array $resolved = [];

    /**
     * All registered transformers
     *
     * @var array<string, callable>
     */
    protected static array $transformers = [];

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

    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Get a value.
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

    protected function transformValue($value, ?string $type)
    {
        // Use custom transformer if registered
        if (isset(self::$transformers[$type])) {
            $transformer = self::$transformers[$type];

            return $transformer($value);
        }

        return match ($type) {
            'color' => (new ColorTransformer)($value),
            default => $value,
        };
    }

    public function toArray()
    {
        return $this->values;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
