<?php

namespace BagistoPlus\Visual\Persistence;

use Craftile\Laravel\BlockSchemaRegistry;

final class LocalizedProperties
{
    public function blockFragment(array $artifact): array
    {
        $blocks = $artifact['blocks'] ?? [];

        if (! is_array($blocks) || empty($artifact['regions']) || ! is_array($artifact['regions'])) {
            return [];
        }

        $localizedByType = $this->localizedBlockProperties();
        $fragment = ['blocks' => []];

        foreach ($this->reachableBlockIds($artifact) as $blockId) {
            $block = $blocks[$blockId] ?? null;
            $type = is_array($block) ? ($block['type'] ?? null) : null;

            if (! is_string($type) || empty($localizedByType[$type]) || ! isset($block['properties']) || ! is_array($block['properties'])) {
                continue;
            }

            foreach ($localizedByType[$type] as $propertyId) {
                if (array_key_exists($propertyId, $block['properties'])) {
                    $fragment['blocks'][$blockId]['properties'][$propertyId] = $block['properties'][$propertyId];
                }
            }
        }

        return $fragment['blocks'] === [] ? [] : $fragment;
    }

    public function themeSettingsFragment(array $settings, array $settingsSchema): array
    {
        $localized = collect($settingsSchema)
            ->map(fn ($group) => $group['settings'] ?? [])
            ->flatten(1)
            ->filter(fn ($schema) => ($schema['localized'] ?? false) === true)
            ->pluck('id')
            ->all();

        return collect($localized)
            ->filter(fn ($id) => array_key_exists($id, $settings))
            ->mapWithKeys(fn ($id) => [$id => $settings[$id]])
            ->all();
    }

    protected function localizedBlockProperties(): array
    {
        return collect(app(BlockSchemaRegistry::class)->all())
            ->mapWithKeys(function ($schema, $type) {
                $properties = collect($schema->properties)
                    ->map(fn ($property) => is_object($property) ? $property->toArray() : $property)
                    ->filter(fn ($property) => ($property['localized'] ?? false) === true)
                    ->pluck('id')
                    ->all();

                return [$type => $properties];
            })
            ->all();
    }

    protected function reachableBlockIds(array $artifact): array
    {
        $blocks = $artifact['blocks'] ?? [];
        $pending = collect($artifact['regions'])
            ->flatMap(fn ($region) => is_array($region) ? ($region['blocks'] ?? []) : [])
            ->values()
            ->all();
        $reachable = [];

        while ($pending !== []) {
            $blockId = array_shift($pending);

            if (! is_string($blockId) || in_array($blockId, $reachable, true) || ! isset($blocks[$blockId])) {
                continue;
            }

            $reachable[] = $blockId;

            if (! empty($blocks[$blockId]['children']) && is_array($blocks[$blockId]['children'])) {
                array_push($pending, ...$blocks[$blockId]['children']);
            }
        }

        return $reachable;
    }
}
