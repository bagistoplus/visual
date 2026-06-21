<?php

use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use BagistoPlus\Visual\Persistence\EditorDataStore;
use BagistoPlus\Visual\Support\EditorInheritanceMetadata;
use BagistoPlus\Visual\Support\TemplateDiscovery;
use BagistoPlus\Visual\Theme\Theme;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemePathsResolver as ThemePathsResolverConcrete;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PreviewDataCollector;
use Craftile\Laravel\PropertyBag;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Theme\Facades\Themes as ThemesFacade;

class TestableInjectThemeEditorScript extends InjectThemeEditorScript
{
    public function inject(Response $response): void
    {
        $this->injectPreviewScripts($response, Request::create('/'));
    }

    public function payload(array $pageData): array
    {
        return $this->buildInjectedPageData($pageData, 'index', new PropertyBag);
    }

    protected function getCurrentPageData(): array
    {
        return ['title' => 'Preview'];
    }

    protected function buildPreviewScripts(array $pageData): string
    {
        return '<script id="preview-script">window.previewValue = "$1";</script>';
    }

    protected function fixCategoryOrProductRoute($routeName)
    {
        return 'shop.home.index';
    }
}

function bindPreviewCurrentTheme(): void
{
    ThemesFacade::swap(new class
    {
        public function current(): Theme
        {
            return Theme::make([
                'code' => 'test-theme',
                'name' => 'Test Theme',
                'visual_theme' => true,
            ]);
        }
    });
}

function testableInjectThemeEditorScript(
    ?ThemeEditor $themeEditor = null,
    ?ThemeSettingsLoader $themeSettingsLoader = null,
    ?EditorInheritanceMetadata $editorInheritanceMetadata = null,
): TestableInjectThemeEditorScript {
    return new TestableInjectThemeEditorScript(
        $themeEditor ?? Mockery::mock(ThemeEditor::class),
        $themeSettingsLoader ?? Mockery::mock(ThemeSettingsLoader::class),
        Mockery::mock(CategoryRepository::class),
        Mockery::mock(ProductRepository::class),
        Mockery::mock(PreviewDataCollector::class),
        $editorInheritanceMetadata ?? app(EditorInheritanceMetadata::class),
    );
}

it('injects the preview script immediately after the opening head tag', function () {
    $response = new Response('<html><head><title>Store</title></head><body></body></html>', 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);

    testableInjectThemeEditorScript()->inject($response);

    expect($response->getContent())
        ->toBe('<html><head><script id="preview-script">window.previewValue = "$1";</script><title>Store</title></head><body></body></html>');
});

it('preserves opening head attributes and casing when injecting', function () {
    $response = new Response('<html><HEAD data-theme="default" ><title>Store</title></HEAD><body></body></html>', 200, [
        'Content-Type' => 'text/html',
    ]);

    testableInjectThemeEditorScript()->inject($response);

    expect($response->getContent())
        ->toBe('<html><HEAD data-theme="default" ><script id="preview-script">window.previewValue = "$1";</script><title>Store</title></HEAD><body></body></html>');
});

it('does not inject into json responses', function () {
    $response = new JsonResponse(['foo' => 'bar']);
    $originalContent = $response->getContent();

    testableInjectThemeEditorScript()->inject($response);

    expect($response->getContent())
        ->toBe($originalContent);
});

it('includes the resolved preview channel and locale in page data', function () {
    bindPreviewCurrentTheme();

    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('getTemplateFromJsonViews')->with('index')->andReturn('index');
    $themeEditor->shouldReceive('jsonViews')->andReturn([]);
    $themeEditor->shouldReceive('preloadedModels')->andReturn([]);

    $themeSettingsLoader = Mockery::mock(ThemeSettingsLoader::class);
    $themeSettingsLoader->shouldReceive('loadActiveThemeSettings')->andReturn(new PropertyBag);

    $middleware = testableInjectThemeEditorScript(
        $themeEditor,
        $themeSettingsLoader,
        app(EditorInheritanceMetadata::class),
    );

    $payload = $middleware->payload(['title' => 'Preview']);

    expect($payload)
        ->toHaveKey('channel', 'default')
        ->toHaveKey('locale', 'en')
        ->toHaveKey('localeInheritance', [])
        ->toHaveKey('blockSchemas', []);
});

