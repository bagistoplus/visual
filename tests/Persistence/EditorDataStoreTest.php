<?php

use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\ThemePathsResolver;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/visual-editor-store-'.uniqid();
    config()->set('bagisto_visual.data_path', $this->dataPath);

    $this->resolver = new class extends ThemePathsResolver
    {
        public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
        {
            return [
                $this->buildThemePath($themeCode, $mode, $channel, $locale),
                $this->buildThemePath($themeCode, $mode, 'default', 'en'),
            ];
        }
    };

    $this->store = new EditorDataStore($this->resolver, new Filesystem);
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->dataPath);
});

it('resolves parent chains recursively and strips parent metadata', function () {
    file_put_contents_for_store('demo', 'default/en/templates/index.json', [
        'blocks' => [
            'hero' => [
                'type' => 'theme::hero',
                'properties' => ['title' => 'Base', 'subtitle' => 'Keep'],
            ],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero']]],
    ]);

    file_put_contents_for_store('demo', 'default/ar/templates/index.json', [
        'parent' => 'default/en/templates/index.json',
        'blocks' => [
            'hero' => ['properties' => ['title' => 'Arabic']],
        ],
    ]);

    expect($this->store->loadResolved('demo', 'default/ar/templates/index.json'))
        ->toBe([
            'blocks' => [
                'hero' => [
                    'type' => 'theme::hero',
                    'properties' => ['title' => 'Arabic', 'subtitle' => 'Keep'],
                ],
            ],
            'regions' => [['name' => 'main', 'blocks' => ['hero']]],
        ]);
});

it('parses app written relative paths directly', function () {
    expect($this->store->logicalPathFromRelative('default/ar/templates/index.json'))->toBe('templates/index.json')
        ->and($this->store->localeFromRelative('default/ar/templates/index.json'))->toBe('ar')
        ->and($this->store->relativePath('default', 'ar', '/templates/index.json'))->toBe('default/ar/templates/index.json');
});

it('ignores unsafe stored parents and falls back to raw child data', function () {
    file_put_contents_for_store('demo', 'default/ar/templates/index.json', [
        'parent' => '../default/en/templates/index.json',
        'blocks' => ['hero' => ['properties' => ['title' => 'Arabic']]],
    ]);

    expect($this->store->storedParent('demo', 'default/ar/templates/index.json'))->toBeNull()
        ->and($this->store->loadResolved('demo', 'default/ar/templates/index.json'))
        ->toBe(['blocks' => ['hero' => ['properties' => ['title' => 'Arabic']]]]);
});

it('returns empty data for invalid or non array json', function (string $content) {
    $path = $this->store->path('demo', 'default/en/theme.json');
    (new Filesystem)->ensureDirectoryExists(dirname($path));
    file_put_contents($path, $content);

    expect($this->store->loadRaw('demo', 'default/en/theme.json'))->toBe([])
        ->and($this->store->loadResolved('demo', 'default/en/theme.json'))->toBe([]);
})->with([
    'invalid json' => ['{'],
    'scalar json' => ['"not-array"'],
]);

it('merges associative arrays and replaces indexed arrays', function () {
    $parent = [
        'blocks' => [
            'hero' => ['properties' => ['title' => 'Base', 'items' => ['a', 'b'], 'parent' => 'nested-base']],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero']]],
    ];

    $child = [
        'parent' => 'default/en/templates/index.json',
        'blocks' => [
            'hero' => ['properties' => ['items' => ['c']]],
        ],
    ];

    expect($this->store->merge($parent, $child))->toBe([
        'blocks' => [
            'hero' => ['properties' => ['title' => 'Base', 'items' => ['c'], 'parent' => 'nested-base']],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero']]],
    ]);
});

it('only ignores top level parent metadata', function () {
    $parent = [
        'parent' => 'default/en/theme.json',
        'settings' => [
            'card' => ['parent' => 'nested-parent', 'title' => 'Base'],
        ],
    ];

    $child = [
        'parent' => 'default/en/theme.json',
        'settings' => [
            'card' => ['parent' => 'nested-child'],
        ],
    ];

    expect($this->store->merge($parent, $child))->toBe([
        'settings' => [
            'card' => ['parent' => 'nested-child', 'title' => 'Base'],
        ],
    ])->and($this->store->diff($child, $parent))->toBe([
        'settings' => [
            'card' => ['parent' => 'nested-child'],
        ],
    ]);
});

it('diffs recursively and omits empty equal branches', function () {
    $current = [
        'blocks' => [
            'hero' => ['properties' => ['title' => 'Changed', 'subtitle' => 'Same', 'parent' => 'nested-current']],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero', 'cta']]],
    ];

    $parent = [
        'blocks' => [
            'hero' => ['properties' => ['title' => 'Base', 'subtitle' => 'Same', 'parent' => 'nested-parent']],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero']]],
    ];

    expect($this->store->diff($current, $parent))->toBe([
        'blocks' => [
            'hero' => ['properties' => ['title' => 'Changed', 'parent' => 'nested-current']],
        ],
        'regions' => [['name' => 'main', 'blocks' => ['hero', 'cta']]],
    ]);
});

it('deletes parent-only child files and marks last edit', function () {
    file_put_contents_for_store('demo', 'default/ar/theme.json', [
        'parent' => 'default/en/theme.json',
        'headline' => 'Old',
    ]);

    $this->store->save('demo', 'default/ar/theme.json', [], 'default/en/theme.json');

    expect(file_exists($this->store->path('demo', 'default/ar/theme.json')))->toBeFalse()
        ->and(file_exists($this->resolver->getThemeBaseDataPath('demo', 'editor/.last-edit')))->toBeTrue();
});

it('creates the edit marker directory when saving an empty root file', function () {
    $this->store->save('demo', 'default/en/theme.json', [], null);

    expect(file_exists($this->store->path('demo', 'default/en/theme.json')))->toBeTrue()
        ->and(file_exists($this->resolver->getThemeBaseDataPath('demo', 'editor/.last-edit')))->toBeTrue();
});

it('ignores source parents outside the editor data root', function () {
    file_put_contents_for_store('demo', 'default/en/theme.json', ['headline' => 'Base']);
    $outsidePath = "{$this->dataPath}/outside/default/en/theme.json";
    (new Filesystem)->ensureDirectoryExists(dirname($outsidePath));
    file_put_contents($outsidePath, json_encode(['headline' => 'Outside']));

    expect($this->store->parentFromSources('demo', 'theme.json', [$outsidePath], 'default/ar/theme.json'))->toBeNull();
});

it('finds the first existing nearest fallback parent and skips the current path', function () {
    file_put_contents_for_store('demo', 'default/en/theme.json', ['headline' => 'Base']);

    expect($this->store->nearestFallbackParent('demo', 'default', 'ar', 'theme.json'))->toBe('default/en/theme.json');

    file_put_contents_for_store('demo', 'default/ar/theme.json', ['headline' => 'Arabic']);

    expect($this->store->nearestFallbackParent('demo', 'default', 'ar', 'theme.json'))->toBe('default/en/theme.json');
});

function file_put_contents_for_store(string $theme, string $relativePath, array $data): void
{
    $path = config('bagisto_visual.data_path')."/themes/{$theme}/editor/{$relativePath}";
    (new Filesystem)->ensureDirectoryExists(dirname($path));
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
