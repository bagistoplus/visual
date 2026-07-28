<?php

namespace BagistoPlus\Visual\Support;

use Craftile\Laravel\BlockSchemaRegistry;

class EditorTranslationReferenceCollector
{
    protected array $blocks = [];

    public function __construct(
        protected BlockSchemaRegistry $schemas,
    ) {}

    public function collect(array $blockData): void
    {
        $blockId = $blockData['id'];
        $blockType = $blockData['type'];
        $propertiesValues = $blockData['properties'] ?? [];

        $schema = $this->schemas->get($blockType);

        if (! $schema) {
            return;
        }

        foreach ($schema->properties as $propertySchema) {
            $propertySchema = is_object($propertySchema) ? $propertySchema->toArray() : $propertySchema;

            if (($propertySchema['localized'] ?? false) !== true) {
                continue;
            }

            $propertyId = $propertySchema['id'];

            if (! array_key_exists($propertyId, $propertiesValues)) {
                continue;
            }

            $reference = $this->translationReferenceValue($propertiesValues[$propertyId], ($propertySchema['responsive'] ?? false) === true);

            if ($reference === null) {
                continue;
            }

            $this->blocks[$blockId]['properties'][$propertyId] = $reference;
        }
    }

    public function forBlockIds(array $blockIds): array
    {
        $blocks = [];

        foreach ($blockIds as $blockId) {
            if (is_string($blockId) && isset($this->blocks[$blockId])) {
                $blocks[$blockId] = $this->blocks[$blockId];
            }
        }

        return $blocks === [] ? [] : ['blocks' => $blocks];
    }

    public function reset(): void
    {
        $this->blocks = [];
    }

    protected function translationReferenceValue(mixed $value, bool $responsive): mixed
    {
        if ($this->isTranslationReference($value)) {
            return $value;
        }

        if (! $responsive || ! is_array($value)) {
            return null;
        }

        $references = [];

        foreach ($value as $key => $item) {
            if ($this->isTranslationReference($item)) {
                $references[$key] = $item;
            }
        }

        return $references === [] ? null : $references;
    }

    protected function isTranslationReference(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, 't:') && $value !== 't:';
    }
}
