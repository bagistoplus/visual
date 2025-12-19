<?php

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Persistence\PersistEditorUpdates;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    if (empty(config('app.key'))) {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    Storage::fake('themes-data');
    $this->persistEditorUpdates = app(PersistEditorUpdates::class);
});

describe('collectRegionBlocks', function () {
    test('collects root blocks', function () {
        $allBlocks = [
            'block-1' => ['id' => 'block-1', 'type' => 'text'],
            'block-2' => ['id' => 'block-2', 'type' => 'image'],
        ];

        $rootBlockIds = ['block-1'];

        $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

        expect($result)->toHaveKey('block-1')
            ->and($result)->not->toHaveKey('block-2');
    });

    test('collects nested children', function () {
        $allBlocks = [
            'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2', 'block-3']],
            'block-2' => ['id' => 'block-2', 'type' => 'text'],
            'block-3' => ['id' => 'block-3', 'type' => 'image'],
            'block-4' => ['id' => 'block-4', 'type' => 'button'],
        ];

        $rootBlockIds = ['block-1'];

        $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

        expect($result)->toHaveKeys(['block-1', 'block-2', 'block-3'])
            ->and($result)->not->toHaveKey('block-4');
    });

    test('handles deeply nested blocks', function () {
        $allBlocks = [
            'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2']],
            'block-2' => ['id' => 'block-2', 'type' => 'container', 'children' => ['block-3']],
            'block-3' => ['id' => 'block-3', 'type' => 'container', 'children' => ['block-4']],
            'block-4' => ['id' => 'block-4', 'type' => 'text'],
            'block-5' => ['id' => 'block-5', 'type' => 'image'],
        ];

        $rootBlockIds = ['block-1'];

        $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

        expect($result)->toHaveKeys(['block-1', 'block-2', 'block-3', 'block-4'])
            ->and($result)->not->toHaveKey('block-5');
    });

    test('handles multiple root blocks', function () {
        $allBlocks = [
            'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2']],
            'block-2' => ['id' => 'block-2', 'type' => 'text'],
            'block-3' => ['id' => 'block-3', 'type' => 'container', 'children' => ['block-4']],
            'block-4' => ['id' => 'block-4', 'type' => 'image'],
            'block-5' => ['id' => 'block-5', 'type' => 'button'],
        ];

        $rootBlockIds = ['block-1', 'block-3'];

        $result = $this->persistEditorUpdates->collectRegionBlocks($allBlocks, $rootBlockIds);

        expect($result)->toHaveKeys(['block-1', 'block-2', 'block-3', 'block-4'])
            ->and($result)->not->toHaveKey('block-5');
    });
});

