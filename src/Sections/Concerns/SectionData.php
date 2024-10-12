<?php

namespace BagistoPlus\Visual\Sections\Concerns;

use BagistoPlus\Visual\Sections\Section;
use Illuminate\Support\Fluent;
use JsonSerializable;

class SectionData implements JsonSerializable
{
    public array $blocks;

    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public Fluent $settings,
        public bool $disabled,
        protected array $allBlocks,
        public array $blocks_order
    ) {
        $this->blocks = collect($this->allBlocks)
            ->reject(fn ($block) => $block->disabled)
            ->sortKeysUsing(function ($a, $b) {
                return array_search($a, $this->blocks_order) - array_search($b, $this->blocks_order);
            })
            ->toArray();
    }

    public static function make(string $id, array $data, Section $section): self
    {
        $blocks = self::prepareBlocks($data['blocks'] ?? [], $section->blocks);

        return new self(
            id: $id,
            type: $data['type'] ?? $section->slug,
            name: $section->name,
            disabled: $data['disabled'] ?? false,
            settings: new Fluent(
                self::prepareSettings($data['settings'] ?? [], $section->settings)
            ),
            allBlocks: $blocks,
            blocks_order: $data['blocks_order'] ?? array_keys($blocks)
        );
    }

    protected static function prepareSettings(array $settings, array $settingsSchema): array
    {
        return collect($settingsSchema)
            ->reject(fn ($setting) => $setting['type'] === 'header')
            ->mapWithKeys(fn ($schema) => [
                $schema['id'] => $settings[$schema['id']] ?? $schema['default'] ?? null,
            ])
            ->toArray();
    }

    protected static function prepareBlocks(array $blocks, array $blocksSchemas): array
    {
        return collect($blocks)->map(function ($block, $id) use ($blocksSchemas) {
            $blockSchema = collect($blocksSchemas)->firstWhere('type', $block['type']);

            return BlockData::make($id, $block, $blockSchema);
        })->toArray();
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
}
