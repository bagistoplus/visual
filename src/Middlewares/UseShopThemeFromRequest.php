<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Closure;
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

            return $next($request);
        }

        return parent::handle($request, $next);
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
}
