<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Theme\Theme as VisualTheme;
use Closure;
use Illuminate\Http\Request;
use Webkul\Core\Models\Channel;
use Webkul\Shop\Http\Middleware\Theme;

class UseShopThemeFromRequest extends Theme
{
    public function handle($request, Closure $next)
    {
        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode() || ThemeEditor::inDraftPreviewMode()) {
            $code = ThemeEditor::activeTheme();

            themes()->set($code);
            $this->showThemeOnChannel($code);

            return $this->shareVisualTheme($request, $next);
        }

        return parent::handle($request, function ($request) use ($next) {
            return $this->shareVisualTheme($request, $next);
        });
    }

    /**
     * Bagisto reads a theme's sections by the theme the channel names, on the
     * home page and in the layout, so a theme drawn in place of the channel's
     * is named on it for this request, the way Bagisto's own preview does.
     */
    protected function showThemeOnChannel(string $code): void
    {
        $channel = core()->getCurrentChannel();

        if (! $channel instanceof Channel || $channel->theme === $code) {
            return;
        }

        $previewed = clone $channel;
        $previewed->theme = $code;

        core()->setCurrentChannel($previewed);
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
