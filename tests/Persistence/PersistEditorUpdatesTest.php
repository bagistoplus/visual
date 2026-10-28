<?php

use BagistoPlus\Visual\Blocks\SimpleBlock;
use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Persistence\Data\EditorUpdateData;
use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\Persistence\PersistEditorUpdates;
use BagistoPlus\Visual\Persistence\StoredBlockPruner;
use BagistoPlus\Visual\Persistence\TemplateDataDiffer;
use BagistoPlus\Visual\Settings\Text;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\ThemePathsResolver as ThemePathsResolverConcrete;
use Craftile\Laravel\BlockSchemaRegistry;
use Craftile\Laravel\Data\UpdateRequest;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\Support\HandleUpdates;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Models\Channel;

class PersistEditorUpdatesLocalizedBlock extends SimpleBlock
{
    protected static string $type = 'persist-editor-localized-block';

    public static function settings(): array
    {
        return [
            Text::make('title', 'Title'),
            Text::make('handle', 'Handle')->localized(false),
        ];
    }
}

class PersistEditorUpdatesDefaultsBlock extends SimpleBlock
{
    protected static string $type = 'persist-editor-defaults-block';

    public static function settings(): array
    {
        return [
            Text::make('align', 'Align')->localized(false)->default('center'),
            Text::make('title', 'Title')->default('Hello'),
        ];
    }
}

function persistEditorUpdatesWith(HandleUpdates $handleUpdates): PersistEditorUpdates
{
    app()->instance(HandleUpdates::class, $handleUpdates);

    return partialPersistEditorUpdates();
}

function partialPersistEditorUpdates(): PersistEditorUpdates
{
    $mock = Mockery::mock(PersistEditorUpdates::class, [
        app(HandleUpdates::class),
        app(EditorDataStore::class),
        app(TemplateDataDiffer::class),
        app(TemplateDiscovery::class),
        app(StoredBlockPruner::class),
    ])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $mock->shouldReceive('resolveChannel')
        ->byDefault()
        ->andReturnUsing(function (string $code) {
            $channel = new Channel;
            $channel->id = 1;
            $channel->code = $code;

            return $channel;
        });

    return $mock;
}

function editorUpdateData(array $data): EditorUpdateData
{
    $data['template']['url'] ??= 'https://example.test';

    return EditorUpdateData::fromValidated($data);
}