describe('handleFullPage', function () {
    test('saves shared regions separately', function () {
        Storage::fake('local');

        $basePath = 'test-theme/default/en/editor';

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'regions/header.json')
            ->andReturn(Storage::path($basePath.'/regions/header.json'));

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'templates/index.json')
            ->andReturn(Storage::path($basePath.'/templates/index.json'));

        ThemePathsResolver::shouldReceive('getThemeBaseDataPath')
            ->with('test-theme', 'editor/.last-edit')
            ->andReturn(Storage::path($basePath.'/.last-edit'));

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => 'index',
            'page' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Header'],
                    'block-2' => ['id' => 'block-2', 'type' => 'text', 'content' => 'Main'],
                ],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => ['block-1']],
                    ['name' => 'main', 'shared' => false, 'blocks' => ['block-2']],
                ],
            ],
        ];

        $this->persistEditorUpdates->handleFullPage($data);

        expect(Storage::exists($basePath.'/regions/header.json'))->toBeTrue()
            ->and(Storage::exists($basePath.'/templates/index.json'))->toBeTrue();

        $headerData = json_decode(Storage::get($basePath.'/regions/header.json'), true);
        $templateData = json_decode(Storage::get($basePath.'/templates/index.json'), true);

        expect($headerData['blocks'])->toHaveKey('block-1')
            ->and($headerData['blocks'])->not->toHaveKey('block-2')
            ->and($templateData['blocks'])->toHaveKey('block-2')
            ->and($templateData['blocks'])->not->toHaveKey('block-1');
    });

    test('collects nested blocks in shared regions', function () {
        Storage::fake('local');

        $basePath = 'test-theme/default/en/editor';

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'regions/header.json')
            ->andReturn(Storage::path($basePath.'/regions/header.json'));

        ThemePathsResolver::shouldReceive('getThemeBaseDataPath')
            ->with('test-theme', 'editor/.last-edit')
            ->andReturn(Storage::path($basePath.'/.last-edit'));

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => 'index',
            'page' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'container', 'children' => ['block-2']],
                    'block-2' => ['id' => 'block-2', 'type' => 'text', 'content' => 'Nested'],
                    'block-3' => ['id' => 'block-3', 'type' => 'image'],
                ],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => ['block-1']],
                ],
            ],
        ];

        $this->persistEditorUpdates->handleFullPage($data);

        $headerData = json_decode(Storage::get($basePath.'/regions/header.json'), true);

        expect($headerData['blocks'])->toHaveKeys(['block-1', 'block-2'])
            ->and($headerData['blocks'])->not->toHaveKey('block-3');
    });

    test('handles only shared regions', function () {
        Storage::fake('local');

        $basePath = 'test-theme/default/en/editor';

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'regions/header.json')
            ->andReturn(Storage::path($basePath.'/regions/header.json'));

        ThemePathsResolver::shouldReceive('getThemeBaseDataPath')
            ->with('test-theme', 'editor/.last-edit')
            ->andReturn(Storage::path($basePath.'/.last-edit'));

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => 'index',
            'page' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text'],
                ],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => ['block-1']],
                ],
            ],
        ];

        $this->persistEditorUpdates->handleFullPage($data);

        expect(Storage::exists($basePath.'/regions/header.json'))->toBeTrue()
            ->and(Storage::exists($basePath.'/templates/index.json'))->toBeFalse();
    });

    test('handles only non-shared regions', function () {
        Storage::fake('local');

        $basePath = 'test-theme/default/en/editor';

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'templates/index.json')
            ->andReturn(Storage::path($basePath.'/templates/index.json'));

        ThemePathsResolver::shouldReceive('getThemeBaseDataPath')
            ->with('test-theme', 'editor/.last-edit')
            ->andReturn(Storage::path($basePath.'/.last-edit'));

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => 'index',
            'page' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text'],
                ],
                'regions' => [
                    ['name' => 'main', 'shared' => false, 'blocks' => ['block-1']],
                ],
            ],
        ];

        $this->persistEditorUpdates->handleFullPage($data);

        expect(Storage::exists($basePath.'/templates/index.json'))->toBeTrue();

        $templateData = json_decode(Storage::get($basePath.'/templates/index.json'), true);

        expect($templateData['blocks'])->toHaveKey('block-1')
            ->and($templateData['regions'])->toHaveCount(1)
            ->and($templateData['regions'][0]['name'])->toBe('main');
    });

    test('handles multiple shared regions', function () {
        Storage::fake('local');

        $basePath = 'test-theme/default/en/editor';

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'regions/header.json')
            ->andReturn(Storage::path($basePath.'/regions/header.json'));

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'regions/footer.json')
            ->andReturn(Storage::path($basePath.'/regions/footer.json'));

        ThemePathsResolver::shouldReceive('getThemeBaseDataPath')
            ->with('test-theme', 'editor/.last-edit')
            ->andReturn(Storage::path($basePath.'/.last-edit'));

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => 'index',
            'page' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text'],
                    'block-2' => ['id' => 'block-2', 'type' => 'text'],
                ],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => ['block-1']],
                    ['name' => 'footer', 'shared' => true, 'blocks' => ['block-2']],
                ],
            ],
        ];

        $this->persistEditorUpdates->handleFullPage($data);

        expect(Storage::exists($basePath.'/regions/header.json'))->toBeTrue()
            ->and(Storage::exists($basePath.'/regions/footer.json'))->toBeTrue();

        $headerData = json_decode(Storage::get($basePath.'/regions/header.json'), true);
        $footerData = json_decode(Storage::get($basePath.'/regions/footer.json'), true);

        expect($headerData['blocks'])->toHaveKey('block-1')
            ->and($footerData['blocks'])->toHaveKey('block-2');
    });
});

