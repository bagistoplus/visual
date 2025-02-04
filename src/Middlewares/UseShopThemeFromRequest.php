<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Closure;
use Webkul\Shop\Http\Middleware\Theme;

class UseShopThemeFromRequest extends Theme
{
    public function handle($request, Closure $next)
    {
        // session()->forget('_dispatched_flash');

        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode()) {
            app('themes')->set(ThemeEditor::activeTheme());

            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
