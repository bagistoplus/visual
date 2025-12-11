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
     *
     * @param  string  $url  The URL to render
     * @param  array|null  $blockIds  Optional array of block IDs to render (null = render all)
     */
    public function execute(string $url, ?array $blockIds = null): string
    {
        $baseUrl = rtrim(config('app.url'));
        $basePath = parse_url($baseUrl, PHP_URL_PATH);

        // Handle subdirectory installs by redirecting
        if ($basePath !== null) {
            return redirect($url);
        }

        // Append _blocks query parameter if specific blocks are requested
        if ($blockIds !== null && count($blockIds) > 0) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator.'_blocks='.implode(',', $blockIds);
        }

        // Reset Craftile's preview mode cache before sub-request
        Craftile::detectPreviewUsing(function () {
            return \BagistoPlus\Visual\Facades\ThemeEditor::inDesignMode();
        });

        app(JsonViewParser::class)->clearCache();
        app(ThemeSettingsLoader::class)->clearCache();

        $request = Request::create($url, 'GET');
        $response = app()->handle($request);

        return $response->getContent();
    }
}
