<?php

use BagistoPlus\Visual\Blocks\BagistoSection;
use BagistoPlus\Visual\Blocks\BagistoSectionBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Data\NativeSectionType;
use BagistoPlus\Visual\Support\SectionBlockSchemaFactory;
use BagistoPlus\Visual\Support\SectionSystem;
use Illuminate\Support\Collection;

function sectionType(string $code, array $fields, bool $singleton = false, bool $pinned = false, bool $layout = false): NativeSectionType
{
    return new NativeSectionType(
        code: $code,
        title: Str::headline($code),
        icon: 'icon-image',
        singleton: $singleton,
        pinned: $pinned,
        rendersInLayout: $layout,
        fieldsResolver: fn () => $fields,
    );
}

function schemasFor(NativeSectionType $type): array
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

it('gives a repeater a block of its own that holds its entries', function () {
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
        'image_carousel.images',
        'image_carousel.images.item',
    ])
        ->and($schemas['image_carousel']->accepts)->toBe(['image_carousel.images'])
        ->and($schemas['image_carousel']->properties)->toBeEmpty()
        ->and($schemas['image_carousel.images']->accepts)->toBe(['image_carousel.images.item'])
        ->and($schemas['image_carousel.images']->properties)->toBeEmpty()
        ->and($schemas['image_carousel.images']->meta['repeater'])->toBe('images')
        ->and($schemas['image_carousel.images']->meta['addLabel'])->toBe('Add slide')
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
        'footer_links.columns',
        'footer_links.columns.item',
        'footer_links.columns.item.links',
        'footer_links.columns.item.links.item',
    ])
        ->and($schemas['footer_links.columns']->meta['max'])->toBe(4)
        ->and($schemas['footer_links.columns.item']->accepts)->toBe(['footer_links.columns.item.links'])
        ->and($schemas['footer_links.columns.item.links.item']->properties)->toHaveCount(2);
});

it('holds every repeater a type declares, not just the first', function () {
    $schemas = schemasFor(sectionType('rich', [
        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
        ['key' => 'slides', 'type' => 'repeater', 'label' => 'Slides', 'fields' => []],
        ['key' => 'services', 'type' => 'repeater', 'label' => 'Services', 'fields' => []],
    ]));

    expect($schemas['rich']->accepts)->toBe(['rich.slides', 'rich.services'])
        ->and($schemas['rich']->properties)->toHaveCount(1);
});

it('carries what the editor arranges a section by', function () {
    $schemas = schemasFor(sectionType('footer_links', [], singleton: true, pinned: true, layout: true));

    expect($schemas['footer_links']->meta)->toBe([
        'native' => true,
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
        ->toBe(['image-carousel', 'image-carousel-images', 'image-carousel-images-item']);
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
        'image_carousel.images',
        'image_carousel.images.item',
        'static_content',
    ])
        ->and($schemas->pluck('type')->duplicates())->toBeEmpty();
});
