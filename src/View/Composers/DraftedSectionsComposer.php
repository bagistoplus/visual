<?php

namespace BagistoPlus\Visual\View\Composers;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Contracts\View\View;

/**
 * Draws the home page from the sections as the editor is holding them.
 *
 * The storefront is given what has been published, which is not what the
 * merchant is editing. Bagisto's own appearance preview has a route of its own
 * for this; the visual editor drives the storefront itself, so the swap happens
 * as the page is composed.
 */
class DraftedSectionsComposer
{
    public function __construct(protected SectionStore $store) {}

    public function compose(View $view): void
    {
        if (! ThemeEditor::inDesignMode()) {
            return;
        }

        $theme = themes()->current();

        if (! $theme instanceof Theme || ! $theme->editorMode()->isNative()) {
            return;
        }

        $view->with('sections', $this->store->draftedForPreview(
            (int) core()->getRequestedChannel()->id,
            $theme->code,
            app()->getLocale(),
        ));
    }
}
