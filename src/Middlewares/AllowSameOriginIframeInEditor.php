<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Closure;

class AllowSameOriginIframeInEditor
{
    public function handle($request, Closure $next)
    {
        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode()) {
            $response = $next($request);

            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

            return $response;
        }

        return $next($request);
    }
}
