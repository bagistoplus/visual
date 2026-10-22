<?php

use BagistoPlus\Visual\Middlewares\RepairPreviewQueryString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

function repairPreviewQuery(string $uri): Request
{
    $request = Request::create($uri);

    app(RepairPreviewQueryString::class)->handle($request, fn (Request $request) => $request);

    return $request;
}

it('splits a query appended with a second question mark out of the preview theme', function () {
    $request = repairPreviewQuery('/stripe/success?_draftPreview=visual-debut?session_id=cs_test_123');

    expect($request->query('_draftPreview'))->toBe('visual-debut')
        ->and($request->input('session_id'))->toBe('cs_test_123')
        ->and($request->getQueryString())->toBe('_draftPreview=visual-debut&session_id=cs_test_123');
});

it('repairs the design and preview mode keys too', function (string $key) {
    $request = repairPreviewQuery("/stripe/cancel?{$key}=visual-debut?session_id=cs_test_123&page=2");

    expect($request->query($key))->toBe('visual-debut')
        ->and($request->query('session_id'))->toBe('cs_test_123')
        ->and($request->query('page'))->toBe('2');
})->with(['_designMode', '_previewMode']);

it('keeps a param the url already carries over the appended one', function () {
    $request = repairPreviewQuery('/search?query=shoes&_draftPreview=visual-debut?query=hats');

    expect($request->query('query'))->toBe('shoes')
        ->and($request->query('_draftPreview'))->toBe('visual-debut');
});

it('leaves a well formed query untouched', function () {
    $request = repairPreviewQuery('/stripe/success?session_id=cs_test_123&_draftPreview=visual-debut');

    expect($request->query('_draftPreview'))->toBe('visual-debut')
        ->and($request->getQueryString())->toBe('_draftPreview=visual-debut&session_id=cs_test_123');
});

it('keeps signed urls that carry a preview param valid', function () {
    Route::get('/withdraw/{order}', fn () => 'ok')->name('repair-preview.signed')->middleware('signed');
    Route::getRoutes()->refreshNameLookups();

    $url = URL::signedRoute('repair-preview.signed', ['order' => 1, '_draftPreview' => 'visual-debut']);

    expect($url)->toContain('_draftPreview=visual-debut');

    $this->get($url)->assertOk();
});

it('leaves requests outside the editor and previews untouched', function () {
    $request = repairPreviewQuery('/stripe/success?session_id=cs_test_123?x=1');

    expect($request->query('session_id'))->toBe('cs_test_123?x=1');
});
