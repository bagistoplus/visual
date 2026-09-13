<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Theme\Theme;
use Closure;
use Illuminate\Http\Request;

/**
 * The customize button of Bagisto's theme gallery always points at its own section editor.
 * Send the themes that are customized in the visual editor there instead.
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

        if (! $code) {
            return null;
        }

        $attributes = config("themes.shop.{$code}");

        if (! $attributes) {
            return null;
        }

        return Theme::make(['code' => $code] + $attributes)->usesVisualEditor() ? $code : null;
    }
}
