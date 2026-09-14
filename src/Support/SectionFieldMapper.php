<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Settings\Base;
use BagistoPlus\Visual\Settings\Code;
use BagistoPlus\Visual\Settings\Filters;
use BagistoPlus\Visual\Settings\Image;
use BagistoPlus\Visual\Settings\Number;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Settings\Textarea;

/**
 * Reads the fields a section type is edited with as the settings the visual
 * editor draws.
 *
 * The editor offers no setting a section type cannot describe, so a section
 * edited here is a section Bagisto's own appearance editor can still edit. That
 * rules out mapping a field onto a richer setting than the one it was declared
 * as, and it rules out inventing fields a type did not ask for.
 *
 * A repeater is not among these. Its entries are drawn as nested blocks, which
 * `SectionBlockSchemaFactory` builds, so it is left out here rather than
 * flattened into a setting.
 */
class SectionFieldMapper
{
    public const TEXT = 'text';

    public const TEXTAREA = 'textarea';

    public const IMAGE = 'image';

    public const CODE = 'code';

    public const NUMBER = 'number';

    public const REPEATER = 'repeater';

    public const FILTERS = 'filters';

    /**
     * The settings that draw the given fields, in the order they were declared.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, Base>
     */
    public function properties(array $fields): array
    {
        $properties = [];

        foreach ($fields as $field) {
            if ($property = $this->property($field)) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * The fields that hold a repeating list of entries, keyed by the option
     * they are stored under.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, array<string, mixed>>
     */
    public function repeaters(array $fields): array
    {
        $repeaters = [];

        foreach ($fields as $field) {
            if (($field['type'] ?? null) === self::REPEATER && isset($field['key'])) {
                $repeaters[$field['key']] = $field;
            }
        }

        return $repeaters;
    }

    /**
     * A value as the field it belongs to is stored.
     *
     * The editor carries an image as the path it was uploaded to together with
     * what the merchant said about it, while Bagisto stores the path alone. The
     * path is what is written, so that a section saved here is a section
     * Bagisto's own appearance editor and storefront still read.
     */
    public function storageValue(string $type, mixed $value): mixed
    {
        if ($type !== self::IMAGE) {
            return $value;
        }

        if (is_string($value) || is_null($value)) {
            return $value;
        }

        $path = data_get($value, 'path');

        return is_string($path) ? $path : null;
    }

    /**
     * The kind each field is declared as, keyed by the option it is stored
     * under.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    public function kinds(array $fields): array
    {
        $kinds = [];

        foreach ($fields as $field) {
            if (is_string($field['key'] ?? null) && is_string($field['type'] ?? null)) {
                $kinds[$field['key']] = $field['type'];
            }
        }

        return $kinds;
    }

    /**
     * The setting that draws one field, or null for a kind the editor has no
     * setting for, which is left uneditable rather than drawn as the wrong one.
     *
     * @param  array<string, mixed>  $field
     */
    protected function property(array $field): ?Base
    {
        $key = $field['key'] ?? null;
        $type = $field['type'] ?? null;

        if (! is_string($key) || ! is_string($type)) {
            return null;
        }

        $label = is_string($field['label'] ?? null) ? $field['label'] : $key;

        $property = match ($type) {
            self::TEXT => Text::make($key, $label),
            self::TEXTAREA => Textarea::make($key, $label),
            self::NUMBER => Number::make($key, $label),
            self::IMAGE => Image::make($key, $label),
            self::CODE => $this->code($key, $label, $field),
            self::FILTERS => $this->filters($key, $label, $field),
            default => null,
        };

        return $property?->localized();
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function code(string $key, string $label, array $field): Code
    {
        $code = Code::make($key, $label);

        if (is_string($field['language'] ?? null)) {
            $code->language($field['language']);
        }

        return $code;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function filters(string $key, string $label, array $field): Filters
    {
        $filters = Filters::make($key, $label);

        if (is_array($field['keys'] ?? null)) {
            $filters->keys($field['keys']);
        }

        if (is_string($field['add_label'] ?? null)) {
            $filters->addLabel($field['add_label']);
        }

        return $filters;
    }
}
