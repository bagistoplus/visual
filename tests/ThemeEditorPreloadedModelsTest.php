<?php

use BagistoPlus\Visual\ThemeEditor;

it('starts with empty lists for every model type', function () {
    $editor = new ThemeEditor;

    expect($editor->preloadedModels())->toBe([
        'categories' => [],
        'products' => [],
        'cms_pages' => [],
    ]);
});

it('preloads each model once per type', function () {
    $editor = new ThemeEditor;
    $category = (object) ['id' => 1];

    $editor->preloadModel('categories', $category);
    $editor->preloadModel('categories', $category);

    expect($editor->preloadedModels()['categories'])->toBe([$category]);
});

it('returns list-shaped arrays regardless of ids', function () {
    $editor = new ThemeEditor;
    $first = (object) ['id' => 42];
    $second = (object) ['id' => 7];

    $editor->preloadModel('products', $first);
    $editor->preloadModel('products', $second);

    expect($editor->preloadedModels()['products'])->toBe([$first, $second])
        ->and(json_encode($editor->preloadedModels()['products']))->toStartWith('[');
});
