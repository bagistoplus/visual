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

        // Handle subdirectory installs by extracting relative path
        $baseUrl = rtrim(config('app.url'));
        $basePath = parse_url($baseUrl, PHP_URL_PATH);

        if ($basePath !== null) {
            // Remove base path from URL to get relative path
            $parsedUrl = parse_url($url);
            $fullPath = $parsedUrl['path'] ?? '/';

            if (str_starts_with($fullPath, $basePath)) {
                $relativePath = substr($fullPath, strlen($basePath)) ?: '/';
            } else {
                $relativePath = $fullPath;
            }

            parse_str($parsedUrl['query'] ?? '', $queryParams);
            $request = Request::create($relativePath, 'GET', $queryParams);
        } else {
            $request = Request::create($url, 'GET');
        }

        $response = app()->handle($request);

        return $response->getContent();
    }
}