beforeEach(function () {
    if (empty(config('app.key'))) {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    Storage::fake('themes-data');
    config()->set('bagisto_visual.storages.file.path', Storage::disk('themes-data')->path(''));

    ThemePathsResolver::shouldReceive('getThemeBaseDataPath')
        ->byDefault()
        ->andReturnUsing(fn (string $theme, string $mode = 'live') => rtrim(config('bagisto_visual.storages.file.path'), '/\\')."/themes/{$theme}/{$mode}");

    ThemePathsResolver::shouldReceive('resolvePath')
        ->byDefault()
        ->andReturnUsing(fn (string $themeCode, string $channel, string $locale, string $mode, string $path = '') => implode('/', array_filter([
            rtrim(config('bagisto_visual.storages.file.path'), '/\\')."/themes/{$themeCode}/{$mode}/{$channel}/{$locale}",
            ltrim($path, '/'),
        ])));

    ThemePathsResolver::shouldReceive('resolveFallbackPaths')
        ->byDefault()
        ->andReturnUsing(fn (string $themeCode, string $mode, string $channel, string $locale) => [
            rtrim(config('bagisto_visual.storages.file.path'), '/\\')."/themes/{$themeCode}/{$mode}/{$channel}/{$locale}",
        ]);

    Craftile::detectPreviewUsing(fn () => ThemeEditor::inDesignMode());
    $this->persistEditorUpdates = partialPersistEditorUpdates();
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

describe('handle', function () {
    test('persists a complete template update without restoring a removed block tree', function () {
        $basePath = 'themes/test-theme/editor/default/en';
        $templatePath = $basePath.'/templates/index.json';
        $absoluteTemplatePath = Storage::disk('themes-data')->path($templatePath);

        Storage::disk('themes-data')->put($templatePath, json_encode([
            'blocks' => [
                'kept' => ['id' => 'kept', 'type' => 'text'],
                'removed' => ['id' => 'removed', 'type' => 'group', 'children' => ['removed-child']],
                'removed-child' => ['id' => 'removed-child', 'type' => 'text', 'parentId' => 'removed'],
            ],
            'regions' => [['name' => 'main', 'shared' => false, 'blocks' => ['kept', 'removed']]],
        ]));

        $current = [
            'blocks' => [
                'kept' => ['id' => 'kept', 'type' => 'text'],
            ],
            'regions' => [['name' => 'main', 'shared' => false, 'blocks' => ['kept']]],
        ];

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn (string $path) => str_replace('\\', '/', $path) === str_replace('\\', '/', $absoluteTemplatePath)),
                Mockery::type(UpdateRequest::class),
                ['main']
            )
            ->andReturn(['updated' => true, 'data' => $current]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => $current['regions'],
                'changes' => [
                    'added' => [],
                    'updated' => [],
                    'removed' => ['removed'],
                ],
            ],
        ]));

        $saved = json_decode(Storage::disk('themes-data')->get($templatePath), true);

        expect($saved)->toBe($current);
    });

    test('persists a complete shared region update without restoring removed blocks', function () {
        $basePath = 'themes/test-theme/editor/default/en';
        $regionPath = $basePath.'/regions/header.json';
        $absoluteRegionPath = Storage::disk('themes-data')->path($regionPath);

        Storage::disk('themes-data')->put($regionPath, json_encode([
            'blocks' => [
                'kept' => ['id' => 'kept', 'type' => 'text'],
                'removed' => ['id' => 'removed', 'type' => 'text'],
            ],
            'regions' => [['name' => 'header', 'shared' => true, 'blocks' => ['kept', 'removed']]],
        ]));

        $current = [
            'blocks' => [
                'kept' => ['id' => 'kept', 'type' => 'text'],
            ],
            'regions' => [['name' => 'header', 'shared' => true, 'blocks' => ['kept']]],
        ];

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn (string $path) => str_replace('\\', '/', $path) === str_replace('\\', '/', $absoluteRegionPath)),
                Mockery::type(UpdateRequest::class),
                ['header']
            )
            ->andReturn(['updated' => true, 'data' => $current]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => $current['regions'],
                'changes' => [
                    'added' => [],
                    'updated' => [],
                    'removed' => ['removed'],
                ],
            ],
        ]));

        $saved = json_decode(Storage::disk('themes-data')->get($regionPath), true);

        expect($saved)->toBe($current);
    });

    test('saves shared and non-shared regions in separate files and keeps stored static blocks the update no longer reaches', function () {
        $basePath = 'themes/test-theme/editor/default/en';
        $templatePath = $basePath.'/templates/index.json';
        $regionPath = $basePath.'/regions/header.json';

        Storage::disk('themes-data')->put($templatePath, json_encode([
            'blocks' => [
                'section' => ['id' => 'section', 'type' => 'group', 'children' => ['zone', 'card']],
                'zone' => ['id' => 'zone', 'type' => 'group', 'static' => true, 'parentId' => 'section', 'children' => ['zone-child']],
                'zone-child' => ['id' => 'zone-child', 'type' => 'text', 'parentId' => 'zone'],
                'card' => ['id' => 'card', 'type' => 'text', 'static' => true, 'repeated' => true, 'parentId' => 'section'],
                'removed-main' => ['id' => 'removed-main', 'type' => 'text'],
            ],
            'regions' => [['name' => 'main', 'shared' => false, 'blocks' => ['section', 'removed-main']]],
        ]));
        Storage::disk('themes-data')->put($regionPath, json_encode([
            'blocks' => [
                'header' => ['id' => 'header', 'type' => 'group', 'children' => ['header-zone']],
                'header-zone' => ['id' => 'header-zone', 'type' => 'text', 'static' => true, 'parentId' => 'header'],
            ],
            'regions' => [['name' => 'header', 'shared' => true, 'blocks' => ['header']]],
        ]));

        $currentRegion = [
            'blocks' => [
                'header' => ['id' => 'header', 'type' => 'group', 'children' => []],
            ],
            'regions' => [['name' => 'header', 'shared' => true, 'blocks' => ['header']]],
        ];
        $currentTemplate = [
            'blocks' => [
                'section' => ['id' => 'section', 'type' => 'group', 'children' => ['card']],
                'card' => ['id' => 'card', 'type' => 'text', 'static' => true, 'repeated' => true, 'parentId' => 'section'],
            ],
            'regions' => [['name' => 'main', 'shared' => false, 'blocks' => ['section']]],
        ];

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->twice()
            ->andReturnUsing(fn (?string $path, UpdateRequest $updateRequest, array $regionKeys) => [
                'updated' => true,
                'data' => $regionKeys === ['header'] ? $currentRegion : $currentTemplate,
            ]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => [...$currentRegion['regions'], ...$currentTemplate['regions']],
                'changes' => [
                    'added' => [],
                    'updated' => [],
                    'removed' => ['removed-main'],
                ],
            ],
        ]));

        $template = json_decode(Storage::disk('themes-data')->get($templatePath), true);
        $region = json_decode(Storage::disk('themes-data')->get($regionPath), true);

        expect($template['blocks'])
            ->toHaveKeys(['section', 'card', 'zone', 'zone-child'])
            ->not->toHaveKeys(['removed-main', 'header'])
            ->and($template['blocks']['section']['children'])->toBe(['card'])
            ->and($template['blocks']['zone']['children'])->toBe(['zone-child'])
            ->and($region['blocks'])->toHaveKeys(['header', 'header-zone'])
            ->and($region['blocks'])->not->toHaveKey('section')
            ->and($region['blocks']['header']['children'])->toBe([]);
    });

    test('saves a shared region in a file named after its id', function () {
        $current = [
            'blocks' => [
                'block-1' => ['id' => 'block-1', 'type' => 'text'],
            ],
            'regions' => [['id' => 'site-header', 'name' => 'Header', 'shared' => true, 'blocks' => ['block-1']]],
        ];

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with('/theme/views/regions/site-header.blade.php', Mockery::type(UpdateRequest::class), ['site-header'])
            ->andReturn(['updated' => true, 'data' => $current]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt(['/theme/views/regions/site-header.blade.php']),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => $current['regions'],
                'changes' => [
                    'added' => ['block-1'],
                    'updated' => [],
                    'removed' => [],
                ],
            ],
        ]));

        $saved = json_decode(Storage::disk('themes-data')->get('themes/test-theme/editor/default/en/regions/site-header.json'), true);

        expect($saved)->toBe($current);
    });

    test('saves custom assignable templates in type-first directories', function () {
        $current = [
            'blocks' => [
                'block-1' => ['id' => 'block-1', 'type' => 'text'],
            ],
            'regions' => [['name' => 'main', 'shared' => false, 'blocks' => ['block-1']]],
        ];

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with('/theme/views/templates/product/gift-box.blade.php', Mockery::type(UpdateRequest::class), ['main'])
            ->andReturn(['updated' => true, 'data' => $current]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'product.gift-box',
                'sources' => encrypt(['/theme/views/templates/product/gift-box.blade.php']),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => $current['regions'],
                'changes' => [
                    'added' => ['block-1'],
                    'updated' => [],
                    'removed' => [],
                ],
            ],
        ]));

        expect(Storage::disk('themes-data')->exists('themes/test-theme/editor/default/en/templates/product/gift-box.json'))->toBeTrue();
    });

    test('uses preview context when parsing shared region source data', function () {
        Storage::fake('local');
        app()->setLocale('en');

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache')->twice();
        app()->instance(JsonViewParser::class, $parser);

        $basePath = 'test-theme/default/ar/editor';
        $regionSourcePath = '/theme/views/regions/site-header.blade.php';

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'ar', 'editor', 'regions/site-header.json')
            ->andReturn(Storage::path($basePath.'/regions/site-header.json'));

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with($regionSourcePath, Mockery::type(UpdateRequest::class), ['site-header'])
            ->andReturnUsing(function () {
                expect(app()->getLocale())->toBe('ar')
                    ->and(Craftile::inPreview())->toBeTrue();

                return ['updated' => false, 'data' => []];
            });

        $persistEditorUpdates = persistEditorUpdatesWith($handleUpdates);

        $result = $persistEditorUpdates->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'ar',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([$regionSourcePath]),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => [
                    ['id' => 'site-header', 'name' => 'Header', 'shared' => true, 'blocks' => []],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => [],
                    'removed' => [],
                ],
            ],
        ]));

        expect($result['loadedBlocks'])->toBeEmpty()
            ->and(app()->getLocale())->toBe('en')
            ->and(Craftile::inPreview())->toBeFalse();
    });

    test('uses non-shared region ids for update targeting', function () {
        Storage::fake('local');

        $basePath = 'test-theme/default/en/editor';
        Storage::put($basePath.'/templates/index.json', '{}');

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'en', 'editor', 'templates/index.json')
            ->andReturn(Storage::path($basePath.'/templates/index.json'));

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with(Storage::path($basePath.'/templates/index.json'), Mockery::type(UpdateRequest::class), ['main-content'])
            ->andReturn(['updated' => false, 'data' => []]);

        $persistEditorUpdates = persistEditorUpdatesWith($handleUpdates);

        $result = $persistEditorUpdates->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => [
                    ['id' => 'main-content', 'name' => 'Main content', 'shared' => false, 'blocks' => []],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => [],
                    'removed' => [],
                ],
            ],
        ]));

        expect($result['loadedBlocks'])->toBeEmpty();
    });

    test('uses preview context when existing editor template data is used', function () {
        Storage::fake('local');
        app()->setLocale('en');
        Craftile::detectPreviewUsing(fn () => false);

        $basePath = 'test-theme/default/ar/editor';
        Storage::put($basePath.'/templates/index.json', '{}');

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache')->twice();
        app()->instance(JsonViewParser::class, $parser);

        ThemePathsResolver::shouldReceive('resolvePath')
            ->with('test-theme', 'default', 'ar', 'editor', 'templates/index.json')
            ->andReturn(Storage::path($basePath.'/templates/index.json'));

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with(Storage::path($basePath.'/templates/index.json'), Mockery::type(UpdateRequest::class), ['main-content'])
            ->andReturnUsing(function () {
                expect(app()->getLocale())->toBe('ar')
                    ->and(Craftile::inPreview())->toBeTrue();

                return ['updated' => false, 'data' => []];
            });

        $persistEditorUpdates = persistEditorUpdatesWith($handleUpdates);

        $result = $persistEditorUpdates->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'ar',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => [],
                'regions' => [
                    ['id' => 'main-content', 'name' => 'Main content', 'shared' => false, 'blocks' => []],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => [],
                    'removed' => [],
                ],
            ],
        ]));

        expect($result['loadedBlocks'])->toBeEmpty()
            ->and(app()->getLocale())->toBe('en')
            ->and(Craftile::inPreview())->toBeFalse();
    });

    test('saves new locale template as parent diff when editor fallback exists', function () {
        $dataPath = sys_get_temp_dir().'/visual-parent-persist-'.uniqid();
        config()->set('bagisto_visual.storages.file.path', $dataPath);

        app()->instance(ThemePathsResolverConcrete::class, new class extends ThemePathsResolverConcrete
        {
            public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
            {
                return [
                    $this->buildThemePath($themeCode, $mode, $channel, $locale),
                    $this->buildThemePath($themeCode, $mode, 'default', 'en'),
                ];
            }
        });

        $files = new Filesystem;
        $baseTemplate = "{$dataPath}/themes/test-theme/editor/default/en/templates/index.json";
        $files->ensureDirectoryExists(dirname($baseTemplate));
        $files->put($baseTemplate, json_encode([
            'blocks' => [
                'hero' => [
                    'id' => 'hero',
                    'type' => 'theme::hero',
                    'properties' => ['title' => 'Base', 'subtitle' => 'Same'],
                ],
            ],
            'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']]],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache')->twice();
        $parser->shouldNotReceive('parse');
        app()->instance(JsonViewParser::class, $parser);

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with("{$dataPath}/themes/test-theme/editor/default/en/templates/index.json", Mockery::type(UpdateRequest::class), ['main-content'])
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'hero' => [
                            'id' => 'hero',
                            'type' => 'theme::hero',
                            'properties' => ['title' => 'Arabic', 'subtitle' => 'Same'],
                        ],
                    ],
                    'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']]],
                ],
            ]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'ar',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => ['hero' => ['id' => 'hero', 'type' => 'theme::hero']],
                'regions' => [
                    ['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => ['hero'],
                    'removed' => [],
                ],
            ],
        ]));

        $saved = json_decode($files->get("{$dataPath}/themes/test-theme/editor/default/ar/templates/index.json"), true);

        expect($saved)->toBe([
            'parent' => 'default/en/templates/index.json',
            'blocks' => [
                'hero' => [
                    'properties' => ['title' => 'Arabic'],
                ],
            ],
        ]);

        $files->deleteDirectory($dataPath);
    });

    test('forces localized values for all inherited blocks when saving a child locale partial update', function () {
        $dataPath = sys_get_temp_dir().'/visual-localized-barrier-persist-'.uniqid();
        config()->set('bagisto_visual.storages.file.path', $dataPath);

        app()->instance(ThemePathsResolverConcrete::class, new class extends ThemePathsResolverConcrete
        {
            public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
            {
                return [
                    $this->buildThemePath($themeCode, $mode, $channel, $locale),
                    $this->buildThemePath($themeCode, $mode, 'default', 'en'),
                ];
            }
        });

        app(BlockSchemaRegistry::class)->register(BlockSchema::fromClass(PersistEditorUpdatesLocalizedBlock::class));

        $files = new Filesystem;
        $baseTemplate = "{$dataPath}/themes/test-theme/editor/default/en/templates/index.json";
        $childTemplate = "{$dataPath}/themes/test-theme/editor/default/ar/templates/index.json";
        $baseData = [
            'blocks' => [
                'hero' => [
                    'id' => 'hero',
                    'type' => 'persist-editor-localized-block',
                    'properties' => ['title' => 'Hero EN', 'handle' => 'hero'],
                ],
                'banner' => [
                    'id' => 'banner',
                    'type' => 'persist-editor-localized-block',
                    'properties' => ['title' => 'Banner EN', 'handle' => 'banner'],
                ],
            ],
            'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero', 'banner']]],
        ];

        $files->ensureDirectoryExists(dirname($baseTemplate));
        $files->put($baseTemplate, json_encode($baseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $files->ensureDirectoryExists(dirname($childTemplate));
        $files->put($childTemplate, json_encode([
            'parent' => 'default/en/templates/index.json',
            'blocks' => [
                'banner' => [
                    'properties' => ['title' => 'Banner AR'],
                ],
            ],
            'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['banner', 'hero']]],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache')->twice();
        $parser->shouldNotReceive('parse');
        app()->instance(JsonViewParser::class, $parser);

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with($childTemplate, Mockery::type(UpdateRequest::class), ['main-content'])
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'banner' => [
                            'properties' => ['title' => 'Banner AR'],
                        ],
                    ],
                    'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['banner', 'hero']]],
                ],
            ]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'ar',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => ['banner' => ['id' => 'banner', 'type' => 'persist-editor-localized-block']],
                'regions' => [
                    ['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['banner', 'hero']],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => ['banner'],
                    'removed' => [],
                ],
            ],
        ]));

        $saved = json_decode($files->get($childTemplate), true);

        expect($saved)->toBe([
            'parent' => 'default/en/templates/index.json',
            'blocks' => [
                'banner' => [
                    'properties' => ['title' => 'Banner AR'],
                ],
                'hero' => [
                    'properties' => ['title' => 'Hero EN'],
                ],
            ],
            'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['banner', 'hero']]],
        ]);

        $files->deleteDirectory($dataPath);
    });

    test('registers discovered schemas before localized values are forced into locale diffs', function () {
        $dataPath = sys_get_temp_dir().'/visual-schema-registration-persist-'.uniqid();
        config()->set('bagisto_visual.storages.file.path', $dataPath);

        app()->instance(ThemePathsResolverConcrete::class, new class extends ThemePathsResolverConcrete
        {
            public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
            {
                return [
                    $this->buildThemePath($themeCode, $mode, $channel, $locale),
                    $this->buildThemePath($themeCode, $mode, 'default', 'en'),
                ];
            }
        });

        $files = new Filesystem;
        $baseTemplate = "{$dataPath}/themes/test-theme/editor/default/en/templates/index.json";
        $baseData = [
            'blocks' => [
                'hero' => [
                    'id' => 'hero',
                    'type' => 'persist-editor-localized-block',
                    'properties' => ['title' => 'Base', 'handle' => 'same'],
                ],
            ],
            'regions' => [['id' => 'main-content', 'name' => 'Main', 'blocks' => ['hero']]],
        ];
        $files->ensureDirectoryExists(dirname($baseTemplate));
        $files->put($baseTemplate, json_encode($baseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        app(BlockSchemaRegistry::class)->clear();
        Craftile::clearResolvedInstance('craftile');
        app()->instance('craftile', new class(app(BlockSchemaRegistry::class))
        {
            protected $previewDetector;

            public function __construct(protected BlockSchemaRegistry $registry) {}

            public function detectPreviewUsing(callable $detector): void
            {
                $this->previewDetector = $detector;
            }

            public function inPreview(): bool
            {
                return $this->previewDetector ? (bool) call_user_func($this->previewDetector) : false;
            }

            public function registerDiscoveredSchemas(): void
            {
                $this->registry->register(BlockSchema::fromClass(PersistEditorUpdatesLocalizedBlock::class));
            }
        });

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache')->twice();
        $parser->shouldNotReceive('parse');
        app()->instance(JsonViewParser::class, $parser);

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->with($baseTemplate, Mockery::type(UpdateRequest::class), ['main-content'])
            ->andReturn([
                'updated' => true,
                'data' => $baseData,
            ]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'ar',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => ['hero' => $baseData['blocks']['hero']],
                'regions' => [
                    ['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => ['hero'],
                    'removed' => [],
                ],
            ],
        ]));

        $saved = json_decode($files->get("{$dataPath}/themes/test-theme/editor/default/ar/templates/index.json"), true);

        expect($saved)->toBe([
            'parent' => 'default/en/templates/index.json',
            'blocks' => [
                'hero' => [
                    'properties' => ['title' => 'Base'],
                ],
            ],
        ])->and(app(BlockSchemaRegistry::class)->all())->toHaveKey('persist-editor-localized-block');

        $files->deleteDirectory($dataPath);
    });

    test('returns loaded blocks from shared regions', function () {
        $sources = ['header' => 'path/to/header.blade.php'];

        $mock = partialPersistEditorUpdates();

        // Mock persistPartialRegionUpdate to return test data
        $mock->shouldReceive('persistPartialRegionUpdate')
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

        $result = $mock->handle(editorUpdateData($data));

        expect($result)->toHaveKey('loadedBlocks')
            ->and($result['loadedBlocks'])->toHaveKey('block-1')
            ->and($result['loadedBlocks']['block-1']['content'])->toBe('Header');
    });

    test('returns loaded blocks from template regions', function () {
        $sources = ['main' => 'path/to/main.blade.php'];

        $mock = partialPersistEditorUpdates();

        // Mock persistPartialTemplateUpdate to return test data
        $mock->shouldReceive('persistPartialTemplateUpdate')
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

        $result = $mock->handle(editorUpdateData($data));

        expect($result)->toHaveKey('loadedBlocks')
            ->and($result['loadedBlocks'])->toHaveKey('block-1')
            ->and($result['loadedBlocks']['block-1']['content'])->toBe('Main');
    });

    test('merges blocks from both shared and template regions', function () {
        $sources = [
            'header' => 'path/to/header.blade.php',
            'main' => 'path/to/main.blade.php',
        ];

        $mock = partialPersistEditorUpdates();

        // Mock persistPartialRegionUpdate for header
        $mock->shouldReceive('persistPartialRegionUpdate')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'block-1' => ['id' => 'block-1', 'type' => 'text', 'content' => 'Header'],
                    ],
                ],
            ]);

        // Mock persistPartialTemplateUpdate for main
        $mock->shouldReceive('persistPartialTemplateUpdate')
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

        $result = $mock->handle(editorUpdateData($data));

        expect($result['loadedBlocks'])->toHaveKeys(['block-1', 'block-2'])
            ->and($result['loadedBlocks']['block-1']['content'])->toBe('Header')
            ->and($result['loadedBlocks']['block-2']['content'])->toBe('Main');
    });

    test('returns empty blocks when nothing was updated', function () {
        $sources = ['header' => 'path/to/header.blade.php'];

        $mock = partialPersistEditorUpdates();

        // Mock persistPartialRegionUpdate to return no update
        $mock->shouldReceive('persistPartialRegionUpdate')
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

        $result = $mock->handle(editorUpdateData($data));

        expect($result)->toHaveKey('loadedBlocks')
            ->and($result['loadedBlocks'])->toBeEmpty();
    });
});

