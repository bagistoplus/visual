<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemesController;

function listedThemeCodes(): array
{
    return app(ThemesController::class)
        ->index()
        ->getData()['themes']
        ->pluck('code')
        ->all();
}

beforeEach(function () {
    config()->set('themes.shop', [
        'default' => ['name' => 'Default'],
        'visual-demo' => ['code' => 'visual-demo', 'name' => 'Visual Demo', 'visual_theme' => true],
    ]);
});

it('lists a plain theme alongside visual ones when bagisto stores sections', function () {
    fakeSectionSystem(available: true);

    expect(listedThemeCodes())->toBe(['default', 'visual-demo']);
});

it('leaves plain themes out when bagisto has no section system', function () {
    fakeSectionSystem(available: false);

    expect(listedThemeCodes())->toBe(['visual-demo']);
});
