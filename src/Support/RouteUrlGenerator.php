<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Routing\RouteUrlGenerator as RoutingRouteUrlGenerator;

class RouteUrlGenerator extends RoutingRouteUrlGenerator
{
    /**
     * Get the query string for a given route.
     *
     * @return string
     */
    protected function getRouteQueryString(array $parameters)
    {
        if (ThemeEditor::inDesignMode()) {
            $parameters['_designMode'] = ThemeEditor::activeTheme();
        } elseif (ThemeEditor::inPreviewMode()) {
            $parameters['_previewMode'] = ThemeEditor::activeTheme();
        }

        return parent::getRouteQueryString($parameters);
    }
}