describe('stored block pruning', function () {
    test('drops schema defaults and default metadata from the saved root template', function () {
        $dataPath = sys_get_temp_dir().'/visual-pruned-persist-'.uniqid();
        config()->set('bagisto_visual.storages.file.path', $dataPath);

        app(BlockSchemaRegistry::class)->register(BlockSchema::fromClass(PersistEditorUpdatesDefaultsBlock::class));

        $files = new Filesystem;
        $template = "{$dataPath}/themes/test-theme/editor/default/en/templates/index.json";
        $files->ensureDirectoryExists(dirname($template));
        $files->put($template, json_encode(['blocks' => [], 'regions' => []]));

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache');
        $parser->shouldNotReceive('parse');
        app()->instance(JsonViewParser::class, $parser);

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'hero' => [
                            'id' => 'hero',
                            'type' => 'persist-editor-defaults-block',
                            'properties' => ['align' => 'center', 'title' => 'Hello'],
                            'parentId' => null,
                            'children' => [],
                            'disabled' => false,
                            'static' => true,
                            'semanticId' => null,
                            'index' => 0,
                            'iteration' => 1,
                        ],
                    ],
                    'regions' => [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']]],
                ],
            ]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'en',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => ['hero' => ['id' => 'hero', 'type' => 'persist-editor-defaults-block']],
                'regions' => [
                    ['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']],
                ],
                'changes' => [
                    'added' => [],
                    'updated' => ['hero'],
                    'removed' => [],
                ],
            ],
        ]));

        $saved = json_decode($files->get($template), true);

        expect($saved['blocks']['hero'])->toBe([
            'id' => 'hero',
            'type' => 'persist-editor-defaults-block',
            'properties' => ['title' => 'Hello'],
            'parentId' => null,
            'children' => [],
            'static' => true,
        ]);

        $files->deleteDirectory($dataPath);
    });

    test('keeps a child locale value that resets an overridden parent value to the default', function () {
        $dataPath = sys_get_temp_dir().'/visual-pruned-child-persist-'.uniqid();
        config()->set('bagisto_visual.storages.file.path', $dataPath);

        app()->instance(ThemePathsResolverConcrete::class, new class extends ThemePathsResolverConcrete
        {
            public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
            {
                return [
                    $this->buildThemePath($themeCode, $mode, $channel, $locale),
                    $this->buildThemePath($themeCode, $mode, 'default', 'en'),
                ];
            }
        });

        app(BlockSchemaRegistry::class)->register(BlockSchema::fromClass(PersistEditorUpdatesDefaultsBlock::class));

        $files = new Filesystem;
        $baseTemplate = "{$dataPath}/themes/test-theme/editor/default/en/templates/index.json";
        $childTemplate = "{$dataPath}/themes/test-theme/editor/default/ar/templates/index.json";
        $regions = [['id' => 'main-content', 'name' => 'Main', 'shared' => false, 'blocks' => ['hero']]];

        $files->ensureDirectoryExists(dirname($baseTemplate));
        $files->put($baseTemplate, json_encode([
            'blocks' => [
                'hero' => [
                    'id' => 'hero',
                    'type' => 'persist-editor-defaults-block',
                    'properties' => ['align' => 'left', 'title' => 'Hello'],
                ],
            ],
            'regions' => $regions,
        ]));

        $parser = Mockery::mock(JsonViewParser::class);
        $parser->shouldReceive('clearCache');
        $parser->shouldNotReceive('parse');
        app()->instance(JsonViewParser::class, $parser);

        $handleUpdates = Mockery::mock(HandleUpdates::class);
        $handleUpdates->shouldReceive('execute')
            ->once()
            ->andReturn([
                'updated' => true,
                'data' => [
                    'blocks' => [
                        'hero' => [
                            'id' => 'hero',
                            'type' => 'persist-editor-defaults-block',
                            'properties' => ['align' => 'center', 'title' => 'Hello'],
                            'disabled' => false,
                            'index' => 0,
                        ],
                    ],
                    'regions' => $regions,
                ],
            ]);

        persistEditorUpdatesWith($handleUpdates)->handle(editorUpdateData([
            'theme' => 'test-theme',
            'channel' => 'default',
            'locale' => 'ar',
            'template' => [
                'name' => 'index',
                'sources' => encrypt([]),
            ],
            'updates' => [
                'blocks' => ['hero' => ['id' => 'hero', 'type' => 'persist-editor-defaults-block']],
                'regions' => $regions,
                'changes' => [
                    'added' => [],
                    'updated' => ['hero'],
                    'removed' => [],
                ],
            ],
        ]));

        $saved = json_decode($files->get($childTemplate), true);

        expect($saved['parent'])->toBe('default/en/templates/index.json')
            ->and($saved['blocks']['hero'])->toBe(['properties' => ['align' => 'center', 'title' => 'Hello']]);

        $files->deleteDirectory($dataPath);
    });
});
