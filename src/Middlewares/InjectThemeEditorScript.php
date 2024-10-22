<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\ThemeDataCollector;
use BagistoPlus\Visual\ThemeEditor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InjectThemeEditorScript
{
    public function __construct(
        protected ThemeEditor $themeEditor,
        protected ThemeDataCollector $themeDataCollector
    ) {}

    /**
     * Inject theme editor metadata in the response
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->themeEditor->inDesignMode() && ! $this->themeEditor->inPreviewMode()) {
            return $next($request);
        }

        $response = $next($request);

        if (
            $response instanceof StreamedResponse
            || $response instanceof BinaryFileResponse
            || $response->getStatusCode() >= 500
            || Route::currentRouteName() === 'imagecache'
        ) {
            return $response;
        }

        // dd($response);

        if ($this->themeEditor->inDesignMode()) {
            $renderedSections = collect($this->themeEditor->renderedSections());

            $themeData = [
                'url' => $request->fullUrl(),

                'theme' => $this->themeEditor->activeTheme(),

                'channel' => app('core')->getRequestedChannelCode(),

                'locale' => app('core')->getRequestedLocaleCode(),

                'template' => $this->themeEditor->getTemplateForRoute(Route::currentRouteName()),

                'hasStaticContent' => $renderedSections->filter(function ($item) {
                    return in_array($item['group'], ['beforeContent', 'afterContent']);
                })->isNotEmpty(),

                'sectionsOrder' => $renderedSections->where('group', 'content')->pluck('id'),

                'beforeContentSectionsOrder' => $renderedSections
                    ->where('group', 'beforeTemplate')
                    ->merge($renderedSections->where('group', 'beforeContent'))
                    ->pluck('id'),

                'afterContentSectionsOrder' => $renderedSections
                    ->where('group', 'afterContent')
                    ->merge($renderedSections->where('group', 'afterTemplate'))
                    ->pluck('id'),

                'sectionsData' => $this->themeDataCollector->getSectionsData()->all(),

                'settings' => $this->themeDataCollector->getThemeSettings(),

                'templateDataPath' => $this->themeEditor->renderingJsonView(),
            ];

            $editorScript = view('visual::admin.editor.injected-script', [
                'theme' => $this->themeEditor->activeTheme(),
                'themeData' => $themeData,
                'settingsSchema' => app('themes')->current()->settingsSchema,
            ]);
        } else {
            $editorScript = view('visual::admin.editor.injected-script', [
                'theme' => $this->themeEditor->activeTheme(),
            ]);
        }

        $content = str_replace('</body>', sprintf('%s</body>', $editorScript), $response->getContent());
        $response->setContent($content);

        return $response;
    }
}
