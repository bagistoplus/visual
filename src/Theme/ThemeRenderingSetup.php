<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\SectionBlockSchemaFactory;
use Craftile\Laravel\BlockSchemaRegistry;
use Craftile\Laravel\Facades\Craftile;

/**
 * Makes the active theme renderable wherever it was activated, including
 * requests that skip the shop middleware, such as a missing image under the
 * image cache drawing the shop's error page.
 */
class ThemeRenderingSetup
{
    public function prepare(Theme $theme): void
    {
        if ($theme->editorMode()->isVisual()) {
            Craftile::registerDiscoveredSchemas();
            view()->share('theme', $theme);

            return;
        }

        if ($theme->editorMode()->isBagisto() && ThemeEditor::inDesignMode()) {
            $this->registerSectionSchemas($theme);
        }
    }

    /**
     * Register the schemas the editor draws a theme's sections with.
     */
    protected function registerSectionSchemas(Theme $theme): void
    {
        $registry = app(BlockSchemaRegistry::class);

        foreach (app(SectionBlockSchemaFactory::class)->forTheme($theme->code) as $schema) {
            /** @var BlockSchema $schema */
            $registry->register($schema);
        }
    }
}
