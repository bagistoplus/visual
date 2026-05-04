<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Theme\Theme as VisualTheme;
use Closure;
use Illuminate\Http\Request;
use Webkul\Shop\Http\Middleware\Theme;

class UseShopThemeFromRequest extends Theme
{
    public function handle($request, Closure $next)
    {
        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode()) {
            themes()->set(ThemeEditor::activeTheme());

            return $this->shareVisualTheme($request, $next);
        }

        return parent::handle($request, function ($request) use ($next) {
            return $this->shareVisualTheme($request, $next);
        });
    }

    protected function shareVisualTheme(Request $request, Closure $next)
    {
        $theme = themes()->current();

        if ($theme instanceof VisualTheme && $theme->isVisualTheme()) {
            view()->share('theme', $theme);
        }

        return $next($request);
    }
}
