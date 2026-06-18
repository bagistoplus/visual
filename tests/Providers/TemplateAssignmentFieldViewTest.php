<?php

use BagistoPlus\Visual\Actions\Admin\AddTemplateAssignmentField;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateAssignment;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Webkul\Core\Models\Channel;
use Webkul\Theme\ViewRenderEventManager;

class TemplateAssignmentFieldViewTestModel extends Model
{
    protected $guarded = [];

    public function getKey()
    {
        return 1;
    }
}

class TemplateAssignmentFieldViewTestManager extends ViewRenderEventManager
{
    public $templates = [];

    public function getParam($name)
    {
        return new TemplateAssignmentFieldViewTestModel;
    }

    public function addTemplate($template, $params = []): void
    {
        $this->templates[] = [$template, $params];
    }
}

beforeEach(function () {
    config()->set('bagisto_visual.template_assignments', true);

    view()->addLocation(__DIR__.'/../views/components');

    Blade::component('test-admin-accordion', 'admin::accordion');
    Blade::component('test-admin-form-control-group', 'admin::form.control-group');
    Blade::component('test-admin-form-control-group-label', 'admin::form.control-group.label');
    Blade::component('test-admin-form-control-group-control', 'admin::form.control-group.control');
    Blade::component('test-admin-form-control-group-error', 'admin::form.control-group.error');

    app()->instance(ChannelThemeResolver::class, new class extends ChannelThemeResolver
    {
        public function resolve(Channel|string|null $channel = null): ?Theme
        {
            return null;
        }

        public function resolveDefault(): ?Theme
        {
            return null;
        }
    });

    app()->instance(TemplateDiscovery::class, new class extends TemplateDiscovery
    {
        public function forType(
            Theme|string $theme,
            string $type,
            ?string $channel = null,
            ?string $locale = null,
            bool $includeEditorDrafts = false
        ): Collection {
            return collect();
        }
    });

    app()->instance(TemplateAssignment::class, new class extends TemplateAssignment
    {
        public function __construct() {}

        public function read(Model $model, string $type, ?string $channel = null, ?string $locale = null): ?string
        {
            return null;
        }
    });
});

function templateAssignmentFieldVisualTheme(): Theme
{
    return Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);
}

function bindTemplateAssignmentFieldThemeResolver(?Theme $productTheme = null, ?Theme $defaultTheme = null): void
{
    app()->instance(ChannelThemeResolver::class, new class($productTheme, $defaultTheme) extends ChannelThemeResolver
    {
        public function __construct(protected ?Theme $productTheme, protected ?Theme $defaultTheme) {}

        public function resolve(Channel|string|null $channel = null): ?Theme
        {
            return $this->productTheme;
        }

        public function resolveDefault(): ?Theme
        {
            return $this->defaultTheme;
        }
    });
}

it('does not prepare assignment field data while template assignments are disabled', function () {
    config()->set('bagisto_visual.template_assignments', false);

    $data = app(AddTemplateAssignmentField::class)->data('category', new TemplateAssignmentFieldViewTestModel);

    expect($data['enabled'])->toBeFalse()
        ->and($data['type'])->toBe('category')
        ->and($data['accordion'])->toBeTrue();
});

it('renders the product template assignment field without an accordion wrapper', function () {
    $html = view('visual::admin.template-assignment.field', [
        'enabled' => true,
        'type' => 'product',
        'model' => new TemplateAssignmentFieldViewTestModel,
        'accordion' => false,
        'theme' => templateAssignmentFieldVisualTheme(),
        'templates' => collect(),
        'selected' => null,
        'defaultLabel' => 'Default Product',
    ])->render();

    expect($html)->toContain('name="visual_template"')
        ->and($html)->not->toContain('data-test-accordion');
});

it('renders category and cms template assignment fields inside an accordion wrapper', function (string $type) {
    $html = view('visual::admin.template-assignment.field', [
        'enabled' => true,
        'type' => $type,
        'model' => new TemplateAssignmentFieldViewTestModel,
        'accordion' => true,
        'theme' => templateAssignmentFieldVisualTheme(),
        'templates' => collect(),
        'selected' => null,
        'defaultLabel' => "Default {$type}",
    ])->render();

    expect($html)->toContain('data-test-accordion')
        ->and($html)->toContain('Theme template')
        ->and($html)->toContain('name="visual_template"');
})->with([
    'category',
    'page',
]);

it('renders nothing when the prepared field is disabled', function () {
    expect(view('visual::admin.template-assignment.field', [
        'enabled' => false,
    ])->render())->toBe('');
});

it('pre-renders the assignment field before adding it to the view render manager', function () {
    bindTemplateAssignmentFieldThemeResolver(defaultTheme: templateAssignmentFieldVisualTheme());

    $manager = new TemplateAssignmentFieldViewTestManager;

    app(AddTemplateAssignmentField::class)($manager, 'category');

    expect($manager->templates)->toHaveCount(1)
        ->and($manager->templates[0][0])->toContain('data-test-accordion')
        ->and($manager->templates[0][0])->toContain('name="visual_template"')
        ->and($manager->templates[0][1])->toBe([]);
});

it('does not add the product assignment field when the selected channel theme is not visual', function () {
    $manager = new TemplateAssignmentFieldViewTestManager;

    app(AddTemplateAssignmentField::class)($manager, 'product');

    expect($manager->templates)->toBe([]);
});

it('does not add category or cms page assignment fields when the default theme is not visual', function (string $type) {
    $manager = new TemplateAssignmentFieldViewTestManager;

    app(AddTemplateAssignmentField::class)($manager, $type);

    expect($manager->templates)->toBe([]);
})->with([
    'category',
    'page',
]);

it('adds the product assignment field when the selected channel theme is visual', function () {
    bindTemplateAssignmentFieldThemeResolver(productTheme: templateAssignmentFieldVisualTheme());

    $manager = new TemplateAssignmentFieldViewTestManager;

    app(AddTemplateAssignmentField::class)($manager, 'product');

    expect($manager->templates)->toHaveCount(1)
        ->and($manager->templates[0][0])->toContain('name="visual_template"')
        ->and($manager->templates[0][0])->not->toContain('data-test-accordion');
});

it('adds category and cms page assignment fields when the default theme is visual', function (string $type) {
    bindTemplateAssignmentFieldThemeResolver(defaultTheme: templateAssignmentFieldVisualTheme());

    $manager = new TemplateAssignmentFieldViewTestManager;

    app(AddTemplateAssignmentField::class)($manager, $type);

    expect($manager->templates)->toHaveCount(1)
        ->and($manager->templates[0][0])->toContain('name="visual_template"')
        ->and($manager->templates[0][0])->toContain('data-test-accordion');
})->with([
    'category',
    'page',
]);
