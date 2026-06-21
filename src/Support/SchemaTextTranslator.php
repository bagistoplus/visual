<?php

namespace BagistoPlus\Visual\Support;

class SchemaTextTranslator
{
    public function translateText(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if ($value === '') {
            return '';
        }

        if ($value === 't:') {
            return '';
        }

        if (str_starts_with($value, 't:')) {
            return trans(substr($value, 2));
        }

        return trans($value);
    }

    public function translatePropertySchema(array $property): array
    {
        foreach (['label', 'info', 'group'] as $key) {
            if (array_key_exists($key, $property)) {
                $property[$key] = $this->translateText($property[$key]);
            }
        }

        if (isset($property['options']) && is_array($property['options'])) {
            $property['options'] = $this->translateOptions($property['options']);
        }

        return $property;
    }

    public function translateBlockMeta(array $meta): array
    {
        foreach (['name', 'description', 'category'] as $key) {
            if (array_key_exists($key, $meta)) {
                $meta[$key] = $this->translateText($meta[$key]);
            }
        }

        return $meta;
    }

    public function translatePreset(array $preset): array
    {
        foreach (['name', 'description', 'category'] as $key) {
            if (array_key_exists($key, $preset)) {
                $preset[$key] = $this->translateText($preset[$key]);
            }
        }

        if (isset($preset['children']) && is_array($preset['children'])) {
            $preset['children'] = $this->translatePresetChildren($preset['children']);
        }

        return $preset;
    }

    public function translatePresetChildren(array $children): array
    {
        return collect($children)->map(function (mixed $child) {
            if (! is_array($child)) {
                return $child;
            }

            if (array_key_exists('name', $child)) {
                $child['name'] = $this->translateText($child['name']);
            }

            if (isset($child['children']) && is_array($child['children'])) {
                $child['children'] = $this->translatePresetChildren($child['children']);
            }

            return $child;
        })->all();
    }

    public function translateThemeSettingsSchema(array $settingsSchema): array
    {
        return collect($settingsSchema)->map(function (array $group) {
            if (array_key_exists('name', $group)) {
                $group['name'] = $this->translateText($group['name']);
            }

            if (isset($group['settings']) && is_array($group['settings'])) {
                $group['settings'] = collect($group['settings'])
                    ->map(fn (array $setting) => $this->translatePropertySchema($setting))
                    ->all();
            }

            return $group;
        })->all();
    }

    public function translateOptions(array $options): array
    {
        return collect($options)->map(function (mixed $option) {
            if (is_array($option) && array_key_exists('label', $option)) {
                $option['label'] = $this->translateText($option['label']);
            }

            return $option;
        })->all();
    }
}
