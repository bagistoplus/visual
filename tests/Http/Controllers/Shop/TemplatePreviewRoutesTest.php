<?php

use BagistoPlus\Visual\Http\Controllers\Shop\TemplatePreviewController;
use Illuminate\Support\Facades\Route;

it('registers the error template preview route', function () {
    $route = Route::getRoutes()->getByName('visual.template-preview.error');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('visual/template-preview/error')
        ->and($route->getActionName())->toBe(TemplatePreviewController::class.'@error');
});
