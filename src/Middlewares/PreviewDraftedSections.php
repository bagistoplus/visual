<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Theme\Theme as VisualTheme;
use Closure;
use Illuminate\Http\Request;

/**
 * Draws the sections Bagisto's layout owns from what the editor is holding.
 *
 * The footer and the services strip are drawn by Bagisto's own layout blades on
 * every page, and they ask their repository for published rows unless the
 * request is marked as previewing. Bagisto marks it in the one controller behind
 * its own preview page, which is all its editor ever shows. Visual drives the
 * whole storefront, so the mark belongs on any storefront request the editor is
 * driving: without it, browsing away from the home page shows the published
 * footer rather than the edited one, and Bagisto marks nothing for the editor to
 * select.
 */
class PreviewDraftedSections
{
    public function __construct(protected SectionStore $sections) {}

    public function handle(Request $request, Closure $next)
    {
        $theme = themes()->current();

        if (
            $theme instanceof VisualTheme
            && $theme->editorMode()->isBagisto()
            && ThemeEditor::inDesignMode()
        ) {
            $this->sections->previewDrafts();
        }

        return $next($request);
    }
}
