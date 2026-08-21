<?php

namespace BagistoPlus\Visual\Middlewares;

use Closure;
use Illuminate\Http\Request;

/**
 * The customize button of Bagisto's theme gallery always points at its own section editor,
 * which knows nothing about visual themes. Send those to the visual editor instead.
 */
class RedirectVisualThemeCustomization
{
    public function handle(Request $request, Closure $next)
    {
        $code = $this->visualThemeBeingCustomized($request);

        if (! $code) {
            return $next($request);
        }

        return redirect()->route('visual.admin.editor', ['theme' => $code]);
    }

    protected function visualThemeBeingCustomized(Request $request): ?string
    {
        if (! $request->isMethod('GET')) {
            return null;
        }

        $adminPrefix = trim((string) config('app.admin_url'), '/');
        $pattern = ($adminPrefix === '' ? '' : $adminPrefix.'/').'appearance/themes/*/sections';

        if (! $request->is($pattern)) {
            return null;
        }

        $segments = explode('/', trim($request->decodedPath(), '/'));
        $code = $segments[count($segments) - 2] ?? null;

        if (! $code || ! config("themes.shop.{$code}.visual_theme", false)) {
            return null;
        }

        return $code;
    }
}
