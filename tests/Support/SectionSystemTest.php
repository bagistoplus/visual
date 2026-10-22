<?php

use BagistoPlus\Visual\Data\BagistoSectionType;
use BagistoPlus\Visual\Support\SectionSystem;
use Illuminate\Support\Collection;

function sectionSystem(bool $available): SectionSystem
{
    return new class($available) extends SectionSystem
    {
        public function __construct(protected bool $isAvailable) {}

        public function available(): bool
        {
            return $this->isAvailable;
        }
    };
}

function bindSectionSchema(object $schema): void
{
    app()->instance('Webkul\Theme\SectionSchema', $schema);
}

function fakeSectionType(
    string $code,
    string $title,
    string $icon,
    bool $singleton = false,
    bool $pinned = false,
    bool $layout = false,
    array $fields = []
): object {
    return new class($code, $title, $icon, $singleton, $pinned, $layout, $fields)
    {
        public function __construct(
            protected string $code,
            protected string $title,
            protected string $icon,
            protected bool $singleton,
            protected bool $pinned,
            protected bool $layout,
            protected array $fields
        ) {}

        public function getCode(): string
        {
            return $this->code;
        }

        public function getTitle(): string
        {
            return $this->title;
        }

        public function getIcon(): string
        {
            return $this->icon;
        }

        public function isSingleton(): bool
        {
            return $this->singleton;
        }

        public function isPinned(): bool
        {
            return $this->pinned;
        }

        public function rendersInLayout(): bool
        {
            return $this->layout;
        }

        public function getFields(): array
        {
            return $this->fields;
        }
    };
}

it('offers no section types when bagisto does not declare them', function () {
    expect(sectionSystem(available: false)->types())->toBeEmpty();
});

it('reads the types a theme declares, each describing itself', function () {
    bindSectionSchema(new class
    {
        public function types(?string $themeCode = null): Collection
        {
            return collect([
                'hero' => fakeSectionType(
                    code: 'hero',
                    title: 'Hero',
                    icon: 'icon-star',
                    singleton: true,
                    fields: [['key' => 'title', 'type' => 'text', 'label' => 'Title']],
                ),
            ]);
        }
    });

    $types = sectionSystem(available: true)->types('fashion');

    expect($types->keys()->all())->toBe(['hero'])
        ->and($types->get('hero'))->toBeInstanceOf(BagistoSectionType::class)
        ->and($types->get('hero')->title)->toBe('Hero')
        ->and($types->get('hero')->icon)->toBe('icon-star')
        ->and($types->get('hero')->singleton)->toBeTrue()
        ->and($types->get('hero')->pinned)->toBeFalse()
        ->and($types->get('hero')->rendersInLayout)->toBeFalse()
        ->and($types->get('hero')->view)->toBe('shop::sections.hero')
        ->and($types->get('hero')->fields())->toBe([['key' => 'title', 'type' => 'text', 'label' => 'Title']]);
});

it('leaves a type\'s fields unresolved until they are asked for', function () {
    $resolved = 0;

    bindSectionSchema(new class($resolved)
    {
        public function __construct(public int &$resolved) {}

        public function types(?string $themeCode = null): Collection
        {
            $counter = function () {
                $this->resolved++;

                return [];
            };

            return collect(['carousel' => new class($counter)
            {
                public function __construct(protected Closure $counter) {}

                public function getCode(): string
                {
                    return 'carousel';
                }

                public function getTitle(): string
                {
                    return 'Carousel';
                }

                public function getIcon(): string
                {
                    return 'icon-image';
                }

                public function isSingleton(): bool
                {
                    return false;
                }

                public function isPinned(): bool
                {
                    return false;
                }

                public function rendersInLayout(): bool
                {
                    return false;
                }

                public function getFields(): array
                {
                    return ($this->counter)();
                }
            }]);
        }
    });

    $type = sectionSystem(available: true)->types()->get('carousel');

    expect($resolved)->toBe(0);

    $type->fields();
    $type->fields();

    expect($resolved)->toBe(1);
});

it('finds a single type by code and nothing for an unknown one', function () {
    bindSectionSchema(new class
    {
        public function types(?string $themeCode = null): Collection
        {
            return collect(['static_content' => fakeSectionType('static_content', 'Static Content', 'icon-cms')]);
        }
    });

    $system = sectionSystem(available: true);

    expect($system->type(null, 'static_content'))->toBeInstanceOf(BagistoSectionType::class)
        ->and($system->type(null, 'nope'))->toBeNull()
        ->and($system->type(null, null))->toBeNull();
});

it('reads bagisto\'s own section types through the real registry', function () {
    $types = app(SectionSystem::class)->types();

    expect($types->keys()->all())->toBe([
        'image_carousel',
        'product_carousel',
        'category_carousel',
        'footer_links',
        'static_content',
        'services_content',
    ])
        ->and($types->get('footer_links')->singleton)->toBeTrue()
        ->and($types->get('footer_links')->pinned)->toBeTrue()
        ->and($types->get('footer_links')->rendersInLayout)->toBeTrue()
        ->and($types->get('services_content')->rendersInLayout)->toBeTrue()
        ->and($types->get('image_carousel')->rendersInLayout)->toBeFalse();
})->skip(
    fn () => ! app(SectionSystem::class)->available(),
    'bagisto does not declare its section types'
);

it('lets a type name the view it is drawn by', function () {
    bindSectionSchema(new class
    {
        public function types(?string $themeCode = null): Collection
        {
            return collect(['hero' => new class
            {
                public function getCode(): string
                {
                    return 'hero';
                }

                public function getTitle(): string
                {
                    return 'Hero';
                }

                public function getIcon(): string
                {
                    return 'icon-star';
                }

                public function isSingleton(): bool
                {
                    return false;
                }

                public function isPinned(): bool
                {
                    return false;
                }

                public function rendersInLayout(): bool
                {
                    return false;
                }

                public function getView(): string
                {
                    return 'premium::heroes.split';
                }

                public function getFields(): array
                {
                    return [];
                }
            }]);
        }
    });

    expect(sectionSystem(available: true)->types()->get('hero')->view)->toBe('premium::heroes.split');
});

it('names bagisto\'s own sections after the types they are stored as', function () {
    expect(app(SectionSystem::class)->types()->get('image_carousel')->view)
        ->toBe('shop::sections.image_carousel');
})->skip(
    fn () => ! app(SectionSystem::class)->available(),
    'bagisto does not declare its section types'
);
