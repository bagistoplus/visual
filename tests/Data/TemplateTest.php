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
