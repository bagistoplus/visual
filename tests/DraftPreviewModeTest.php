<?php

use BagistoPlus\Visual\Facades\ThemeEditor as ThemeEditorFacade;
use BagistoPlus\Visual\Middlewares\InjectThemeEditorScript;
use BagistoPlus\Visual\Support\EditorInheritanceMetadata;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use BagistoPlus\Visual\View\DraftAwareJsonViewParser;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\PreviewDataCollector;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

function draftPreviewRequest(array $query = [], array $server = []): Request
{
    $request = Request::create('/', 'GET', $query, [], [], $server);

    app()->instance('request', $request);
    app('url')->setRequest($request);

    return $request;
}

it('detects draft preview mode from the query parameter and the header', function (array $query, array $server) {
    draftPreviewRequest($query, $server);

    expect(ThemeEditorFacade::inDraftPreviewMode())->toBeTrue()
        ->and(ThemeEditorFacade::activeTheme())->toBe('fake-theme')
        ->and(ThemeEditorFacade::usesEditorData())->toBeTrue()
        ->and(ThemeEditorFacade::inDesignMode())->toBeFalse()
        ->and(ThemeEditorFacade::inPreviewMode())->toBeFalse();
})->with([
    'query' => [['_draftPreview' => 'fake-theme'], []],
    'header' => [[], ['HTTP_X_VISUAL_DRAFT_PREVIEW_THEME' => 'fake-theme']],
]);

it('does not read editor data for a plain storefront request', function () {
    draftPreviewRequest();

    expect(ThemeEditorFacade::inDraftPreviewMode())->toBeFalse()
        ->and(ThemeEditorFacade::usesEditorData())->toBeFalse();
});

it('keeps the theme preview mode reading live data', function () {
    draftPreviewRequest(['_previewMode' => 'fake-theme']);

    expect(ThemeEditorFacade::inPreviewMode())->toBeTrue()
        ->and(ThemeEditorFacade::usesEditorData())->toBeFalse();
});

it('leaves craftile preview off so no editor script or attribute is emitted', function () {
    draftPreviewRequest(['_draftPreview' => 'fake-theme']);
    Craftile::detectPreviewUsing(fn () => ThemeEditorFacade::inDesignMode());

    expect(Craftile::inPreview())->toBeFalse();

    $response = new Response('<html><head></head><body></body></html>', 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);

    $middleware = new InjectThemeEditorScript(
        app(ThemeEditor::class),
        Mockery::mock(ThemeSettingsLoader::class),
        Mockery::mock(CategoryRepository::class),
        Mockery::mock(ProductRepository::class),
        Mockery::mock(PreviewDataCollector::class),
        app(EditorInheritanceMetadata::class),
    );

    $handled = $middleware->handle(request(), fn () => $response);

    expect($handled->getContent())->toBe('<html><head></head><body></body></html>');
});

it('propagates the draft preview parameter onto generated urls', function () {
    Route::get('/draft-preview-target', fn () => '')->name('draft-preview.target');
    Route::getRoutes()->refreshNameLookups();

    draftPreviewRequest(['_draftPreview' => 'fake-theme']);

    expect(route('draft-preview.target'))->toContain('_draftPreview=fake-theme')
        ->and(url('/cart'))->toContain('_draftPreview=fake-theme');
});

it('swaps craftile json view parser for the draft aware one', function () {
    expect(app(JsonViewParser::class))->toBeInstanceOf(DraftAwareJsonViewParser::class);
});

it('re-parses the template on every draft preview read so parent edits are picked up', function () {
    $path = sys_get_temp_dir().'/visual-draft-preview-'.uniqid().'/index.json';
    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode(['blocks' => [], 'regions' => []]));

    draftPreviewRequest(['_draftPreview' => 'fake-theme']);
    Craftile::detectPreviewUsing(fn () => ThemeEditorFacade::inDesignMode());

    $parser = app(JsonViewParser::class);
    $parser->clearCache();

    $calls = 0;
    Craftile::normalizeTemplateUsing(function (array $data) use (&$calls) {
        $calls++;

        return $data;
    });

    $parser->parse($path);
    $parser->parse($path);

    expect($calls)->toBe(2);

    File::deleteDirectory(dirname($path));
});
