<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\NativeSectionType;
use BagistoPlus\Visual\Data\SectionDraft;
use Illuminate\Support\Collection;

/**
 * Reads the page the editor arranged back as what each section row should hold.
 *
 * The mirror of `SectionPageBuilder`. A repeating list is rebuilt from the
 * order its block holds its entries in, never from the ids those entries
 * carry, because an id records where an entry was when the page was read and
 * says nothing about where the merchant has since dragged it.
 *
 * An option a section type does not declare is left as it was found. The editor
 * never drew it, so it is not the editor's to remove.
 */
class SectionDraftBuilder
{
    public function __construct(
        protected SectionSystem $sectionSystem,
        protected SectionFieldMapper $fieldMapper,
        protected SectionBlockIds $ids,
    ) {}

    /**
     * What each section row should hold, ordered as the page holds them.
     *
     * Sections drawn on every page are numbered after the rest, which is where
     * Bagisto's own editor keeps them.
     *
     * @param  array<string, array<string, mixed>>  $blocks
     * @param  array<int, array<string, mixed>>  $regions
     * @param  array<int, array<string, mixed>>  $existingOptions  Options as stored, keyed by row id.
     * @return Collection<int, SectionDraft>
     */
    public function build(
        array $blocks,
        array $regions,
        array $existingOptions = [],
        ?string $themeCode = null
    ): Collection {
        $types = $this->sectionSystem->types($themeCode);
        $drafts = collect();
        $order = 0;

        foreach ($this->rootIds($regions) as $blockId) {
            $block = $blocks[$blockId] ?? null;

            if (! is_array($block)) {
                continue;
            }

            $type = $types->get($block['type'] ?? '');

            if (! $type) {
                continue;
            }

            $rowId = $this->ids->rowId($blockId);

            $drafts->push(new SectionDraft(
                blockId: $blockId,
                rowId: $rowId,
                type: $type->code,
                name: $this->name($block, $type),
                options: $this->options($block, $type->fields(), $blocks, $existingOptions[$rowId] ?? []),
                status: ! ($block['disabled'] ?? false),
                sortOrder: ++$order,
            ));
        }

        return $drafts;
    }

    /**
     * What the section is called, falling back to the name of its type for one
     * the merchant has just added and not yet named.
     *
     * @param  array<string, mixed>  $block
     */
    protected function name(array $block, NativeSectionType $type): string
    {
        $name = $block['name'] ?? null;

        return is_string($name) && $name !== '' ? $name : $type->title;
    }

    /**
     * The sections a page holds, the ones drawn on every page last.
     *
     * @param  array<int, array<string, mixed>>  $regions
     * @return array<int, string>
     */
    protected function rootIds(array $regions): array
    {
        $byName = collect($regions)->keyBy(fn (array $region) => $region['name'] ?? '');

        return array_merge(
            $byName->get(SectionPageBuilder::CONTENT_REGION)['blocks'] ?? [],
            $byName->get(SectionPageBuilder::LAYOUT_REGION)['blocks'] ?? [],
        );
    }

    /**
     * The options a block holds: the values of the fields it was drawn with,
     * the entries of every repeating list beneath it, and whatever the type
     * does not declare, left as it was.
     *
     * @param  array<string, mixed>  $block
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    protected function options(array $block, array $fields, array $blocks, array $existing): array
    {
        $options = $this->undeclared($fields, $existing);

        foreach ($this->fieldMapper->properties($fields) as $property) {
            $options[$property->id] = $block['properties'][$property->id] ?? null;
        }

        $lists = $this->listsBeneath($block, $blocks);

        foreach ($this->fieldMapper->repeaters($fields) as $key => $repeater) {
            $entryFields = is_array($repeater['fields'] ?? null) ? $repeater['fields'] : [];
            $list = $lists[$key] ?? null;

            $options[$key] = $list === null
                ? ($existing[$key] ?? [])
                : $this->entries($list, $entryFields, $blocks, $existing[$key] ?? []);
        }

        return $options;
    }

    /**
     * The lists a block holds, keyed by the option each is stored under.
     *
     * @param  array<string, mixed>  $block
     * @param  array<string, array<string, mixed>>  $blocks
     * @return array<string, array<string, mixed>>
     */
    protected function listsBeneath(array $block, array $blocks): array
    {
        $lists = [];

        foreach ($block['children'] ?? [] as $childId) {
            $child = $blocks[$childId] ?? null;
            $key = is_array($child) ? ($child['semanticId'] ?? null) : null;

            if (is_string($key)) {
                $lists[$key] = $child;
            }
        }

        return $lists;
    }

    /**
     * The entries a list holds, in the order it holds them.
     *
     * @param  array<string, mixed>  $list
     * @param  array<int, array<string, mixed>>  $entryFields
     * @param  array<string, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    protected function entries(array $list, array $entryFields, array $blocks, mixed $existing): array
    {
        $stored = array_values(array_filter(is_array($existing) ? $existing : [], 'is_array'));
        $entries = [];

        foreach ($list['children'] ?? [] as $position => $entryId) {
            $entry = $blocks[$entryId] ?? null;

            if (! is_array($entry)) {
                continue;
            }

            $entries[] = $this->options($entry, $entryFields, $blocks, $stored[$position] ?? []);
        }

        return $entries;
    }

    /**
     * The options a section type does not declare, which the editor never drew
     * and must not drop.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    protected function undeclared(array $fields, array $existing): array
    {
        $declared = array_merge(
            array_map(fn ($property) => $property->id, $this->fieldMapper->properties($fields)),
            array_keys($this->fieldMapper->repeaters($fields)),
        );

        return array_diff_key($existing, array_flip($declared));
    }
}
