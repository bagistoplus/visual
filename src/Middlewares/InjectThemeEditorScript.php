<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\ThemeDataCollector;
use BagistoPlus\Visual\ThemeEditor;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class InjectThemeEditorScript
{
    public function __construct(
        protected ThemeEditor $themeEditor,
        protected ThemeDataCollector $themeDataCollector,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
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
            ! $this->isHtmlResponse($response)
            || $response->getStatusCode() >= 500
            || Route::currentRouteName() === 'imagecache'
        ) {
            return $response;
        }

        if ($this->themeEditor->inDesignMode()) {
            $renderedSections = collect($this->themeEditor->renderedSections());

            $themeData = [
                'url' => $request->fullUrl(),

                'theme' => $this->themeEditor->activeTheme(),

                'channel' => core()->getRequestedChannelCode(),

                'locale' => core()->getRequestedLocaleCode(),

                'template' => $this->themeEditor->getTemplateForRoute(
                    $this->fixCategoryOrProductRoute(Route::currentRouteName())
                ),

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

                'sectionsData' => (object) $this->themeDataCollector->getSectionsData()->all(),

                'settings' => $this->themeDataCollector->getThemeSettings(),

                'source' => $this->themeEditor->renderingJsonView() ? encrypt($this->themeEditor->renderingJsonView()) : null,

                'haveEdits' => $this->checkIfHaveEdits(),
            ];

            /** @var \BagistoPlus\Visual\Theme\Theme */
            $theme = themes()->current();

            $editorScript = view('visual::admin.editor.injected-script', [
                'theme' => $this->getCurrentTheme(),
                'themeData' => $themeData,
                'templates' => $this->themeEditor->getTemplates(),
                'settingsSchema' => $this->translateSettingsSchema($theme->settingsSchema),
                'preloadedModels' => $this->themeEditor->preloadedModels(),
            ]);
        } else {
            $editorScript = view('visual::admin.editor.injected-script', [
                'theme' => $this->getCurrentTheme(),
            ]);
        }

        $content = str_replace('</body>', sprintf('%s</body>', $editorScript), $response->getContent());
        $response->setContent($content);

        return $response;
    }

    protected function checkIfHaveEdits(): bool
    {
        $theme = $this->themeEditor->activeTheme();
        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();

        $lastDeployFile = ThemePathsResolver::getThemeBaseDataPath($theme, 'editor/.last-deploy');

        if (! file_exists($lastDeployFile)) {
            return false;
        }

        $lastDeploy = filemtime($lastDeployFile);

        $themeJsonPath = ThemePathsResolver::getThemeBaseDataPath($theme, "editor/{$channel}/{$locale}/theme.json");

        if (! file_exists($themeJsonPath)) {
            return false;
        }

        $lastEdit = filemtime($themeJsonPath);

        return $lastEdit > $lastDeploy;
    }

    protected function getCurrentTheme()
    {

        return collect(themes()->current())
            ->only(['code', 'name', 'version']);
    }

    protected function translateSettingsSchema(array $settingsSchema): array
    {
        return collect($settingsSchema)->map(function ($group) {
            $group['name'] = trans($group['name']);

            $group['settings'] = collect($group['settings'])->map(function ($setting) {
                $setting['label'] = trans($setting['label']);
                $setting['info'] = trans($setting['info']);

                return $setting;
            })->all();

            return $group;
        })->all();
    }

    protected function isHtmlResponse($response)
    {
        if ($response instanceof JsonResponse) {
            return false;
        }

        return str_starts_with($response->headers->get('Content-Type'), 'text/html');
    }

    protected function fixCategoryOrProductRoute($routeName)
    {
        if ($routeName === 'shop.product_or_category.index') {
            $slug = urldecode(trim(request()->getPathInfo(), '/'));

            if ($this->categoryRepository->findBySlug($slug) !== null) {
                return 'shop.categories.index';
            } elseif ($this->productRepository->findBySlug($slug) !== null) {
                return 'shop.products.index';
            } else {
                return 'shop.error.index';
            }
        }

        return $routeName;
    }
}
