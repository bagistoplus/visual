<?php

use BagistoPlus\Visual\Http\Controllers\Admin\ThemeEditorController;
use BagistoPlus\Visual\Http\Controllers\Admin\ThemesController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('/visual/editor')->group(function () {
        Route::post('api/persist', [ThemeEditorController::class, 'persistTheme'])
            ->name('visual.admin.editor.api.persist');

        Route::post('api/upload', [ThemeEditorController::class, 'uploadImages'])
            ->name('visual.admin.editor.api.upload');

        Route::get('api/images', [ThemeEditorController::class, 'listImages'])
            ->name('visual.admin.editor.api.images');

        Route::get('api/cms-pages', [ThemeEditorController::class, 'cmsPages'])
            ->name('visual.admin.editor.api.cms_pages');

        Route::get('{theme}/{path?}', [ThemeEditorController::class, 'index'])
            ->where('path', '.*')
            ->name('visual.admin.editor');
    });

    Route::prefix('/visual/themes')->group(function () {
        Route::get('/', [ThemesController::class, 'index'])
            ->name('visual.admin.themes.index');
    });
});
