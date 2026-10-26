<?php

use BagistoPlus\Visual\Middlewares\PreviewDraftedSections;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Http\Request;
use Webkul\Theme\Facades\Themes as ThemesFacade;

function bindThemeForDraftPreview(mixed $theme): void
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

function previewingAfter(mixed $theme, string $url): bool
{
    fakeSectionSystem(available: true);
    bindThemeForDraftPreview($theme);

    $request = Request::create($url);
    app()->instance('request', $request);

    $response = (new PreviewDraftedSections(app(SectionStore::class)))->handle($request, fn () => 'next');

    expect($response)->toBe('next');

    return (bool) $request->attributes->get('appearance_previewing', false);
}

function bagistoTheme(): Theme
{
    return Theme::make(['code' => 'default', 'name' => 'Default']);
}

it('reads the drafted sections while the editor is driving', function () {
    expect(previewingAfter(bagistoTheme(), '/?_designMode=default'))->toBeTrue();
});

it('leaves a storefront request to the published sections', function () {
    expect(previewingAfter(bagistoTheme(), '/'))->toBeFalse();
});

it('leaves a visual theme alone, whose drafts are held elsewhere', function () {
    $theme = Theme::make(['code' => 'demo', 'name' => 'Demo', 'visual_theme' => true]);

    expect(previewingAfter($theme, '/?_designMode=demo'))->toBeFalse();
});

it('leaves a theme it knows nothing about alone', function () {
    expect(previewingAfter(null, '/?_designMode=default'))->toBeFalse();
});

it('reads the drafted sections in a draft preview', function () {
    expect(previewingAfter(bagistoTheme(), '/?_draftPreview=default'))->toBeTrue();
});

it('leaves a draft preview link to the published sections for a visitor', function () {
    auth()->guard('admin')->forgetUser();

    expect(previewingAfter(bagistoTheme(), '/?_draftPreview=default'))->toBeFalse();
});
