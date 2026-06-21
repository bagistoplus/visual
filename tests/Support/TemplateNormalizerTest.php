<?php

use BagistoPlus\Visual\Support\TemplateNormalizer;
use BagistoPlus\Visual\ThemePathsResolver;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Craftile::normalizeTemplateUsing(new TemplateNormalizer);
    Craftile::detectPreviewUsing(fn () => true);
    app(JsonViewParser::class)->clearCache();
});

it('resolves editor parent data from the parsed editor file path', function () {
    $dataPath = sys_get_temp_dir().'/visual-template-normalizer-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    app()->instance(ThemePathsResolver::class, new ThemePathsResolver);

    $parentPath = "{$dataPath}/themes/visual-debut/editor/default/en/templates/index.json";
    $childPath = "{$dataPath}/themes/visual-debut/editor/default/fr/templates/index.json";

    File::ensureDirectoryExists(dirname($parentPath));
    File::ensureDirectoryExists(dirname($childPath));

    File::put($parentPath, json_encode([
        'blocks' => [
            'section' => [
                'id' => 'section',
                'type' => '@basic-blocks/flex-section',
                'name' => 'Image with Text',
                'properties' => ['section_width' => 'container'],
                'children' => ['image', 'content'],
            ],
        ],
        'regions' => [
            ['id' => 'main', 'name' => 'main', 'blocks' => ['section'], 'shared' => false],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    File::put($childPath, json_encode([
        'parent' => 'default/en/templates/index.json',
        'blocks' => [
            'section' => [
                'children' => ['content', 'image'],
            ],
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $parsed = app(JsonViewParser::class)->parse($childPath);

    expect($parsed['blocks']['section'])
        ->toMatchArray([
            'id' => 'section',
            'type' => '@basic-blocks/flex-section',
            'name' => 'Image with Text',
            'properties' => ['section_width' => 'container'],
            'children' => ['content', 'image'],
        ]);

    File::deleteDirectory($dataPath);
});
