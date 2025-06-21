<?php

namespace BagistoPlus\Visual\Sections\Support;

use BagistoPlus\Visual\Settings\Support\SettingsValues;
use JsonSerializable;

class BlockData implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public SettingsValues $settings,
        public bool $disabled,
        public string $sectionId
    ) {}

    public static function make(string $id, array $data, array $blockSchema, string $sectionId): self
    {
        return new self(
            id: $id,
            sectionId: $sectionId,
            type: $data['type'] ?? $blockSchema['type'],
            name: $data['name'] ?? $blockSchema['name'] ?? $data['type'],
            disabled: $data['disabled'] ?? false,
            settings: new SettingsValues(
                self::prepareSettings($data['settings'] ?? [], $blockSchema['settings'] ?? []),
                collect($blockSchema['settings'] ?? [])->keyBy('id')->toArray()
            )
        );
    }

    public static function prepareSettings(array $settings, array $settingsSchema): array
    {
        return collect($settingsSchema)
            ->reject(fn ($setting) => $setting['type'] === 'header')
            ->mapWithKeys(fn ($schema) => [
                $schema['id'] => array_key_exists($schema['id'], $settings) ? $settings[$schema['id']] : ($schema['default'] ?? null),
            ])
            ->toArray();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'disabled' => $this->disabled,
            'settings' => $this->settings->toArray(),
        ];
    }

    public function liveUpdate(?string $settingId = null, ?string $attr = null): LiveUpdatesBuilder
    {
        $builder = new LiveUpdatesBuilder(sectionId: $this->sectionId, blockId: $this->id);

        if ($settingId && $attr) {
            return $builder->attr($settingId, $attr);
        }

        if ($settingId) {
            return $builder->text($settingId);
        }

        return $builder;
    }
}
