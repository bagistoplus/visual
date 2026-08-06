<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Support\EditorBlockSchemaSerializer;
use BagistoPlus\Visual\Support\EditorInheritanceMetadata;
use BagistoPlus\Visual\Support\EditorTranslationReferenceCollector;
use BagistoPlus\Visual\ThemeEditor;
use BagistoPlus\Visual\ThemeSettingsLoader;
use Closure;
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
        protected PreviewDataCollector $previewCollector,
        protected EditorInheritanceMetadata $editorInheritanceMetadata,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        app(EditorTranslationReferenceCollector::class)->reset();

        try {
            return parent::handle($request, $next);
        } finally {
            app(EditorTranslationReferenceCollector::class)->reset();
        }
    }

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
        $scripts = $this->buildPreviewScriptsForRequest($pageData, $request);

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
    protected function buildPreviewScriptsForRequest(array $pageData, Request $request): string
    {
        $routeTemplate = $this->themeEditor->getTemplateForRoute(
            $this->fixCategoryOrProductRoute(Route::currentRouteName())
        );

        return view()->make('visual::admin.editor.injected-script', [
            'pageData' => $this->buildInjectedPageDataForRequest($pageData, $routeTemplate, $request),
        ])->render();
    }

    protected function buildInjectedPageDataForRequest(array $pageData, $routeTemplate, Request $request): array
    {
        $includePageLoadMetadata = ! $request->query->has('_visual_render');
        $settingsBag = $includePageLoadMetadata
            ? $this->themeSettingsLoader->loadActiveThemeSettings()
            : null;

        return $this->buildInjectedPageData(
            $pageData,
            $routeTemplate,
            $settingsBag,
            $includePageLoadMetadata,
        );
    }

    protected function buildInjectedPageData(
        array $pageData,
        $routeTemplate,
        ?PropertyBag $settingsBag,
        bool $includePageLoadMetadata = true,
    ): array {
        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();
        $template = $this->themeEditor->getTemplateFromJsonViews($routeTemplate);

        $payload = [
            'content' => $pageData,
            'template' => [
                'url' => request()->fullUrl(),
                'name' => $template,
                'sources' => encrypt($this->themeEditor->jsonViews()),
            ],
            'channel' => $channel,
            'locale' => $locale,
            // Page-load metadata; autosaves do not refresh it until the next page-data event.
            'localeInheritance' => $this->editorInheritanceMetadata->localeInheritanceForTemplate(
                themes()->current()->code,
                $channel,
                $template
            ),
            'translationReferences' => app(EditorTranslationReferenceCollector::class)
                ->forBlockIds(array_keys($pageData['blocks'] ?? [])),
            'preloadedModels' => $this->themeEditor->preloadedModels(),
        ];

        if ($includePageLoadMetadata) {
            $payload['blockSchemas'] = app(EditorBlockSchemaSerializer::class)->all();
            $payload['settings'] = $settingsBag?->toArray() ?? [];
        }

        return $payload;
    }

    protected function getCurrentTheme()
    {

        return collect(themes()->current())
            ->only(['code', 'name', 'version']);
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
