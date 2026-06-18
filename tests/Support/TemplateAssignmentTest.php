<?php

use BagistoPlus\Visual\Models\VisualTemplateAssignment;
use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateAssignment;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Database\Eloquent\Model;

class VisualTemplateAssignmentTestModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function getKey()
    {
        return 1;
    }
}

function templateAssignmentTestModel(): VisualTemplateAssignmentTestModel
{
    return new VisualTemplateAssignmentTestModel;
}

function templateAssignmentService(?TemplateDiscovery $discovery = null): TemplateAssignment
{
    return new TemplateAssignment(
        $discovery ?? app(TemplateDiscovery::class),
        app(ChannelThemeResolver::class),
    );
}

beforeEach(function () {
    config()->set('bagisto_visual.template_assignments', true);
});

it('no-ops while template assignments are disabled', function () {
    config()->set('bagisto_visual.template_assignments', false);

    $model = templateAssignmentTestModel();
    $model->setRelation('visualTemplateAssignments', collect([
        new VisualTemplateAssignment([
            'template_type' => 'product',
            'template_key' => 'product.gift-box',
            'channel' => 'default',
            'locale' => 'en',
        ]),
    ]));

    $service = templateAssignmentService();

    expect($service->read($model, 'product', 'default', 'en'))->toBeNull()
        ->and($service->resolve($model, 'product', null, 'default', 'en'))->toBe('product')
        ->and($service->isValid(null, 'product', null, 'default', 'en'))->toBeTrue()
        ->and($service->isValid('product.gift-box', 'product', null, 'default', 'en'))->toBeFalse();
});

it('uses an eager loaded visual template assignment relation when present', function () {
    $model = templateAssignmentTestModel();
    $model->setRelation('visualTemplateAssignments', collect([
        new VisualTemplateAssignment([
            'template_type' => 'product',
            'template_key' => 'product.gift-box',
            'channel' => 'default',
            'locale' => 'en',
        ]),
        new VisualTemplateAssignment([
            'template_type' => 'product',
            'template_key' => 'product.sale',
            'channel' => 'mobile',
            'locale' => 'en',
        ]),
        new VisualTemplateAssignment([
            'template_type' => 'category',
            'template_key' => 'category.gift-box',
            'channel' => null,
            'locale' => 'en',
        ]),
    ]));

    expect(templateAssignmentService()->read($model, 'product', 'default', 'en'))->toBe('product.gift-box')
        ->and(templateAssignmentService()->read($model, 'product', 'mobile', 'en'))->toBe('product.sale')
        ->and(templateAssignmentService()->read($model, 'category', null, 'en'))->toBe('category.gift-box');
});

it('ignores unsupported assignment types without touching storage', function () {
    $model = templateAssignmentTestModel();
    $model->setRelation('visualTemplateAssignments', collect([
        new VisualTemplateAssignment([
            'template_type' => 'blog',
            'template_key' => 'blog.article',
            'channel' => null,
            'locale' => 'en',
        ]),
    ]));

    expect(templateAssignmentService()->read($model, 'blog', null, 'en'))->toBeNull();
});

it('resolves wrong-type assignments back to the default template type', function () {
    $model = templateAssignmentTestModel();
    $model->setRelation('visualTemplateAssignments', collect([
        new VisualTemplateAssignment([
            'template_type' => 'product',
            'template_key' => 'category.gift-box',
            'channel' => 'default',
            'locale' => 'en',
        ]),
    ]));

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect(templateAssignmentService()->resolve($model, 'product', $theme, 'default', 'en'))->toBe('product');
});

it('resolves missing templates back to the default template type', function () {
    $model = templateAssignmentTestModel();
    $model->setRelation('visualTemplateAssignments', collect([
        new VisualTemplateAssignment([
            'template_type' => 'product',
            'template_key' => 'product.missing',
            'channel' => 'default',
            'locale' => 'en',
        ]),
    ]));

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $templates = new class extends TemplateDiscovery
    {
        public function exists(
            Theme|string $theme,
            string $key,
            string $type,
            ?string $channel = null,
            ?string $locale = null,
            bool $includeEditorDrafts = false
        ): bool {
            return false;
        }
    };

    expect(templateAssignmentService($templates)->resolve($model, 'product', $theme, 'default', 'en'))->toBe('product');
});
