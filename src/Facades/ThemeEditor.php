<?php

namespace BagistoPlus\Visual\Facades;

use BagistoPlus\Visual\ThemeEditor as ThemeEditorManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void serving(\Closure $callback)
 * @method static bool active()
 * @method static bool inDesignMode()
 * @method static string activeTheme()
 * @method static bool inPreviewMode()
 * @method static void addJsonView(string $path)
 * @method static array jsonViews()
 * @method static mixed registerTemplate(\BagistoPlus\Visual\Data\Template $template)
 * @method static mixed getTemplates()
 * @method static mixed getTemplateForRoute(string $routeName)
 * @method static void preloadModel(string $type, $model)
 * @method static array preloadedModels()
 * @method static void assets(string $buildDirectory, string|null $manifestFilename = 'manifest.json')
 * @method static void script(array|string $scripts)
 * @method static void style(array|string $styles)
 * @method static \Illuminate\Support\HtmlString renderScripts()
 * @method static \Illuminate\Support\HtmlString renderStyles()
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
