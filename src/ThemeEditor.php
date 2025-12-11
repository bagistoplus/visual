<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Data\Template;
use BagistoPlus\Visual\Events\ServingThemeEditor;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ThemeEditor
{
    protected array $templates = [];

    protected array $scripts = [];

    protected array $styles = [];

    protected array $jsonViews = [];

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
        return $this->inDesignMode() || request()->is('*/visual/editor/*');
    }

    public function inDesignMode(): bool
    {
        return request()->query->has('_designMode') || request()->headers->has('x-visual-editor-theme');
    }

    public function activeTheme(): string
    {
        if (self::inDesignMode()) {
            return request()->query->get('_designMode', request()->headers->get('x-visual-editor-theme'));
        }

        return request()->query->get('_previewMode', request()->headers->get('x-visual-preview-theme'));
    }

    public function inPreviewMode(): bool
    {
        return request()->query->has('_previewMode') || request()->headers->has('x-visual-preview-theme');
    }

    public function addJsonView(string $path): void
    {
        $this->jsonViews[] = $path;
    }

    public function jsonViews(): array
    {
        return $this->jsonViews;
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
