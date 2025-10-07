<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\ThemeEditor;
use Craftile\Laravel\Middlewares\PreviewScriptMiddleware;
use Craftile\Laravel\PreviewDataCollector;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class InjectThemeEditorScript extends PreviewScriptMiddleware
{
    public function __construct(
        protected ThemeEditor $themeEditor,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected PreviewDataCollector $previewCollector
    ) {}

    /**
     * Build the scripts to inject for preview functionality.
     */
    protected function buildPreviewScripts(array $pageData): string
    {
        return view('visual::admin.editor.injected-script', [
            'pageData' => [
                'content' => $pageData,
                'template' => [
                    'url' => request()->fullUrl(),
                    'name' => $this->themeEditor->getTemplateForRoute(
                        $this->fixCategoryOrProductRoute(Route::currentRouteName())
                    ),
                    'sources' => encrypt($this->themeEditor->jsonViews()),
                ],
            ],
        ])->render();
    }

    protected function checkIfHaveEdits(): bool
    {
        parent::__construct($this->previewCollector);
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
