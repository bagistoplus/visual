<?php

use BagistoPlus\Visual\Data\TemplateFile;
use BagistoPlus\Visual\Persistence\CreateTemplate;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Support\TemplateNormalizer;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemePathsResolver;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('craftile.php_template_extensions', ['visual.php']);
    Craftile::normalizeTemplateUsing(new TemplateNormalizer);
    app(JsonViewParser::class)->clearCache();

    app()->instance(ThemePathsResolver::class, new class extends ThemePathsResolver
    {
        public function resolveFallbackPaths(string $themeCode, string $mode, string $channel, string $locale): array
        {
            return [
                $this->buildThemePath($themeCode, $mode, $channel, $locale),
            ];
        }
    });
});

function parseTemplateFileForTest(TemplateDiscovery $discovery, string $path, string $source): ?TemplateFile
{
    $method = new ReflectionMethod($discovery, 'createFromFile');
    $method->setAccessible(true);

    return $method->invoke($discovery, $path, $source);
}

it('parses assignable templates from type-first paths', function () {
    $directory = sys_get_temp_dir().'/visual-template-discovery-'.uniqid();
    File::ensureDirectoryExists($directory.'/product');

    $path = $directory.'/product/gift-box.json';
    File::put($path, '{}');

    $template = parseTemplateFileForTest(app(TemplateDiscovery::class), $path, 'package');

    expect($template)->toBeInstanceOf(TemplateFile::class)
        ->and($template->key)->toBe('product.gift-box')
        ->and($template->type)->toBe('product')
        ->and($template->label)->toBe('Gift Box')
        ->and($template->source)->toBe('package')
        ->and($template->isJsonTemplate)->toBeTrue();

    File::deleteDirectory($directory);
});

it('parses default assignable data template filenames', function (string $filename, string $type) {
    $directory = sys_get_temp_dir().'/visual-template-discovery-'.uniqid();
    File::ensureDirectoryExists(dirname($directory.'/'.$filename));

    $path = $directory.'/'.$filename;
    File::put($path, '{}');

    $template = parseTemplateFileForTest(app(TemplateDiscovery::class), $path, 'package');

    expect($template)->toBeInstanceOf(TemplateFile::class)
        ->and($template->key)->toBe($type)
        ->and($template->type)->toBe($type)
        ->and($template->label)->toBe('Default '.Str::headline($type))
        ->and($template->isJsonTemplate)->toBeTrue();

    File::deleteDirectory($directory);
})->with([
    'product json' => ['product.json', 'product'],
    'category visual php' => ['category.visual.php', 'category'],
    'page yaml' => ['page.yaml', 'page'],
    'product index json' => ['product/index.json', 'product'],
    'category index visual php' => ['category/index.visual.php', 'category'],
    'page index yaml' => ['page/index.yaml', 'page'],
]);

it('rejects unsupported template filenames', function (string $filename) {
    $directory = sys_get_temp_dir().'/visual-template-discovery-'.uniqid();
    File::ensureDirectoryExists(dirname($directory.'/'.$filename));

    $path = $directory.'/'.$filename;
    File::put($path, '{}');

    expect(parseTemplateFileForTest(app(TemplateDiscovery::class), $path, 'package'))->toBeNull();

    File::deleteDirectory($directory);
})->with([
    'wrong type' => 'collection/gift-box.json',
    'legacy dotted custom' => 'gift-box.product.json',
    'custom dotted slug' => 'product/gift.box.json',
]);

it('creates empty editor templates with only an empty main region', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $createTemplate = app(CreateTemplate::class);

    $result = $createTemplate(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'product',
        name: 'Gift Box',
        basedOn: '__empty__',
    );

    $json = json_decode(File::get($result['path']), true);

    expect($result['key'])->toBe('product.gift-box')
        ->and($result['path'])->toEndWith('/templates/product/gift-box.json')
        ->and($json['regions'])->toBe([
            ['name' => 'main', 'shared' => false, 'blocks' => []],
        ])
        ->and($json['blocks'])->toBe([]);

    File::deleteDirectory($dataPath);
});

it('copies non-shared content from a default data template', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $templatePath = "{$dataPath}/themes/fake-theme/editor/default/en/templates/page/index.yaml";
    File::ensureDirectoryExists(dirname($templatePath));
    File::put($templatePath, <<<'YAML'
sections:
  page:
    type: '@visual-debut/cms-page'
order:
  - page
YAML);

    $result = app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'page',
        name: 'Landing',
        basedOn: 'page',
    );

    $json = json_decode(File::get($result['path']), true);

    expect($result['key'])->toBe('page.landing')
        ->and($json['regions'])->toBe([
            ['name' => 'main', 'blocks' => ['page']],
        ])
        ->and($json['blocks'])->toHaveKey('page')
        ->and($json['blocks']['page']['type'])->toBe('@visual-debut/cms-page');

    File::deleteDirectory($dataPath);
});

