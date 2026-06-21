<?php

namespace BagistoPlus\Visual\Data;

use AllowDynamicProperties;
use BagistoPlus\Visual\Support\LiveUpdatesBuilder;
use Craftile\Laravel\BlockData as LaravelBlockData;
use Craftile\Laravel\PropertyBag;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Block data with Visual-specific magic properties.
 *
 * @property-read PropertyBag $settings Access to block properties (alias for $properties)
 * @property-read Htmlable $editorAttributes Craftile editor attributes for rendering
 */
#[AllowDynamicProperties]
class BlockData extends LaravelBlockData
{
    public static function make(array $blockData, $resolveChildData = null): static
    {
        return new static(
            id: $blockData['id'] ?? '',
            type: $blockData['type'] ?? '',
            properties: static::createPropertyBag($blockData['properties'] ?? [], $blockData['type'] ?? ''),
            name: isset($blockData['name']) ? static::resolveTranslationReference($blockData['name']) : null,
            parentId: $blockData['parentId'] ?? null,
            childrenIds: $blockData['children'] ?? [],
            disabled: $blockData['disabled'] ?? false,
            static: $blockData['static'] ?? false,
            repeated: $blockData['repeated'] ?? false,
            ghost: $blockData['ghost'] ?? false,
            semanticId: $blockData['semanticId'] ?? null,
            index: $blockData['index'] ?? null,
            resolveChildData: $resolveChildData,
        );
    }

    public function __get(string $key)
    {
        if ($key === 'settings') {
            return $this->properties;
        } elseif ($key === 'editorAttributes' || $key === 'editor_attributes') {
            return $this->editorAttributes();
        }

        return null;
    }

    public function editorAttributes()
    {
        return $this->craftileAttributes();
    }

    public function liveUpdate(?string $propertyId = null, ?string $attr = null): LiveUpdatesBuilder
    {
        $builder = new LiveUpdatesBuilder(blockId: $this->id);

        if ($propertyId && $attr) {
            return $builder->attr($propertyId, $attr);
        }

        if ($propertyId) {
            return $builder->text($propertyId);
        }

        return $builder;
    }

    protected static function createPropertyBag(array $properties, string $blockType): PropertyBag
    {
        $schemas = static::schemasFor($blockType);
        $preparedProperties = collect($schemas)
            ->mapWithKeys(function ($schema) use ($properties) {
                $value = array_key_exists($schema['id'], $properties)
                    ? $properties[$schema['id']]
                    : ($schema['default'] ?? null);

                return [
                    $schema['id'] => ($schema['localized'] ?? false) === true
                        ? static::resolveTranslationReferences($value, $schema)
                        : $value,
                ];
            })
            ->union($properties) // include any extra properties that are not defined in the schema
            ->all();

        return new PropertyBag($preparedProperties, $schemas);
    }

    protected static function schemasFor(string $blockType): array
    {
        if ($blockType === '') {
            return [];
        }

        $blockSchema = \craftile()->getBlockSchema($blockType);

        if (! $blockSchema) {
            return [];
        }

        return collect($blockSchema->properties)
            ->map(fn ($property) => is_object($property) ? $property->toArray() : $property)
            ->filter(fn ($schema) => array_key_exists('id', $schema))
            ->keyBy('id')
            ->toArray();
    }

    protected static function resolveTranslationReferences(mixed $value, array $schema): mixed
    {
        if (is_string($value)) {
            return static::resolveTranslationReference($value);
        }

        if (is_array($value) && ($schema['responsive'] ?? false) === true) {
            foreach ($value as $key => $item) {
                $value[$key] = is_string($item)
                    ? static::resolveTranslationReference($item)
                    : $item;
            }
        }

        return $value;
    }

    protected static function resolveTranslationReference(string $value): mixed
    {
        if (str_starts_with($value, 't:') && $value !== 't:') {
            return __(substr($value, 2));
        }

        return $value;
    }
}
