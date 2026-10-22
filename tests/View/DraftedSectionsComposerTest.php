<?php

use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\View\Composers\DraftedSectionsComposer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Webkul\Theme\Facades\Themes as ThemesFacade;

function draftingStore(): SectionStore
{
    return new class extends SectionStore
    {
        public array $askedFor = [];

        public function __construct() {}

        public function draftedForPreview(int $channelId, string $themeCode, string $locale): iterable
        {
            $this->askedFor = [$channelId, $themeCode, $locale];

            return ['drafted'];
        }
    };
}

function composerThemeIs(Theme $theme): void
{
    ThemesFacade::swap(new class($theme)
    {
        public function __construct(protected Theme $theme) {}

        public function current(): Theme
        {
            return $this->theme;
        }
    });
}

it('draws the sections the editor is holding while it drives the storefront', function () {
    fakeSectionSystem(available: true);
    composerThemeIs(Theme::make(['code' => 'default', 'name' => 'Default']));
    app()->instance('request', Request::create('/?_designMode=default'));

    $store = draftingStore();
    $view = Mockery::mock(View::class);
    $view->shouldReceive('with')->once()->with('sections', ['drafted']);

    (new DraftedSectionsComposer($store))->compose($view);

    expect($store->askedFor)->toBe([1, 'default', 'en']);
});

it('leaves the storefront its published sections', function () {
    fakeSectionSystem(available: true);
    composerThemeIs(Theme::make(['code' => 'default', 'name' => 'Default']));
    app()->instance('request', Request::create('/'));

    $view = Mockery::mock(View::class);
    $view->shouldNotReceive('with');

    $store = draftingStore();

    (new DraftedSectionsComposer($store))->compose($view);

    expect($store->askedFor)->toBeEmpty();
});

it('leaves a visual theme to render its own blocks', function () {
    fakeSectionSystem(available: true);
    composerThemeIs(Theme::make(['code' => 'debut', 'name' => 'Debut', 'visual_theme' => true]));
    app()->instance('request', Request::create('/?_designMode=debut'));

    $view = Mockery::mock(View::class);
    $view->shouldNotReceive('with');

    $store = draftingStore();

    (new DraftedSectionsComposer($store))->compose($view);

    expect($store->askedFor)->toBeEmpty();
});
