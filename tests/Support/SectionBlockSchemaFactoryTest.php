<?php

use BagistoPlus\Visual\Blocks\BagistoSection;
use BagistoPlus\Visual\Blocks\BagistoSectionBlock;
use BagistoPlus\Visual\Data\BagistoSectionType;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Support\SectionBlockSchemaFactory;
use BagistoPlus\Visual\Support\SectionSystem;
use Illuminate\Support\Collection;

function sectionType(string $code, array $fields, bool $singleton = false, bool $pinned = false, bool $layout = false): BagistoSectionType
{
    return new BagistoSectionType(
        code: $code,
        title: Str::headline($code),
        icon: 'icon-image',
        singleton: $singleton,
        pinned: $pinned,
        rendersInLayout: $layout,
        view: "shop::sections.{$code}",
        fieldsResolver: fn () => $fields,
    );
}

function schemasFor(BagistoSectionType $type): array
{
    return collect(app(SectionBlockSchemaFactory::class)->forType($type))
        ->keyBy(fn (BlockSchema $schema) => $schema->type)
        ->all();
}

it('draws a section with no repeater as a single block', function () {
    $schemas = schemasFor(sectionType('static_content', [
        ['key' => 'html', 'type' => 'code', 'language' => 'html', 'label' => 'HTML'],
    ]));

    expect(array_keys($schemas))->toBe(['static_content'])
        ->and($schemas['static_content']->class)->toBe(BagistoSection::class)
        ->and($schemas['static_content']->properties)->toHaveCount(1)
        ->and($schemas['static_content']->accepts)->toBeEmpty();
});

it('holds the entries of a lone repeater on the section itself', function () {
    $schemas = schemasFor(sectionType('image_carousel', [
        [
            'key' => 'images',
            'type' => 'repeater',
            'label' => 'Slider',
            'add_label' => 'Add slide',
            'fields' => [
                ['key' => 'image', 'type' => 'image', 'label' => 'Image'],
                ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
            ],
        ],
    ]));

    expect(array_keys($schemas))->toBe([
        'image_carousel',
        'image_carousel.images.item',
    ])
        ->and($schemas['image_carousel']->accepts)->toBe(['image_carousel.images.item'])
        ->and($schemas['image_carousel']->properties)->toBeEmpty()
        ->and($schemas['image_carousel.images.item']->name)->toBe('Slider')
        ->and($schemas['image_carousel.images.item']->meta['entryOf'])->toBe('images')
        ->and($schemas['image_carousel.images.item']->meta['addLabel'])->toBe('Add slide')
        ->and($schemas['image_carousel.images.item']->properties)->toHaveCount(2)
        ->and($schemas['image_carousel.images.item']->class)->toBe(BagistoSectionBlock::class);
});

it('nests a repeater declared inside an entry', function () {
    $schemas = schemasFor(sectionType('footer_links', [
        [
            'key' => 'columns',
            'type' => 'repeater',
            'label' => 'Columns',
            'max' => 4,
            'fields' => [
                [
                    'key' => 'links',
                    'type' => 'repeater',
                    'label' => 'Links',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'url', 'type' => 'text', 'label' => 'URL'],
                    ],
                ],
            ],
        ],
    ]));

    expect(array_keys($schemas))->toBe([
        'footer_links',
        'footer_links.columns.item',
        'footer_links.columns.item.links.item',
    ])
        ->and($schemas['footer_links']->accepts)->toBe(['footer_links.columns.item'])
        ->and($schemas['footer_links.columns.item']->meta['max'])->toBe(4)
        ->and($schemas['footer_links.columns.item']->accepts)->toBe(['footer_links.columns.item.links.item'])
        ->and($schemas['footer_links.columns.item.links.item']->properties)->toHaveCount(2);
});

it('holds every repeater a type declares, not just the first', function () {
    $schemas = schemasFor(sectionType('rich', [
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'slides', 'type' => 'repeater', 'label' => 'Slides', 'fields' => []],
        ['key' => 'services', 'type' => 'repeater', 'label' => 'Services', 'fields' => []],
    ]));

    expect($schemas['rich']->accepts)->toBe(['rich.slides', 'rich.services'])
        ->and($schemas['rich']->properties)->toHaveCount(1)
        ->and(array_keys($schemas))->toBe([
            'rich',
            'rich.slides',
            'rich.slides.item',
            'rich.services',
            'rich.services.item',
        ]);
});

it('carries what the editor arranges a section by', function () {
    $schemas = schemasFor(sectionType('footer_links', [], singleton: true, pinned: true, layout: true));

    expect($schemas['footer_links']->meta)->toBe([
        'bagisto' => true,
        'singleton' => true,
        'pinned' => true,
        'rendersInLayout' => true,
    ]);
});

it('gives every schema a slug a blade component name tolerates', function () {
    $schemas = schemasFor(sectionType('image_carousel', [
        ['key' => 'images', 'type' => 'repeater', 'label' => 'Slider', 'fields' => []],
    ]));

    expect(collect($schemas)->map(fn (BlockSchema $schema) => $schema->slug)->values()->all())
        ->toBe(['image-carousel', 'image-carousel-images-item']);
});

it('builds the schemas of every type a theme offers', function () {
    app()->instance(SectionSystem::class, new class extends SectionSystem
    {
        public function types(?string $themeCode = null): Collection
        {
            return collect([
                'image_carousel' => sectionType('image_carousel', [
                    ['key' => 'images', 'type' => 'repeater', 'label' => 'Slider', 'fields' => []],
                ]),
                'static_content' => sectionType('static_content', [
                    ['key' => 'html', 'type' => 'code', 'label' => 'HTML'],
                ]),
            ]);
        }
    });

    $schemas = collect(app(SectionBlockSchemaFactory::class)->forTheme());

    expect($schemas->pluck('type')->all())->toBe([
        'image_carousel',
        'image_carousel.images.item',
        'static_content',
    ])
        ->and($schemas->pluck('type')->duplicates())->toBeEmpty();
});

it('hands the editor markup for a section\'s icon, not a font class', function () {
    $schemas = schemasFor(sectionType('image_carousel', [
        ['key' => 'images', 'type' => 'repeater', 'label' => 'Slider', 'fields' => []],
    ]));

    expect($schemas['image_carousel']->icon)->toStartWith('<svg')
        ->and($schemas['image_carousel.images.item']->icon)->toStartWith('<svg');
});
