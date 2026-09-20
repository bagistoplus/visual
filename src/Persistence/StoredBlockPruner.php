<?php

namespace BagistoPlus\Visual\Persistence;

use Craftile\Laravel\BlockSchemaRegistry;

/**
 * Drops values the reader recreates anyway: properties equal to their schema
 * default and metadata at its default. Localized properties always stay stored
 * because translation references are collected from the raw file. Assumes
 * schema defaults are static; a default computed per request would resolve
 * differently after pruning.
 */
final class StoredBlockPruner
{
    protected const FALSE_FLAGS = ['disabled', 'static', 'repeated', 'ghost', 'keepInTree'];

    protected const DERIVED_KEYS = ['index', 'iteration'];

    protected array $schemaCache = [];

    public function __construct(protected BlockSchemaRegistry $schemas) {}

    public function prune(array $data, array $resolvedParent = []): array
    {
        if (empty($data['blocks']) || ! is_array($data['blocks'])) {
            return $data;
        }

        foreach ($data['blocks'] as $blockId => $block) {
            if (! is_array($block)) {
                continue;
            }

            $parentBlock = $resolvedParent['blocks'][$blockId] ?? [];
            $pruned = $this->pruneBlock($block, is_array($parentBlock) ? $parentBlock : []);

            if ($pruned === []) {
                unset($data['blocks'][$blockId]);

                continue;
            }

            $data['blocks'][$blockId] = $pruned;
        }

        if ($data['blocks'] === []) {
            unset($data['blocks']);
        }

        return $data;
    }

    protected function pruneBlock(array $block, array $parentBlock): array
    {
        if (isset($block['properties']) && is_array($block['properties'])) {
            $block['properties'] = $this->pruneProperties(
                $block['properties'],
                $this->propertySchemas($block['type'] ?? $parentBlock['type'] ?? null),
                $parentBlock['properties'] ?? []
            );

            if ($block['properties'] === []) {
                unset($block['properties']);
            }
        }

        foreach (self::FALSE_FLAGS as $flag) {
            if (array_key_exists($flag, $block) && $block[$flag] === false && ($parentBlock[$flag] ?? false) !== true) {
                unset($block[$flag]);
            }
        }

        if (array_key_exists('semanticId', $block) && $block['semanticId'] === null && ($parentBlock['semanticId'] ?? null) === null) {
            unset($block['semanticId']);
        }

        foreach (self::DERIVED_KEYS as $key) {
            unset($block[$key]);
        }

        return $block;
    }

    protected function pruneProperties(array $properties, array $schemas, mixed $parentProperties): array
    {
        $parentProperties = is_array($parentProperties) ? $parentProperties : [];

        foreach ($properties as $id => $value) {
            $schema = $schemas[$id] ?? null;

            if ($schema === null || ($schema['localized'] ?? false) === true) {
                continue;
            }

            $default = $schema['default'] ?? null;

            if (! $this->same($value, $default)) {
                continue;
            }

            if (array_key_exists($id, $parentProperties) && ! $this->same($parentProperties[$id], $default)) {
                continue;
            }

            unset($properties[$id]);
        }

        return $properties;
    }

    protected function propertySchemas(?string $type): array
    {
        if (! is_string($type) || $type === '') {
            return [];
        }

        if (array_key_exists($type, $this->schemaCache)) {
            return $this->schemaCache[$type];
        }

        $blockSchema = $this->schemas->get($type);

        if (! $blockSchema) {
            return $this->schemaCache[$type] = [];
        }

        return $this->schemaCache[$type] = collect($blockSchema->properties)
            ->map(fn ($property) => is_object($property) ? $property->toArray() : $property)
            ->filter(fn ($property) => is_array($property) && isset($property['id']))
            ->keyBy('id')
            ->all();
    }

    protected function same(mixed $a, mixed $b): bool
    {
        return json_encode($a) === json_encode($b);
    }
}