it('copies non-shared content from a default visual php template', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $templatePath = "{$dataPath}/themes/fake-theme/editor/default/en/templates/product/index.visual.php";
    File::ensureDirectoryExists(dirname($templatePath));
    File::put($templatePath, <<<'PHP'
<?php

use BagistoPlus\Visual\Support\TemplateBuilder;

return TemplateBuilder::make()
    ->section('product-information', '@visual-debut/product-information')
    ->order(['product-information']);
PHP);

    $result = app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'product',
        name: 'Landing',
        basedOn: 'product',
    );

    $json = json_decode(File::get($result['path']), true);

    expect($result['key'])->toBe('product.landing')
        ->and($json['regions'])->toBe([
            ['name' => 'main', 'blocks' => ['product-information']],
        ])
        ->and($json['blocks'])->toHaveKey('product-information')
        ->and($json['blocks']['product-information']['type'])->toBe('@visual-debut/product-information');

    File::deleteDirectory($dataPath);
});

it('filters shared regions after normalizing base templates', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $templatePath = "{$dataPath}/themes/fake-theme/editor/default/en/templates/page/source.json";
    File::ensureDirectoryExists(dirname($templatePath));
    File::put($templatePath, json_encode([
        'regions' => [
            ['name' => 'header', 'shared' => true, 'blocks' => ['header-block']],
            ['name' => 'main', 'shared' => false, 'blocks' => ['page-block']],
        ],
        'blocks' => [
            'header-block' => ['id' => 'header-block', 'type' => 'hero'],
            'page-block' => ['id' => 'page-block', 'type' => 'text'],
        ],
    ]));

    $result = app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'page',
        name: 'Copy',
        basedOn: 'page.source',
    );

    $json = json_decode(File::get($result['path']), true);

    expect($json['regions'])->toBe([
        ['name' => 'main', 'shared' => false, 'blocks' => ['page-block']],
    ])
        ->and(array_keys($json['blocks']))->toBe(['page-block']);

    File::deleteDirectory($dataPath);
});

it('rejects missing default data templates as duplication bases', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect(fn () => app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'page',
        name: 'Landing',
        basedOn: 'page',
    ))->toThrow(InvalidArgumentException::class, __('visual::theme-editor.create_template_errors.base_not_found'));
});

it('rejects invalid template names with a translated message', function () {
    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    expect(fn () => app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'page',
        name: '!!!',
        basedOn: '__empty__',
    ))->toThrow(InvalidArgumentException::class, __('visual::theme-editor.create_template_errors.invalid_name_or_type'));
});

it('rejects duplicate template names with a translated message', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'page',
        name: 'Landing',
        basedOn: '__empty__',
    );

    expect(fn () => app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'page',
        name: 'Landing',
        basedOn: '__empty__',
    ))->toThrow(InvalidArgumentException::class, __('visual::theme-editor.create_template_errors.already_exists'));
});

it('duplicates only non-shared regions and their blocks from an existing template', function () {
    $dataPath = sys_get_temp_dir().'/visual-create-template-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $theme = Theme::make([
        'code' => 'fake-theme',
        'name' => 'Fake Theme',
        'visual_theme' => true,
    ]);

    $templatePath = "{$dataPath}/themes/fake-theme/editor/default/en/templates/product/source.json";
    File::ensureDirectoryExists(dirname($templatePath));
    File::put($templatePath, json_encode([
        'regions' => [
            ['name' => 'header', 'shared' => true, 'blocks' => ['header-block']],
            ['name' => 'main', 'shared' => false, 'blocks' => ['main-block']],
            ['name' => 'aside', 'shared' => false, 'blocks' => ['aside-block']],
        ],
        'blocks' => [
            'header-block' => ['id' => 'header-block', 'type' => 'hero'],
            'main-block' => ['id' => 'main-block', 'type' => 'text', 'children' => ['child-block']],
            'child-block' => ['id' => 'child-block', 'type' => 'text'],
            'aside-block' => ['id' => 'aside-block', 'type' => 'image'],
        ],
    ]));

    $result = app(CreateTemplate::class)(
        theme: $theme,
        channel: 'default',
        locale: 'en',
        type: 'product',
        name: 'Copy',
        basedOn: 'product.source',
    );

    $json = json_decode(File::get($result['path']), true);

    expect($json['regions'])->toBe([
        ['name' => 'main', 'shared' => false, 'blocks' => ['main-block']],
        ['name' => 'aside', 'shared' => false, 'blocks' => ['aside-block']],
    ])
        ->and(array_keys($json['blocks']))->toBe(['main-block', 'aside-block', 'child-block']);

    File::deleteDirectory($dataPath);
});
