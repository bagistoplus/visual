<?php

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\BladeSection;
use BagistoPlus\Visual\Sections\LivewireSection;
use BagistoPlus\Visual\Sections\Section;
use BagistoPlus\Visual\Sections\Support\SectionData;
use Livewire\Livewire;

it('can be instantiated with default values', function () {
    $section = new Section('test-slug', 'Test Name');

    expect($section->slug)->toBe('test-slug');
    expect($section->name)->toBe('Test Name');
    expect($section->wrapper)->toBe('div');
    expect($section->settings)->toBeArray()->toBeEmpty();
    expect($section->blocks)->toBeArray()->toBeEmpty();
    expect($section->maxBlocks)->toBe(16);
    expect($section->description)->toBeEmpty();
    expect($section->previewImageUrl)->toBeEmpty();
    expect($section->previewDescription)->toBeEmpty();
    expect($section->default)->toBeArray()->toBeEmpty();
    expect($section->isLivewire)->toBeFalse();
});

it('creates a section from component', function () {
    $section = Section::createFromComponent(TestSection::class);

    expect($section->slug)->toBe('test-section');
    expect($section->name)->toBe('Test Section');
    expect($section->wrapper)->toBe('div');
    expect($section->settings)->toBeArray()->toHaveCount(1);
    expect($section->maxBlocks)->toBe(5);
    expect($section->description)->toBe('Test section description');
    expect($section->settings[0])
        ->toBeArray()
        ->toHaveKey('id', 'setting1')
        ->toHaveKey('type', 'text');
})->todo();

it('returns an array representation of the section', function () {
    $section = new Section(
        slug: 'test-section',
        name: 'Test Section',
        wrapper: 'div',
        settings: ['setting1' => 'value'],
        blocks: [],
        maxBlocks: 10,
        description: 'Test Description',
        previewImageUrl: 'image.jpg',
        previewDescription: 'Preview Description',
        default: ['default_key' => 'default_value'],
        enabledOn: ['*'],
        disabledOn: [],
        isLivewire: true
    );

    $array = $section->toArray();

    expect($array)
        ->toHaveKey('slug', 'test-section')
        ->toHaveKey('name', 'Test Section')
        ->toHaveKey('wrapper', 'div')
        ->toHaveKey('settings.setting1', 'value')
        ->toHaveKey('maxBlocks', 10)
        ->toHaveKey('description', 'Test Description')
        ->toHaveKey('previewImageUrl', 'image.jpg')
        ->toHaveKey('previewDescription', 'Preview Description')
        ->toHaveKey('default.default_key', 'default_value')
        ->toHaveKey('isLivewire', true)
        ->toHaveKey('enabledOn', ['*'])
        ->toHaveKey('disabledOn', []);
});

it('serializes to JSON', function () {
    $section = new Section('test-section', 'Test Section', 'div');

    $json = json_encode($section);

    expect($json)->toContain('"slug":"test-section"');
    expect($json)->toContain('"name":"Test Section"');
    expect($json)->toContain('"wrapper":"div"');
});

it('renders a Section to blade template', function () {
    $section = new Section('test-section', 'Test Section');

    $id = '123456';
    $rendered = $section->renderToBlade($id);

    expect($rendered)
        // ->toContain("<div data-section-type=\"test-section\" data-section-id=\"$id\"")
        ->toContain("<x-visual-section-test-section visualId=\"$id\"");
});

it('renders a Livewire section using its component class name', function () {
    $section = Section::createFromComponent(TestLivewireSection::class);

    $id = '123456';
    $rendered = $section->renderToBlade($id);

    expect($rendered)->toContain('@livewire(\\'.TestLivewireSection::class.'::class');
    expect($rendered)->toContain("'visualId' => '$id'");
    expect($rendered)->toContain("'context' => collect(get_defined_vars()['__data']");
    expect($rendered)->not->toContain("'viewData' =>");
    expect($rendered)->not->toContain("@livewire('visual-section-");
});

it('passes page context into Livewire section context', function () {
    config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

    $section = Section::createFromComponent(ContextAwareLivewireSection::class);

    Visual::themeDataCollector()->setSectionData(
        'section-id',
        SectionData::make('section-id', [], $section, __FILE__)
    );

    $testable = Livewire::test(ContextAwareLivewireSection::class, [
        'visualId' => 'section-id',
        'context' => [
            'pageTitle' => 'Landing page',
            'theme' => 'excluded',
            'cart' => 'excluded',
            'errors' => 'excluded',
        ],
    ]);

    $component = $testable->instance();

    if (! $component instanceof ContextAwareLivewireSection) {
        throw new RuntimeException('Unexpected Livewire test component instance.');
    }

    expect($component->getContext())
        ->toHaveKey('pageTitle', 'Landing page')
        ->toHaveKey('section')
        ->toHaveKey('comparableAttributes');

    expect($component->getContext())->not->toHaveKeys(['theme', 'cart', 'errors']);
});

class TestSection extends BladeSection
{
    public static function getSchema(): array
    {
        return [
            'name' => 'Test Section',
            'wrapper' => 'div',
            'settings' => [
                [
                    'id' => 'setting1',
                    'type' => 'text',
                ],
            ],
            'blocks' => [],
            'maxBlocks' => 5,
            'description' => 'Test section description',
        ];
    }

    public function render()
    {
        return 'Test Section';
    }
}

class TestLivewireSection extends LivewireSection
{
    protected static string $slug = 'test-livewire-section';
}

class ContextAwareLivewireSection extends LivewireSection
{
    protected static string $slug = 'context-aware-livewire-section';

    public function render()
    {
        return <<<'HTML'
            <div>Context aware section</div>
        HTML;
    }
}
