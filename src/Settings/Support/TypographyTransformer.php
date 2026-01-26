<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;

class TypographyTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, mixed $property = null): mixed
    {
        if (! $value || ! is_string($value)) {
            return null;
        }

        // In editor context, return just the ID
        if (request()->is('admin/visual/editor*')) {
            return $value;
        }

        // Find the TypographyPresets and get the preset data
        /** @var \BagistoPlus\Visual\Theme\Theme $theme */
        $theme = themes()->current();

        $presetsSetting = collect($theme->settingsSchema)
            ->flatMap(fn ($group) => $group['settings'])
            ->first(fn ($setting) => $setting['type'] === 'typography_presets');

        if (! $presetsSetting) {
            return null;
        }

        $presets = $theme->settings->get($presetsSetting['id']);
        $presetData = $presets[$value] ?? null;

        if (! $presetData) {
            return null;
        }

        return new TypographyValue($presetData->toArray(), $value);
    }
}
