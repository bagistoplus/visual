<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Events\ServingThemeEditor;
use BagistoPlus\Visual\Support\Template;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ThemeEditor
{
    protected string $renderingLayout = '';

    protected string $renderingView = '';

    protected string $renderingJsonView = '';

    protected string $renderingSectionGroup = 'beforeContent';

    protected array $renderedSections = [];

    protected array $templates = [];

    protected array $assetGroups = [];

    protected array $scripts = [];

    protected array $styles = [];

    protected array $preloadedModels = [
        'categories' => [],
        'products' => [],
        'cms_pages' => [],
    ];

    /**
     * Register an event listerner for ServingThemeEditor event
     */
    public function serving(\Closure $callback): void
    {
        Event::listen(ServingThemeEditor::class, $callback);
    }

    public function active(): bool
    {
        return $this->inDesignMode();
    }

    public function inDesignMode(): bool
    {
        return request()->query->has('_designMode') || request()->headers->has('x-visual-editor-theme');
    }

    public function activeTheme(): string
    {
        if (self::inDesignMode()) {
            return request()->query->get('_designMode', request()->headers->get('x-arcade-editor-theme'));
        }

        return request()->query->get('_previewMode', request()->headers->get('x-arcade-preview-theme'));
    }

    public function inPreviewMode(): bool
    {
        return request()->query->has('_previewMode') || request()->headers->has('x-visual-preview-theme');
    }

    public function renderingView(?string $view = '')
    {
        if ($view) {
            $this->renderingView = $view;
        }

        return $this->renderingView;
    }

    public function renderingJsonView(?string $view = '')
    {
        if ($view) {
            $this->renderingJsonView = $view;
        }

        return $this->renderingJsonView;
    }

    public function startRenderingLayout()
    {
        $this->renderingSectionGroup = 'beforeTemplate';
    }

    public function startRenderingTemplate()
    {
        $this->renderingSectionGroup = 'beforeContent';
    }

    public function stopRenderingTemplate()
    {
        $this->renderingSectionGroup = 'afterTemplate';
    }

    public function startRenderingContent()
    {
        $this->renderingSectionGroup = 'content';
    }

    public function stopRenderingContent()
    {
        $this->renderingSectionGroup = 'afterContent';
    }

    public function collectRenderedSection($type, $viewType, $viewName, $id = null)
    {
        if ($viewType === 'layouts' && ! $this->renderingLayout) {
            $this->startRenderingLayout();
            $this->renderingLayout = $viewName;
        }

        $this->renderedSections[] = [
            'id' => $id ?? $type,
            'group' => $this->renderingSectionGroup,
        ];
    }

    public function renderedSections()
    {
        return $this->renderedSections;
    }

    public function registerTemplate(Template $template)
    {
        $template->icon = svg($template->icon, ['class' => 'w-4 h-4'])->toHtml();
        $this->templates[] = $template;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function getTemplateForRoute(string $routeName)
    {
        $template = collect($this->templates)
            ->firstWhere(fn ($template) => $template->matchRoute($routeName));

        return $template ? $template->template : Str::of($routeName)->slug();
    }

    public function preloadModel(string $type, $model): void
    {
        $this->preloadedModels[$type][] = $model;
    }

    public function preloadedModels(): array
    {
        return $this->preloadedModels;
    }

    /**
     * Collect Vite asset(s) with specific config.
     */
    public function assets(string $buildDirectory, ?string $manifestFilename = 'manifest.json'): void
    {
        $manifestPath = public_path($buildDirectory.'/'.$manifestFilename);

        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        foreach ($manifest as $entry) {
            $url = asset($buildDirectory.'/'.$entry['file']);

            if (Str::of($url)->endsWith('.js')) {
                $this->script($url);
            } elseif (Str::of($url)->endsWith('.css')) {
                $this->style($url);
            }
        }

        /* if (! app()->isProduction()) {
            $manifestUrl = asset($buildDirectory.'/'.$manifestFilename);
            $reloadScript = <<<HTML
<script>
(function() {
    let lastManifest = '';
    setInterval(async () => {
        try {
            const res = await fetch("{$manifestUrl}", { cache: 'no-store' });
            const text = await res.text();

            if (!lastManifest) {
                lastManifest = text;
                return;
            }

            if (text !== lastManifest) {
                location.reload();
            }
        } catch (e) {
            console.warn('Reload check failed', e);
        }
    }, 2000);
})();
</script>
HTML;

            $this->script($reloadScript);
        } */
    }

    /**
     * Collect js scripts
     */
    public function script(string|array $scripts): void
    {
        $this->scripts = array_merge($this->scripts, (array) $scripts);
    }

    /**
     * Collect css styles
     */
    public function style(string|array $styles): void
    {
        $this->styles = array_merge($this->styles, (array) $styles);
    }

    /**
     * Render all collected scripts.
     */
    public function renderScripts(): HtmlString
    {
        $output = '';

        foreach (array_unique($this->scripts) as $script) {
            if (str_starts_with($script, '<script')) {
                $output .= $script;
            } else {
                $output .= '<script defer src="'.$script.'"></script>';
            }
        }

        return new HtmlString($output);
    }

    /**
     * Render all collected styles.
     */
    public function renderStyles(): HtmlString
    {
        $output = '';

        foreach (array_unique($this->styles) as $style) {

            $output .= '<link rel="stylesheet" type="text/css" href="'.$style.'">';
        }

        return new HtmlString($output);
    }
}