describe('handle', function () {
    test('returns loaded blocks from shared regions', function () {
        $sources = ['header' => 'path/to/header.blade.php'];

        $mock = Mockery::mock(PersistEditorUpdates::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Mock persistSharedRegion to return test data
        $mock->shouldReceive('persistSharedRegion')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Header'],
                    ],
                ],
            ]);

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt($sources),
            ],
            'updates' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Header'],
                ],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => ['block-1']],
                ],
                'changes' => [
                    'updated' => ['block-1'],
                ],
            ],
        ];

        $result = $mock->handle($data);

        expect($result)->toHaveKey('loadedBlocks')
            ->and($result['loadedBlocks'])->toHaveKey('block-1')
            ->and($result['loadedBlocks']['block-1']['content'])->toBe('Header');
    });

    test('returns loaded blocks from template regions', function () {
        $sources = ['main' => 'path/to/main.blade.php'];

        $mock = Mockery::mock(PersistEditorUpdates::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Mock persistTemplateRegions to return test data
        $mock->shouldReceive('persistTemplateRegions')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Main'],
                    ],
                ],
            ]);

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt($sources),
            ],
            'updates' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Main'],
                ],
                'regions' => [
                    ['name' => 'main', 'shared' => false, 'blocks' => ['block-1']],
                ],
                'changes' => [
                    'updated' => ['block-1'],
                ],
            ],
        ];

        $result = $mock->handle($data);

        expect($result)->toHaveKey('loadedBlocks')
            ->and($result['loadedBlocks'])->toHaveKey('block-1')
            ->and($result['loadedBlocks']['block-1']['content'])->toBe('Main');
    });

    test('merges blocks from both shared and template regions', function () {
        $sources = [
            'header' => 'path/to/header.blade.php',
            'main' => 'path/to/main.blade.php',
        ];

        $mock = Mockery::mock(PersistEditorUpdates::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Mock persistSharedRegion for header
        $mock->shouldReceive('persistSharedRegion')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Header'],
                    ],
                ],
            ]);

        // Mock persistTemplateRegions for main
        $mock->shouldReceive('persistTemplateRegions')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'block-2' => ['id' => 'block-2', 'type' => 'text', 'content' => 'Main'],
                    ],
                ],
            ]);

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt($sources),
            ],
            'updates' => [
                'blocks' => [
                    'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Header'],
                    'block-2' => ['id' => 'block-2', 'type' => 'text', 'content' => 'Main'],
                ],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => ['block-1']],
                    ['name' => 'main', 'shared' => false, 'blocks' => ['block-2']],
                ],
                'changes' => [
                    'updated' => ['block-1', 'block-2'],
                ],
            ],
        ];

        $result = $mock->handle($data);

        expect($result['loadedBlocks'])->toHaveKeys(['block-1', 'block-2'])
            ->and($result['loadedBlocks']['block-1']['content'])->toBe('Header')
            ->and($result['loadedBlocks']['block-2']['content'])->toBe('Main');
    });

    test('returns empty blocks when nothing was updated', function () {
        $sources = ['header' => 'path/to/header.blade.php'];

        $mock = Mockery::mock(PersistEditorUpdates::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Mock persistSharedRegion to return no update
        $mock->shouldReceive('persistSharedRegion')
            ->once()
            ->andReturn([
                'updated' => false,
            ]);

        $data = [
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt($sources),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => [
                    ['name' => 'header', 'shared' => true, 'blocks' => []],
                ],
                'changes' => [],
            ],
        ];

        $result = $mock->handle($data);

        expect($result)->toHaveKey('loadedBlocks')
            ->and($result['loadedBlocks'])->toBeEmpty();
    });
});
