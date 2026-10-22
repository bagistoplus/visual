<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Data\Template;
use BagistoPlus\Visual\Events\ServingThemeEditor;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ThemeEditor
{
    /**
     * What Bagisto answers with about who may do what.
     */
    protected const BOUNCER = 'Webkul\User\Bouncer';

    /**
     * The permission the editor's own routes are gated on.
     */
    protected const EDITOR_PERMISSION = 'bagisto_visual.editor';

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

    /**
     * Whether the editor is driving, for someone who may drive it.
     */
    public function active(): bool
    {
        return $this->inDesignMode() || request()->is('*/visual/editor/*');
    }

    /**
     * Whether the request is the editor's, whoever is making it.
     *
     * Asked where the answer is needed before a session exists, and where it
     * discloses nothing on its own: which templates are registered, whether the
     * page may be framed, whether it may be cached. What the page is allowed to
     * say is decided by `active()` and `inDesignMode()`, later, where the viewer
     * is known.
     */
    public function requested(): bool
    {
        return $this->designModeRequested() || request()->is('*/visual/editor/*');
    }

    /**
     * Whether the editor is driving this request.
     *
     * Design mode shows the merchant's unpublished work, so it is offered to
     * whoever may edit it and to nobody else. The permission is the one Bagisto
     * already gates the editor's own routes on, so the editor and the storefront
     * it draws agree on who may drive them.
     */
    public function inDesignMode(): bool
    {
        return $this->designModeRequested() && $this->allowedToEdit();
    }

    /**
     * Whether the request asks for design mode, whoever is asking.
     *
     * Public because a decision that discloses nothing, such as whether the
     * page may be framed, is answered before a session exists and so cannot ask
     * who is asking.
     */
    public function designModeRequested(): bool
    {
        return request()->query->has('_designMode') || request()->headers->has('x-visual-editor-theme');
    }

    /**
     * Whether the viewer may edit a theme.
     *
     * Bagisto's own guard answers no for a visitor who is not signed in as an
     * administrator, so there is nothing else to check. It is resolved by name
     * because it belongs to Bagisto rather than to this package.
     */
    protected function allowedToEdit(): bool
    {
        if (! class_exists(self::BOUNCER)) {
            return false;
        }

        return (bool) $this->bouncer()->hasPermission(self::EDITOR_PERMISSION);
    }

    /**
     * Bagisto's guard, resolved by name because it belongs to a package whose
     * releases describe it differently.
     */
    protected function bouncer(): mixed
    {
        return app(self::BOUNCER);
    }

    public function activeTheme(): string
    {
        if ($this->inDesignMode()) {
            return request()->query->get('_designMode', request()->headers->get('x-visual-editor-theme'));
        }

        if ($this->inDraftPreviewMode()) {
            return request()->query->get('_draftPreview', request()->headers->get('x-visual-draft-preview-theme'));
        }

        return request()->query->get('_previewMode', request()->headers->get('x-visual-preview-theme'));
    }

    public function inPreviewMode(): bool
    {
        return request()->query->has('_previewMode') || request()->headers->has('x-visual-preview-theme');
    }

    /**
     * Preview of the unpublished editor data, rendered without the editor client.
     */
    public function inDraftPreviewMode(): bool
    {
        return request()->query->has('_draftPreview') || request()->headers->has('x-visual-draft-preview-theme');
    }

    /**
     * Whether theme views and settings should be read from the editor (draft) data instead of live.
     */
    public function usesEditorData(): bool
    {
        return $this->active() || $this->inDraftPreviewMode();
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

    public function getTemplateFromJsonViews(?string $fallback = null): string
    {
        foreach (array_reverse($this->jsonViews) as $path) {
            if (preg_match('#(?:^|/)templates/(product|category|page)/([^/.]+)\.(?:json|ya?ml|visual\.php)$#', $path, $matches)) {
                return $matches[2] === 'index'
                    ? $matches[1]
                    : "{$matches[1]}.{$matches[2]}";
            }

            if (preg_match('#(?:^|/)templates/(product|category|page)\.(?:json|ya?ml|visual\.php)$#', $path, $matches)) {
                return $matches[1];
            }
        }

        return $fallback ?? 'index';
    }

    public function preloadModel(string $type, $model): void
    {
        $this->preloadedModels[$type][$model->id] = $model;
    }

    public function preloadedModels(): array
    {
        return array_map('array_values', $this->preloadedModels);
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
