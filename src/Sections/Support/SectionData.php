<?php

namespace BagistoPlus\Visual\Sections\Support;

use BagistoPlus\Visual\Sections\Section;
use BagistoPlus\Visual\Settings\Support\SettingsValues;
use JsonSerializable;

class SectionData implements JsonSerializable
{
    public array $blocks;

    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public SettingsValues $settings,
        public bool $disabled,
        protected array $allBlocks,
        public array $blocks_order,
        public ?string $sourceFile = null,
    ) {
        $this->blocks = collect($this->blocks_order)
            ->map(fn ($id) => $this->allBlocks[$id])
            ->reject(fn ($block) => $block->disabled)
            ->sortKeysUsing(function ($a, $b) {
                return array_search($a, $this->blocks_order) - array_search($b, $this->blocks_order);
            })
            ->all();
    }

    public static function make(string $id, array $data, Section $section, ?string $sourceFile = null): self
    {

        $blocks = self::prepareBlocks($data['blocks'] ?? [], $section->blocks, $id);

        return new self(
            id: $id,
            type: $data['type'] ?? $section->slug,
            name: $section->name,
            disabled: $data['disabled'] ?? false,
            settings: new SettingsValues(
                self::prepareSettings($data['settings'] ?? [], $section->settings),
                collect($section->settings)->keyBy('id')->toArray()
            ),
            allBlocks: $blocks,
            blocks_order: $data['blocks_order'] ?? array_keys($blocks),
            sourceFile: $sourceFile
        );
    }

    protected static function prepareSettings(array $settings, array $settingsSchemas): array
    {
        return collect($settingsSchemas)
            ->reject(fn ($schema) => $schema['type'] === 'header')
            ->mapWithKeys(fn ($schema) => [
                $schema['id'] => $settings[$schema['id']] ?? $schema['default'] ?? null,
            ])
            ->all();
    }

    protected static function prepareBlocks(array $blocks, array $blocksSchemas, string $sectionId): array
    {

        return collect($blocks)->map(function ($block, $id) use ($blocksSchemas, $sectionId) {
            $blockSchema = collect($blocksSchemas)->firstWhere('type', $block['type']);

            return BlockData::make(
                id: $id,
                data: $block,
                sectionId: $sectionId,
                blockSchema: $blockSchema
            );
        })->all();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'disabled' => $this->disabled,
            'settings' => $this->settings->toArray(),
            'blocks' => array_map(fn ($block) => $block->jsonSerialize(), $this->allBlocks),
            'blocks_order' => $this->blocks_order,
        ];
    }

    public function liveUpdate(?string $settingId = null, ?string $attr = null): LiveUpdatesBuilder
    {
        $builder = new LiveUpdatesBuilder(sectionId: $this->id, blockId: null);

        if ($settingId && $attr) {
            return $builder->attr($settingId, $attr);
        }

        if ($settingId) {
            return $builder->text($settingId);
        }

        return $builder;
    }
}
