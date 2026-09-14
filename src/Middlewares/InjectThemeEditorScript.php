<?php

namespace BagistoPlus\Visual\Middlewares;

use BagistoPlus\Visual\Facades\ThemePathsResolver;
use BagistoPlus\Visual\Persistence\SectionStore;
use BagistoPlus\Visual\Support\EditorBlockSchemaSerializer;
use BagistoPlus\Visual\Support\EditorInheritanceMetadata;
use BagistoPlus\Visual\Support\EditorTranslationReferenceCollector;
use BagistoPlus\Visual\Support\SectionPageBuilder;
use BagistoPlus\Visual\Theme\Theme as VisualTheme;
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
    /**
     * The one page a theme's content sections are drawn on.
     */
    protected const HOME_ROUTE = 'shop.home.index';

    public function __construct(
        protected ThemeEditor $themeEditor,
        protected ThemeSettingsLoader $themeSettingsLoader,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected PreviewDataCollector $previewCollector,
        protected EditorInheritanceMetadata $editorInheritanceMetadata,
        protected SectionStore $sectionStore,
        protected SectionPageBuilder $sectionPageBuilder,
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
     * The page the editor arranges.
     *
     * A visual theme renders its blocks, and the collector reports what was
     * rendered. A Bagisto theme renders sections Craftile knows nothing about,
     * so its page is read from the rows they are stored as instead.
     */
    protected function getCurrentPageData(): array
    {
        $theme = themes()->current();

        if ($theme instanceof VisualTheme && $theme->editorMode()->isBagisto()) {
            return $this->sectionPageData($theme);
        }

        return parent::getCurrentPageData();
    }

    /**
     * Whether the page being previewed draws the content sections.
     *
     * Bagisto's home page draws them and no other page does, so browsing the
     * storefront in the preview shows the layout sections alone, which is what
     * is actually on screen.
     *
     * A request that matched no named route is read as the home page rather
     * than as another one: hiding sections that are on screen is the worse of
     * the two mistakes.
     */
    protected function drawsContentSections(): bool
    {
        $route = Route::currentRouteName();

        return $route === null || $route === self::HOME_ROUTE;
    }

    /**
     * @return array{blocks: array<string, array<string, mixed>>, regions: array<int, array<string, mixed>>}
     */
    protected function sectionPageData(VisualTheme $theme): array
    {
        $records = $this->sectionStore->records(
            (int) core()->getRequestedChannel()->id,
            $theme->code,
            core()->getRequestedLocaleCode(),
        );

        return $this->sectionPageBuilder->build($records, $theme->code, $this->drawsContentSections());
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

        if (($count = $this->draftCount()) !== null) {
            $payload['draftCount'] = $count;
        }

        if ($includePageLoadMetadata) {
            $payload['blockSchemas'] = app(EditorBlockSchemaSerializer::class)->all();
            $payload['settings'] = $settingsBag?->toArray() ?? [];
        }

        return $payload;
    }

    /**
     * How much publishing or discarding would carry, so that the editor can say
     * so the way Bagisto's own appearance editor does: one for each section
     * holding a change, and one more for the theme settings.
     *
     * A visual theme holds its edits in files rather than rows and has nothing
     * to count, so it is told nothing.
     */
    protected function draftCount(): ?int
    {
        $theme = themes()->current();

        if (! $theme instanceof VisualTheme || ! $theme->editorMode()->isBagisto()) {
            return null;
        }

        $sections = $this->sectionStore->countDrafts(
            (int) core()->getRequestedChannel()->id,
            $theme->code,
        );

        return $sections + ($this->hasSettingsEdits($theme->code) ? 1 : 0);
    }

    /**
     * Whether the theme settings are holding a change the storefront has not
     * been given yet, which the editor data store marks as it saves.
     */
    protected function hasSettingsEdits(string $themeCode): bool
    {
        return file_exists(ThemePathsResolver::getThemeBaseDataPath($themeCode, 'editor/.last-edit'));
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
