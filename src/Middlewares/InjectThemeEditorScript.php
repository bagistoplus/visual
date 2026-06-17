<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Support\EditorBlockSchemaSerializer;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Craftile\Laravel\Middlewares\PreviewScriptMiddleware;
use Craftile\Laravel\PreviewDataCollector;
use Craftile\Laravel\PropertyBag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class InjectThemeEditorScript extends PreviewScriptMiddleware
{
    public function __construct(
        protected ThemeEditor $themeEditor,
        protected ThemeSettingsLoader $themeSettingsLoader,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected PreviewDataCollector $previewCollector
    ) {}

    /**
     * Inject preview client and page data scripts into the response.
     */
    protected function injectPreviewScripts(Response $response, Request $request): void
    {
        if (! $this->isHtmlResponse($response)) {
            return;
        }

        $content = $response->getContent();
        if (! $content || ! preg_match('/<head\b[^>]*>/i', $content)) {
            return;
        }

        $pageData = $this->getCurrentPageData();
        $scripts = $this->buildPreviewScripts($pageData);

        $content = preg_replace_callback(
            '/<head\b[^>]*>/i',
            fn (array $matches) => $matches[0].$scripts,
            $content,
            1
        );

        $response->setContent($content);
    }

    /**
     * Build the scripts to inject for preview functionality.
     */
    protected function buildPreviewScripts(array $pageData): string
    {
        $settingsBag = $this->themeSettingsLoader->loadActiveThemeSettings();
        $routeTemplate = $this->themeEditor->getTemplateForRoute(
            $this->fixCategoryOrProductRoute(Route::currentRouteName())
        );

        return view()->make('visual::admin.editor.injected-script', [
            'pageData' => $this->buildInjectedPageData($pageData, $routeTemplate, $settingsBag),
        ])->render();
    }

    protected function buildInjectedPageData(array $pageData, $routeTemplate, PropertyBag $settingsBag): array
    {
        return [
            'content' => $pageData,
            'template' => [
                'url' => request()->fullUrl(),
                'name' => $this->themeEditor->getTemplateFromJsonViews($routeTemplate),
                'sources' => encrypt($this->themeEditor->jsonViews()),
            ],
            'channel' => core()->getRequestedChannelCode(),
            'locale' => core()->getRequestedLocaleCode(),
            'blockSchemas' => app(EditorBlockSchemaSerializer::class)->all(),
            'settings' => $settingsBag->toArray(),
            'preloadedModels' => $this->themeEditor->preloadedModels(),
        ];
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
