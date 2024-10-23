<?php

namespace BagistoPlus\Visual\Facades;

use BagistoPlus\Visual\ThemeEditor as ThemeEditorManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool active()
 * @method static bool inDesignMode()
 * @method static string activeTheme()
 * @method static bool inPreviewMode()
 * @method static mixed renderingView(string|null $view = '')
 * @method static mixed renderingJsonView(string|null $view = '')
 * @method static mixed startRenderingLayout()
 * @method static mixed startRenderingTemplate()
 * @method static mixed stopRenderingTemplate()
 * @method static mixed startRenderingContent()
 * @method static mixed stopRenderingContent()
 * @method static mixed collectRenderedSection($type, $viewType, $viewName, $id = null)
 * @method static mixed renderedSections()
 * @method static mixed registerTemplate(\BagistoPlus\Visual\Support\Template $template)
 * @method static mixed getTemplates()
 * @method static mixed getTemplateForRoute(string $routeName)
 *
 * @see \BagistoPlus\Visual\ThemeEditor
 */
class ThemeEditor extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ThemeEditorManager::class;
    }
}
