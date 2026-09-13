<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Blocks\BagistoSection;
use BagistoPlus\Visual\Blocks\BagistoSectionBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Data\NativeSectionType;
use Illuminate\Support\Str;

/**
 * Builds the block schemas the editor draws a native section with.
 *
 * A section type describes itself as a flat list of fields, where a repeater
 * holds a list of entries under one option key. Craftile blocks hold a single
 * unnamed list of children, so each repeater becomes a block of its own that
 * holds its entries, and the option key it is stored under is the block's own
 * place in the tree rather than a name its children have to carry.
 *
 * An image carousel is read as:
 *
 *     image_carousel
 *     └── image_carousel.images          the repeater
 *         └── image_carousel.images.item an entry
 *
 * A repeater declared inside an entry nests the same way, which is how footer
 * links reach their columns and the links within them.
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
    ) {}

    /**
     * Every schema the editor needs to draw the sections a theme offers.
     *
     * @return array<int, BlockSchema>
     */
    public function forTheme(?string $themeCode = null): array
    {
        return $this->sectionSystem->types($themeCode)
            ->flatMap(fn (NativeSectionType $type) => $this->forType($type))
            ->all();
    }

    /**
     * The schema of one section type, followed by the schemas of every
     * repeating list it declares, however deeply they nest.
     *
     * @return array<int, BlockSchema>
     */
    public function forType(NativeSectionType $type): array
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
            'native' => true,
            'singleton' => $type->singleton,
            'pinned' => $type->pinned,
            'rendersInLayout' => $type->rendersInLayout,
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
        $schemas = [];

        foreach ($this->fieldMapper->repeaters($fields) as $key => $repeater) {
            $listType = $parentType.self::SEPARATOR.$key;
            $entryType = $listType.self::SEPARATOR.self::ENTRY;
            $entryFields = is_array($repeater['fields'] ?? null) ? $repeater['fields'] : [];

            $list = $this->schema(
                type: $listType,
                name: $this->label($repeater, $key),
                icon: 'icon-list',
                fields: [],
                class: BagistoSectionBlock::class,
                accepts: [$entryType],
            );

            $list->meta = [
                'native' => true,
                'repeater' => $key,
                'addLabel' => is_string($repeater['add_label'] ?? null) ? $repeater['add_label'] : null,
                'max' => $repeater['max'] ?? null,
            ];

            $entry = $this->schema(
                type: $entryType,
                name: $this->label($repeater, $key),
                icon: 'icon-cms',
                fields: $entryFields,
                class: BagistoSectionBlock::class,
            );

            $entry->meta = ['native' => true, 'entryOf' => $key];

            $schemas[] = $list;
            $schemas[] = $entry;
            $schemas = array_merge($schemas, $this->repeaterSchemas($entryType, $entryFields));
        }

        return $schemas;
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
            icon: $icon,
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
        return array_map(
            fn (string $key) => $parentType.self::SEPARATOR.$key,
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
