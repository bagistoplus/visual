<?php

use BagistoPlus\Visual\Http\Controllers\Shop\TemplatePreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/checkout-success', [TemplatePreviewController::class, 'checkoutSuccess'])
    ->name('visual.template-preview.checkout-success');
