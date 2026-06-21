<?php

use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\TemplateAssignment;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Database\Eloquent\Model;
use Webkul\Core\Models\Channel;
use Webkul\Theme\Facades\Themes as ThemesFacade;

class VisualTemplateHelperTestModel extends Model
{
    protected $guarded = [];
}

function visualTemplateHelperTheme(): Theme
{
    return Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);
}

function bindCurrentTheme(mixed $theme): void
{
    ThemesFacade::swap(new class($theme)
    {
        public function __construct(protected mixed $theme) {}

        public function current(): mixed
        {
            return $this->theme;
        }
    });
}

beforeEach(function () {
    view()->addNamespace('shop', __DIR__.'/../views/shop');
});

it('returns the default template type when the current theme is not visual', function () {
    bindCurrentTheme(null);

    app()->instance(ChannelThemeResolver::class, new class extends ChannelThemeResolver
    {
        public function resolve(Channel|string|null $channel = null): ?Theme
        {
            throw new RuntimeException('ChannelThemeResolver should not be used by visual_template_for().');
        }
    });

    expect(visual_template_for('product', new VisualTemplateHelperTestModel))->toBe('product');
});

it('uses a valid requested template in design mode with the current visual theme', function () {
    $theme = visualTemplateHelperTheme();
    bindCurrentTheme($theme);
    request()->query->set('_designMode', 'fake-theme');
    request()->query->set('_template', 'product.gift-box');

    app()->instance(TemplateDiscovery::class, new class($theme) extends TemplateDiscovery
    {
        public function __construct(protected Theme $expectedTheme) {}

        public function typeForKey(string $key): ?string
        {
            return $key === 'product.gift-box' ? 'product' : null;
        }

        public function exists(
            Theme|string $theme,
            string $key,
            string $type,
            ?string $channel = null,
            ?string $locale = null,
            bool $includeEditorDrafts = false
        ): bool {
            expect($theme)->toBe($this->expectedTheme)
                ->and($key)->toBe('product.gift-box')
                ->and($type)->toBe('product')
                ->and($channel)->toBe('default')
                ->and($locale)->toBe('en')
                ->and($includeEditorDrafts)->toBeTrue();

            return true;
        }
    });

    expect(visual_template_for('product'))->toBe('product.gift-box');
});

it('passes the current visual theme into template assignment resolution', function () {
    config()->set('bagisto_visual.template_assignments', true);

    $theme = visualTemplateHelperTheme();
    bindCurrentTheme($theme);

    app()->instance(ChannelThemeResolver::class, new class extends ChannelThemeResolver
    {
        public function resolve(Channel|string|null $channel = null): ?Theme
        {
            throw new RuntimeException('ChannelThemeResolver should not be used by visual_template_for().');
        }
    });

    app()->instance(TemplateAssignment::class, new class($theme) extends TemplateAssignment
    {
        public function __construct(protected Theme $expectedTheme) {}

        public function resolve(Model $model, string $type, ?Theme $theme = null, ?string $channel = null, ?string $locale = null, bool $includeEditorDrafts = false): string
        {
            expect($theme)->toBe($this->expectedTheme)
                ->and($type)->toBe('product')
                ->and($channel)->toBe('default')
                ->and($locale)->toBe('en')
                ->and($includeEditorDrafts)->toBeFalse();

            return 'product.assigned';
        }
    });

    expect(visual_template_for('product', new VisualTemplateHelperTestModel))->toBe('product.assigned');
});

it('returns keys that normal shop template includes can render', function () {
    config()->set('bagisto_visual.template_assignments', true);

    $theme = visualTemplateHelperTheme();
    bindCurrentTheme($theme);

    app()->instance(TemplateAssignment::class, new class extends TemplateAssignment
    {
        public function __construct() {}

        public function resolve(Model $model, string $type, ?Theme $theme = null, ?string $channel = null, ?string $locale = null, bool $includeEditorDrafts = false): string
        {
            return 'product.gift-box';
        }
    });

    $key = visual_template_for('product', new VisualTemplateHelperTestModel);

    expect($key)->toBe('product.gift-box')
        ->and(view("shop::templates.{$key}")->render())->toContain('custom gift box product template');
});

it('does not resolve persisted assignments while template assignments are disabled', function () {
    $theme = visualTemplateHelperTheme();
    bindCurrentTheme($theme);

    app()->instance(TemplateAssignment::class, new class extends TemplateAssignment
    {
        public function __construct() {}

        public function resolve(Model $model, string $type, ?Theme $theme = null, ?string $channel = null, ?string $locale = null, bool $includeEditorDrafts = false): string
        {
            throw new RuntimeException('TemplateAssignment should not be used while disabled.');
        }
    });

    expect(visual_template_for('product', new VisualTemplateHelperTestModel))->toBe('product');
});
