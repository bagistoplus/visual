<?php

namespace BagistoPlus\Visual\Persistence;

use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\Facades\Craftile;
use Craftile\Laravel\View\JsonViewParser;
use Illuminate\Http\Request;

class RenderPreview
{
    /**
     * Render a preview by making a sub-request with design mode enabled.
     */
    public function execute(string $url): string
    {
        // Reset Craftile's preview mode cache before sub-request
        Craftile::detectPreviewUsing(function () {
            return \BagistoPlus\Visual\Facades\ThemeEditor::inDesignMode();
        });

        app(JsonViewParser::class)->clearCache();
        app(ThemeSettingsLoader::class)->clearCache();

        // Handle subdirectory installs by redirecting
        $baseUrl = rtrim(config('app.url'));
        $basePath = parse_url($baseUrl, PHP_URL_PATH);

        if ($basePath !== null) {
            return redirect($url);
        }

        $request = Request::create($url, 'GET');
        $response = app()->handle($request);

        return $response->getContent();
    }
}
