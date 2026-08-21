<?php

use BagistoPlus\Visual\Actions\Admin\EnrichVisualThemesInThemeGallery;
use Illuminate\View\View;

afterEach(function () {
    Mockery::close();
});

function enrichThemeGalleryRows(array $rows): array
{
    $enriched = [];

    $view = Mockery::mock(View::class);
    $view->shouldReceive('getData')->andReturn(['themes' => $rows]);
    $view->shouldReceive('with')
        ->once()
        ->with('themes', Mockery::on(function ($themes) use (&$enriched) {
            $enriched = collect($themes)->all();

            return true;
        }));

    app(EnrichVisualThemesInThemeGallery::class)($view);

    return $enriched;
}

function galleryRow(string $code): array
{
    return [
        'code' => $code,
        'name' => ucfirst($code),
        'author' => null,
        'version' => null,
        'description' => null,
        'screenshot' => null,
        'is_installed' => true,
    ];
}

it('fills missing metadata of a visual theme from its config', function () {
    config()->set('themes.shop.visual-demo', [
        'code' => 'visual-demo',
        'name' => 'Visual Demo',
        'version' => '2.1.0',
        'author' => 'Bagisto Plus',
        'description' => 'A demo visual theme',
        'preview_image' => 'themes/shop/visual-demo/images/theme-preview.png',
        'visual_theme' => true,
    ]);

    $enriched = enrichThemeGalleryRows([galleryRow('visual-demo')]);

    expect($enriched[0])
        ->author->toBe('Bagisto Plus')
        ->version->toBe('2.1.0')
        ->description->toBe('A demo visual theme')
        ->screenshot->toBe(asset('themes/shop/visual-demo/images/theme-preview.png'));
});

it('enriches the bagisto theme gallery when the view renders', function () {
    view()->addNamespace('admin', __DIR__.'/../views/admin');

    config()->set('themes.shop.visual-demo', [
        'code' => 'visual-demo',
        'name' => 'Visual Demo',
        'author' => 'Bagisto Plus',
        'visual_theme' => true,
    ]);

    $rendered = view('admin::appearance.themes.index', [
        'themes' => collect([galleryRow('visual-demo')]),
    ])->render();

    expect(json_decode($rendered, true))
        ->toHaveCount(1)
        ->and(json_decode($rendered, true)[0]['author'])
        ->toBe('Bagisto Plus');
});
