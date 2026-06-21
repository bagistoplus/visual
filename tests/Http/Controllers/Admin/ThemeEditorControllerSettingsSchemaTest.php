<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;

it('translates admin theme settings schema ui text', function () {
    app('translator')->addLines([
        'settings.group' => 'General',
        'settings.title' => 'Title',
        'settings.title_info' => 'Shown on the storefront',
        'settings.option' => 'Featured',
    ], 'en');

    $controller = (new ReflectionClass(ThemeEditorController::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(ThemeEditorController::class, 'translateSettingsSchema');
    $method->setAccessible(true);

    $schema = $method->invoke($controller, [
        [
            'name' => 't:settings.group',
            'settings' => [
                [
                    'id' => 'title',
                    'type' => 'select',
                    'label' => 't:settings.title',
                    'info' => 'settings.title_info',
                    'default' => 't:settings.default',
                    'options' => [
                        ['value' => 'featured', 'label' => 't:settings.option'],
                    ],
                ],
            ],
        ],
    ]);

    expect($schema[0]['name'])->toBe('General')
        ->and($schema[0]['settings'][0]['label'])->toBe('Title')
        ->and($schema[0]['settings'][0]['info'])->toBe('Shown on the storefront')
        ->and($schema[0]['settings'][0]['default'])->toBe('t:settings.default')
        ->and($schema[0]['settings'][0]['options'][0])->toBe([
            'value' => 'featured',
            'label' => 'Featured',
        ]);
});
