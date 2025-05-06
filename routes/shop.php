<?php

use BagistoPlus\Visual\Http\Controllers\Shop\TemplatePreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('/visual/template-preview')
    ->middleware(['web', 'locale', 'theme', 'currency'])
    ->group(function () {

        Route::get('/checkout-success', [TemplatePreviewController::class, 'checkoutSuccess'])
            ->name('visual.template-preview.checkout-success');
    });
