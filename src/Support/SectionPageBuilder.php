<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\NativeSectionType;
use BagistoPlus\Visual\Data\SectionRecord;
use Illuminate\Support\Collection;

/**
 * Reads `theme_sections` rows as the page the editor arranges.
 *
 * Craftile holds a page as a flat map of blocks that name their children by id,
 * gathered into regions. A section row becomes one block, and each entry of a
 * repeating list its type declares becomes a block of its own, which is what
 * lets an entry be selected and dragged rather than edited down a column of the
 * sidebar. A type declaring several lists gets a block per list, so that an
 * entry's place says which list it belongs to; declaring one, the entries are
 * held by the section itself.
 *
 * The sections a type draws on every page are gathered into their own region,
 * so that editing a footer from the home page does not read as a change to the
 * home page alone.
 */
class SectionPageBuilder
{
    public const CONTENT_REGION = 'content';

    public const LAYOUT_REGION = 'layout';

    public function __construct(
        protected SectionSystem $sectionSystem,
        protected SectionFieldMapper $fieldMapper,
        protected SectionBlockIds $ids,
    ) {}

    /**
     * @param  iterable<SectionRecord>  $records
     * @return array{blocks: array<string, array<string, mixed>>, regions: array<int, array<string, mixed>>}
     */
    public function build(iterable $records, ?string $themeCode = null): array
    {
        $types = $this->sectionSystem->types($themeCode);

        $blocks = [];
        $content = [];
        $layout = [];

        foreach ($this->ordered($records) as $record) {
            $type = $types->get($record->type);

            if (! $type) {
                continue;
            }

            $id = $this->ids->section($record->id);

            $blocks[$id] = $this->sectionBlock($id, $record, $type, $blocks);

            if ($type->rendersInLayout) {
                $layout[] = $id;
            } else {
                $content[] = $id;
            }
        }

        return [
            'blocks' => $blocks,
            'regions' => [
                ['name' => self::CONTENT_REGION, 'shared' => false, 'blocks' => $content],
                ['name' => self::LAYOUT_REGION, 'shared' => true, 'blocks' => $layout],
            ],
        ];
    }

    /**
     * @param  iterable<SectionRecord>  $records
     * @return Collection<int, SectionRecord>
     */
    protected function ordered(iterable $records): Collection
    {
        return collect($records)->sortBy(fn (SectionRecord $record) => $record->sortOrder)->values();
    }

    /**
     * @param  array<string, array<string, mixed>>  $blocks
     * @return array<string, mixed>
     */
    protected function sectionBlock(string $id, SectionRecord $record, NativeSectionType $type, array &$blocks): array
    {
        $fields = $type->fields();

        return [
            'id' => $id,
            'type' => $type->code,
            'name' => $record->name,
            'properties' => $this->scalarProperties($fields, $record->options),
            'children' => $this->listBlocks($id, $type->code, $fields, $record->options, $blocks),
            'disabled' => ! $record->status,
        ];
    }

    /**
     * A block per repeating list declared among the fields, each holding a
     * block per entry stored under it.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $values
     * @param  array<string, array<string, mixed>>  $blocks
     * @return array<int, string>
     */
    protected function listBlocks(
        string $parentId,
        string $parentType,
        array $fields,
        array $values,
        array &$blocks
    ): array {
        $repeaters = $this->fieldMapper->repeaters($fields);
        $flatten = count($repeaters) === 1;
        $ids = [];

        foreach ($repeaters as $key => $repeater) {
            $listId = $this->ids->child($parentId, $key);
            $listType = $parentType.SectionBlockSchemaFactory::SEPARATOR.$key;
            $entryType = $listType.SectionBlockSchemaFactory::SEPARATOR.SectionBlockSchemaFactory::ENTRY;
            $entryFields = is_array($repeater['fields'] ?? null) ? $repeater['fields'] : [];
            $holderId = $flatten ? $parentId : $listId;

            $entryIds = [];

            foreach ($this->entries($values, $key) as $index => $entry) {
                $entryId = $this->ids->child($listId, $index);

                $blocks[$entryId] = [
                    'id' => $entryId,
                    'type' => $entryType,
                    'parentId' => $holderId,
                    'index' => $index,
                    'properties' => $this->scalarProperties($entryFields, $entry),
                    'children' => $this->listBlocks($entryId, $entryType, $entryFields, $entry, $blocks),
                ];

                $entryIds[] = $entryId;
            }

            if ($flatten) {
                return $entryIds;
            }

            $blocks[$listId] = [
                'id' => $listId,
                'type' => $listType,
                'parentId' => $parentId,
                'properties' => [],
                'children' => $entryIds,
                'static' => true,
                'semanticId' => $key,
            ];

            $ids[] = $listId;
        }

        return $ids;
    }

    /**
     * The entries stored under a repeating list, numbered from zero however
     * they were keyed, since Bagisto writes some of them as a map.
     *
     * @param  array<string, mixed>  $values
     * @return array<int, array<string, mixed>>
     */
    protected function entries(array $values, string $key): array
    {
        $entries = $values[$key] ?? [];

        if (! is_array($entries)) {
            return [];
        }

        return array_values(array_filter($entries, 'is_array'));
    }

    /**
     * The values of the fields that are not repeating lists, which the block
     * holding them carries as its own properties.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function scalarProperties(array $fields, array $values): array
    {
        $properties = [];

        foreach ($this->fieldMapper->properties($fields) as $property) {
            if (array_key_exists($property->id, $values)) {
                $properties[$property->id] = $values[$property->id];
            }
        }

        return $properties;
    }
}
