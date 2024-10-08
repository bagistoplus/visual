<?php

namespace BagistoPlus\Visual;

class ThemeEditor
{
    protected string $renderingLayout = '';

    protected string $renderingView = '';

    protected string $renderingSectionGroup = 'beforeContent';

    protected array $renderedSections = [];

    protected array $templates;

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
            'id' => $id ?: $type,
            'group' => $this->renderingSectionGroup,
        ];
    }

    public function renderedSections()
    {
        return $this->renderedSections;
    }

    public function registerTemplateForRoute(string $routeName, array $template)
    {
        $this->templates[$routeName] = $template;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function getTemplateForRoute(string $routeName)
    {
        if (isset($this->templates[$routeName])) {
            return $this->templates[$routeName]['template'];
        }

        return 'index';
    }
}
