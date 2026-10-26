<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Closure;

/**
 * Lets the editor draw the storefront in its frame.
 *
 * Asked of the request alone, not of who is making it: this runs before the
 * session is started, and framing discloses nothing on its own. What the page
 * is allowed to say is decided later, where the viewer is known.
 */
class AllowSameOriginIframeInEditor
{
    public function handle($request, Closure $next)
    {
        if (ThemeEditor::designModeRequested() || ThemeEditor::previewModeRequested()) {
            $response = $next($request);

            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

            return $response;
        }

        return $next($request);
    }
}