it('includes locale inheritance for the current template in page data', function () {
    bindPreviewCurrentTheme();

    $dataPath = sys_get_temp_dir().'/visual-injected-inheritance-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $files = new Filesystem;
    $files->ensureDirectoryExists("{$dataPath}/themes/test-theme/editor/default/en/templates");
    $files->ensureDirectoryExists("{$dataPath}/themes/test-theme/editor/default/ar/templates");
    $files->put("{$dataPath}/themes/test-theme/editor/default/en/templates/index.json", json_encode(['blocks' => []]));
    $files->put("{$dataPath}/themes/test-theme/editor/default/ar/templates/index.json", json_encode([
        'parent' => 'default/en/templates/index.json',
    ]));

    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('getTemplateFromJsonViews')->with('index')->andReturn('index');
    $themeEditor->shouldReceive('jsonViews')->andReturn([]);
    $themeEditor->shouldReceive('preloadedModels')->andReturn([]);

    $themeSettingsLoader = Mockery::mock(ThemeSettingsLoader::class);
    $themeSettingsLoader->shouldReceive('loadActiveThemeSettings')->andReturn(new PropertyBag);

    $middleware = testableInjectThemeEditorScript(
        $themeEditor,
        $themeSettingsLoader,
        new EditorInheritanceMetadata(
            new EditorDataStore(new ThemePathsResolverConcrete, $files),
            app(TemplateDiscovery::class),
        ),
    );

    $payload = $middleware->payload(['title' => 'Preview']);

    expect($payload['localeInheritance'])->toBe([
        'ar' => [
            'parentChannel' => 'default',
            'parentLocale' => 'en',
        ],
    ]);

    $files->deleteDirectory($dataPath);
});

it('includes cross-channel locale inheritance for the current template in page data', function () {
    bindPreviewCurrentTheme();

    core()->setRequestedChannelCode('uea');
    core()->setRequestedLocaleCode('ar');
    core()->setChannels([
        (object) [
            'code' => 'default',
            'name' => 'Default',
            'locales' => collect([
                (object) ['code' => 'en', 'name' => 'English'],
                (object) ['code' => 'ar', 'name' => 'Arabic'],
            ]),
            'default_locale' => (object) ['code' => 'en'],
        ],
        (object) [
            'code' => 'uea',
            'name' => 'UEA',
            'locales' => collect([
                (object) ['code' => 'en', 'name' => 'English'],
                (object) ['code' => 'ar', 'name' => 'Arabic'],
            ]),
            'default_locale' => (object) ['code' => 'en'],
        ],
    ]);

    $dataPath = sys_get_temp_dir().'/visual-injected-cross-channel-inheritance-'.uniqid();
    config()->set('bagisto_visual.data_path', $dataPath);

    $files = new Filesystem;
    $files->ensureDirectoryExists("{$dataPath}/themes/test-theme/editor/default/ar/templates");
    $files->ensureDirectoryExists("{$dataPath}/themes/test-theme/editor/uea/ar/templates");
    $files->put("{$dataPath}/themes/test-theme/editor/default/ar/templates/index.json", json_encode(['blocks' => []]));
    $files->put("{$dataPath}/themes/test-theme/editor/uea/ar/templates/index.json", json_encode([
        'parent' => 'default/ar/templates/index.json',
    ]));

    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('getTemplateFromJsonViews')->with('index')->andReturn('index');
    $themeEditor->shouldReceive('jsonViews')->andReturn([]);
    $themeEditor->shouldReceive('preloadedModels')->andReturn([]);

    $themeSettingsLoader = Mockery::mock(ThemeSettingsLoader::class);
    $themeSettingsLoader->shouldReceive('loadActiveThemeSettings')->andReturn(new PropertyBag);

    $middleware = testableInjectThemeEditorScript(
        $themeEditor,
        $themeSettingsLoader,
        new EditorInheritanceMetadata(
            new EditorDataStore(new ThemePathsResolverConcrete, $files),
            app(TemplateDiscovery::class),
        ),
    );

    $payload = $middleware->payload(['title' => 'Preview']);

    expect($payload['localeInheritance'])->toBe([
        'ar' => [
            'parentChannel' => 'default',
            'parentLocale' => 'ar',
        ],
    ]);

    $files->deleteDirectory($dataPath);
});
