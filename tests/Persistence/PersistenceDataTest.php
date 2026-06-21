<?php

use BagistoPlus\Visual\Persistence\Data\EditorUpdateData;
use BagistoPlus\Visual\Persistence\Data\FullPageEditorData;
use BagistoPlus\Visual\Persistence\Data\ThemeSettingsUpdateData;
use Craftile\Laravel\Data\UpdateRequest;

it('creates editor update data from validated payloads', function () {
    $data = EditorUpdateData::fromValidated([
        'theme' => 'test-theme',
        'channel' => 'default',
        'locale' => 'ar',
        'template' => [
            'url' => 'https://example.test',
            'name' => 'index',
            'sources' => encrypt(['/theme/templates/index.blade.php']),
        ],
        'updates' => [
            'blocks' => [
                'hero' => ['id' => 'hero'],
                'banner' => ['id' => 'banner'],
            ],
            'regions' => [
                ['id' => 'header', 'shared' => true, 'blocks' => ['hero']],
                ['id' => 'main', 'shared' => false, 'blocks' => ['banner']],
            ],
            'changes' => [
                'added' => ['hero'],
                'updated' => ['banner'],
                'removed' => [],
            ],
        ],
    ]);

    expect($data->theme)->toBe('test-theme')
        ->and($data->channel)->toBe('default')
        ->and($data->locale)->toBe('ar')
        ->and($data->templateName)->toBe('index')
        ->and($data->templateUrl)->toBe('https://example.test')
        ->and($data->sources)->toBe(['/theme/templates/index.blade.php'])
        ->and($data->updateRequest)->toBeInstanceOf(UpdateRequest::class)
        ->and($data->sharedRegions()->pluck('id')->all())->toBe(['header'])
        ->and($data->nonSharedRegions()->pluck('id')->all())->toBe(['main'])
        ->and($data->changedBlockIds())->toBe(['hero', 'banner'])
        ->and($data->addedBlockIds())->toBe(['hero']);
});

it('creates full page editor data from validated payloads', function () {
    $data = FullPageEditorData::fromValidated([
        'theme' => 'test-theme',
        'channel' => 'default',
        'locale' => 'ar',
        'template' => 'index',
        'page' => [
            'blocks' => ['hero' => ['id' => 'hero']],
            'regions' => [
                ['id' => 'header', 'shared' => true, 'blocks' => []],
                ['id' => 'main', 'shared' => false, 'blocks' => ['hero']],
            ],
        ],
    ]);

    expect($data->theme)->toBe('test-theme')
        ->and($data->template)->toBe('index')
        ->and($data->blocks)->toHaveKey('hero')
        ->and($data->sharedRegions()->pluck('id')->all())->toBe(['header'])
        ->and($data->nonSharedRegions()->pluck('id')->all())->toBe(['main']);
});

it('creates theme settings update data without template sources', function () {
    $data = ThemeSettingsUpdateData::fromValidated([
        'theme' => 'test-theme',
        'channel' => 'default',
        'locale' => 'ar',
        'template' => [
            'url' => 'https://example.test',
        ],
        'updates' => [
            'colors.primary' => '#ff0000',
        ],
    ]);

    expect($data->templateUrl)->toBe('https://example.test')
        ->and($data->updates)->toBe(['colors.primary' => '#ff0000']);
});
