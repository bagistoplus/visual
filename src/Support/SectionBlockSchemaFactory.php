<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Blocks\BagistoSection;
use BagistoPlus\Visual\Blocks\BagistoSectionBlock;
use BagistoPlus\Visual\Data\BagistoSectionType;
use BagistoPlus\Visual\Data\BlockSchema;
use Illuminate\Support\Str;

/**
 * Builds the block schemas the editor draws a Bagisto section with.
 *
 * A section type describes itself as a flat list of fields, where a repeater
 * holds a list of entries under one option key. Craftile blocks hold a single
 * unnamed list of children, so each repeater becomes a block of its own that
 * holds its entries, and the option key it is stored under is the block's own
 * place in the tree rather than a name its children have to carry.
 *
 * Declare one list and its entries are held by whatever declared them:
 *
 *     image_carousel
 *     └── image_carousel.images.item     a slide
 *
 * Declare several and each gets a block of its own, so that an entry's place
 * says which list it belongs to:
 *
 *     rich_section
 *     ├── rich_section.slides            the first list
 *     │   └── rich_section.slides.item
 *     └── rich_section.services          the second
 *         └── rich_section.services.item
 *
 * A list declared inside an entry nests the same way, which is how footer links
 * reach their columns and the links within them.
 *
 * Building a schema reads the fields of the type it is built from, and a
 * product carousel builds its filter options from the catalogue. Schemas are
 * therefore built for the editor, never for a storefront request, which renders
 * its sections through Bagisto and needs none of them.
 */
class SectionBlockSchemaFactory
{
    /**
     * Separates a repeater, and its entries, from what declared them.
     */
    public const SEPARATOR = '.';

    /**
     * The type an entry of a repeater is registered under.
     */
    public const ENTRY = 'item';

    public function __construct(
        protected SectionFieldMapper $fieldMapper,
        protected SectionSystem $sectionSystem,
        protected SectionIcons $icons,
    ) {}

    /**
     * Every schema the editor needs to draw the sections a theme offers.
     *
     * @return array<int, BlockSchema>
     */
    public function forTheme(?string $themeCode = null): array
    {
        return $this->sectionSystem->types($themeCode)
            ->flatMap(fn (BagistoSectionType $type) => $this->forType($type))
            ->all();
    }

    /**
     * The schema of one section type, followed by the schemas of every
     * repeating list it declares, however deeply they nest.
     *
     * @return array<int, BlockSchema>
     */
    public function forType(BagistoSectionType $type): array
    {
        $fields = $type->fields();

        $section = $this->schema(
            type: $type->code,
            name: $type->title,
            icon: $type->icon,
            fields: $fields,
            class: BagistoSection::class,
        );

        $section->meta = [
            'bagisto' => true,
            'singleton' => $type->singleton,
            'pinned' => $type->pinned,
            'rendersInLayout' => $type->rendersInLayout,
        ];

        // Which region a section belongs to is decided by its type, not by where
        // it was dropped: the page is read back split on the same flag, so a
        // section added to the other region would move on the next reload.
        $section->enabledOn = [
            'regions' => [
                $type->rendersInLayout ? SectionPageBuilder::LAYOUT_REGION : SectionPageBuilder::CONTENT_REGION,
            ],
        ];

        return array_merge([$section], $this->repeaterSchemas($type->code, $fields));
    }

    /**
     * The schemas of the repeating lists declared among the given fields: one
     * for each list, one for its entries, and the same again for any list an
     * entry declares.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, BlockSchema>
     */
    protected function repeaterSchemas(string $parentType, array $fields): array
    {
        $repeaters = $this->fieldMapper->repeaters($fields);
        $flatten = $this->flattens($fields);
        $schemas = [];

        foreach ($repeaters as $key => $repeater) {
            $listType = $parentType.self::SEPARATOR.$key;
            $entryType = $listType.self::SEPARATOR.self::ENTRY;
            $entryFields = is_array($repeater['fields'] ?? null) ? $repeater['fields'] : [];

            if (! $flatten) {
                $list = $this->schema(
                    type: $listType,
                    name: $this->label($repeater, $key),
                    icon: 'icon-list',
                    fields: [],
                    class: BagistoSectionBlock::class,
                    accepts: [$entryType],
                );

                $list->meta = [
                    'bagisto' => true,
                    'repeater' => $key,
                    'addLabel' => is_string($repeater['add_label'] ?? null) ? $repeater['add_label'] : null,
                    'max' => $repeater['max'] ?? null,
                ];

                $schemas[] = $list;
            }

            $entry = $this->schema(
                type: $entryType,
                name: Str::singular($this->label($repeater, $key)),
                icon: 'icon-cms',
                fields: $entryFields,
                class: BagistoSectionBlock::class,
            );

            $entry->meta = [
                'bagisto' => true,
                'entryOf' => $key,
                'addLabel' => is_string($repeater['add_label'] ?? null) ? $repeater['add_label'] : null,
                'max' => $repeater['max'] ?? null,
            ];

            $schemas[] = $entry;
            $schemas = array_merge($schemas, $this->repeaterSchemas($entryType, $entryFields));
        }

        return $schemas;
    }

    /**
     * Whether the entries of the lists declared among these fields are held by
     * whatever declared them, rather than by a block standing for the list.
     *
     * A block holds one unnamed list of children, so a list needs a block of
     * its own only when it would otherwise be mistaken for another. Declare one
     * list and its entries sit directly under the section, which is a level of
     * the tree the merchant does not have to open.
     *
     * @param  array<int, array<string, mixed>>  $fields
     */
    protected function flattens(array $fields): bool
    {
        return count($this->fieldMapper->repeaters($fields)) === 1;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, string>  $accepts
     */
    protected function schema(
        string $type,
        string $name,
        string $icon,
        array $fields,
        string $class,
        array $accepts = []
    ): BlockSchema {
        return new BlockSchema(
            type: $type,
            slug: Str::slug(str_replace([self::SEPARATOR, '_'], '-', $type)),
            class: $class,
            name: $name,
            icon: $this->icons->svg($icon),
            properties: $this->fieldMapper->properties($fields),
            accepts: $accepts ?: $this->repeaterTypes($type, $fields),
        );
    }

    /**
     * The repeating lists declared among the given fields, as the block types
     * their parent holds.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, string>
     */
    protected function repeaterTypes(string $parentType, array $fields): array
    {
        $suffix = $this->flattens($fields) ? self::SEPARATOR.self::ENTRY : '';

        return array_map(
            fn (string $key) => $parentType.self::SEPARATOR.$key.$suffix,
            array_keys($this->fieldMapper->repeaters($fields))
        );
    }

    /**
     * @param  array<string, mixed>  $repeater
     */
    protected function label(array $repeater, string $key): string
    {
        return is_string($repeater['label'] ?? null) ? $repeater['label'] : Str::headline($key);
    }
}
