<?php

use BagistoPlus\Visual\Data\Template;

it('serializes template variant support metadata', function () {
    $template = new Template(
        template: 'product',
        route: 'shop.products.index',
        label: 'Product',
        icon: 'lucide-tag',
        previewUrl: 'https://example.test/product',
        type: 'product',
        supportsVariants: true,
    );

    expect($template->toArray())
        ->toHaveKey('supportsVariants', true)
        ->not->toHaveKey('assignable');
});

it('serializes lazy preview urls as strings', function () {
    $resolved = false;

    $template = new Template(
        template: 'category',
        route: 'shop.categories.index',
        label: 'Category',
        icon: 'lucide-tags',
        previewUrl: function () use (&$resolved) {
            $resolved = true;

            return 'https://example.test/category';
        },
        type: 'category',
        supportsVariants: true,
    );

    expect($resolved)->toBeFalse()
        ->and($template->toArray())->toHaveKey('previewUrl', 'https://example.test/category')
        ->and($resolved)->toBeTrue();
});

it('resolves lazy preview url fallbacks', function () {
    $template = new Template(
        template: 'page',
        route: 'shop.cms.page',
        label: 'Page',
        icon: 'lucide-file',
        previewUrl: fn () => 'https://example.test/page',
    );

    expect($template->resolvePreviewUrl())->toBe('https://example.test/page');
});

it('hydrates template variant support metadata from arrays', function () {
    $template = Template::fromArray([
        'template' => 'page',
        'route' => 'shop.cms.page',
        'label' => 'Page',
        'icon' => 'lucide-file',
        'previewUrl' => 'https://example.test/page',
        'type' => 'page',
        'supportsVariants' => true,
    ]);

    expect($template->supportsVariants)->toBeTrue();
});
