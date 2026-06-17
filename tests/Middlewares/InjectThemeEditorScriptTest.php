<?php

use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\PreviewDataCollector;
use Craftile\Laravel\PropertyBag;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class TestInjectThemeEditorScript extends InjectThemeEditorScript
{
    public function __construct() {}

    public function inject(Response $response): void
    {
        $this->injectPreviewScripts($response, Request::create('/'));
    }

    protected function getCurrentPageData(): array
    {
        return ['title' => 'Preview'];
    }

    protected function buildPreviewScripts(array $pageData): string
    {
        return '<script id="preview-script">window.previewValue = "$1";</script>';
    }
}

class PreviewContextInjectThemeEditorScript extends InjectThemeEditorScript
{
    public function payload(array $pageData): array
    {
        return $this->buildInjectedPageData($pageData, 'index', new PropertyBag);
    }

    protected function fixCategoryOrProductRoute($routeName)
    {
        return 'shop.home.index';
    }
}

it('injects the preview script immediately after the opening head tag', function () {
    $response = new Response('<html><head><title>Store</title></head><body></body></html>', 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);

    (new TestInjectThemeEditorScript)->inject($response);

    expect($response->getContent())
        ->toBe('<html><head><script id="preview-script">window.previewValue = "$1";</script><title>Store</title></head><body></body></html>');
});

it('preserves opening head attributes and casing when injecting', function () {
    $response = new Response('<html><HEAD data-theme="default" ><title>Store</title></HEAD><body></body></html>', 200, [
        'Content-Type' => 'text/html',
    ]);

    (new TestInjectThemeEditorScript)->inject($response);

    expect($response->getContent())
        ->toBe('<html><HEAD data-theme="default" ><script id="preview-script">window.previewValue = "$1";</script><title>Store</title></HEAD><body></body></html>');
});

it('does not inject into json responses', function () {
    $response = new JsonResponse(['html' => '<html><head></head><body></body></html>']);
    $originalContent = $response->getContent();

    (new TestInjectThemeEditorScript)->inject($response);

    expect($response->getContent())
        ->toBe($originalContent);
});

it('does not inject when the response has no head tag', function () {
    $response = new Response('<html><body></body></html>', 200, [
        'Content-Type' => 'text/html',
    ]);

    (new TestInjectThemeEditorScript)->inject($response);

    expect($response->getContent())
        ->toBe('<html><body></body></html>');
});

it('includes the resolved preview channel and locale in page data', function () {
    $themeEditor = Mockery::mock(ThemeEditor::class);
    $themeEditor->shouldReceive('getTemplateFromJsonViews')->with('index')->andReturn('index');
    $themeEditor->shouldReceive('jsonViews')->andReturn([]);
    $themeEditor->shouldReceive('preloadedModels')->andReturn([]);

    $themeSettingsLoader = Mockery::mock(ThemeSettingsLoader::class);
    $themeSettingsLoader->shouldReceive('loadActiveThemeSettings')->andReturn(new PropertyBag);

    $middleware = new PreviewContextInjectThemeEditorScript(
        $themeEditor,
        $themeSettingsLoader,
        Mockery::mock(CategoryRepository::class),
        Mockery::mock(ProductRepository::class),
        Mockery::mock(PreviewDataCollector::class),
    );

    $payload = $middleware->payload(['title' => 'Preview']);

    expect($payload)
        ->toHaveKey('channel', 'default')
        ->toHaveKey('locale', 'en')
        ->toHaveKey('blockSchemas', []);
});
