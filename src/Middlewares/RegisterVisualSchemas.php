<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Data\BlockSchema;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\SectionBlockSchemaFactory;
use BagistoPlus\Visual\Theme\Theme as VisualTheme;
use Closure;
use Craftile\Laravel\BlockSchemaRegistry;
use Craftile\Laravel\Facades\Craftile;
use Illuminate\Http\Request;

class RegisterVisualSchemas
{
    public function handle(Request $request, Closure $next)
    {
        $theme = themes()->current();

        if (! $theme instanceof VisualTheme) {
            return $next($request);
        }

        $mode = $theme->editorMode();

        if ($mode->isVisual()) {
            Craftile::registerDiscoveredSchemas();
        }

        if ($mode->isBagisto() && ThemeEditor::inDesignMode()) {
            $this->registerSectionSchemas($theme);
        }

        return $next($request);
    }

    /**
     * Register the schemas the editor draws a theme's sections with.
     *
     * Only while the editor is driving. Building a schema reads the fields of
     * the type it is built from, and a product carousel builds its filter
     * options from the catalogue; a storefront request renders its sections
     * through Bagisto and needs no schema at all.
     */
    protected function registerSectionSchemas(VisualTheme $theme): void
    {
        $registry = app(BlockSchemaRegistry::class);

        foreach (app(SectionBlockSchemaFactory::class)->forTheme($theme->code) as $schema) {
            /** @var BlockSchema $schema */
            $registry->register($schema);
        }
    }
}
