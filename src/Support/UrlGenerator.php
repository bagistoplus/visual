<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator as RoutingUrlGenerator;
use Illuminate\Support\Arr;

class UrlGenerator extends RoutingUrlGenerator
{
    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        $url = parent::current();

        if (ThemeEditor::inDesignMode() || ThemeEditor::inPreviewMode()) {
            $url = explode('?', $url)[0];
        }

        return $url;
    }

    /**
     * Get the Route URL generator instance.
     *
     * @return \Illuminate\Routing\RouteUrlGenerator
     */
    protected function routeUrl()
    {
        if (! $this->routeGenerator) {
            $this->routeGenerator = new RouteUrlGenerator($this, $this->request);
        }

        return $this->routeGenerator;
    }

    /**
     * Get the URL for a given route instance.
     *
     * @param  mixed  $route
     * @param  mixed  $parameters
     * @param  bool  $absolute
     */
    public function toRoute($route, $parameters, $absolute)
    {
        return parent::toRoute(
            $route,
            $this->withTemplateParameterForRoute($route, $parameters),
            $absolute
        );
    }

    /**
     * Extract the query string from the given path.
     *
     * @param  string  $path
     * @return array
     */
    protected function extractQueryString($path)
    {
        $query = '';

        if (ThemeEditor::inDesignMode()) {
            $query = '_designMode='.ThemeEditor::activeTheme();
        } elseif (ThemeEditor::inPreviewMode()) {
            $query = '_previewMode='.ThemeEditor::activeTheme();
        }

        if (($queryPosition = strpos($path, '?')) !== false) {
            return [
                substr($path, 0, $queryPosition),
                substr($path, $queryPosition).'&'.$query,
            ];
        }

        return [$path, $query ? '?'.$query : ''];
    }

    protected function withTemplateParameterForRoute(Route $route, mixed $parameters): mixed
    {
        if (! ThemeEditor::inDesignMode() || ! request()->query->has('_template')) {
            return $parameters;
        }

        $template = request()->query('_template');
        $templateType = app(TemplateDiscovery::class)->typeForKey($template);
        $routeType = $this->templateTypeForRoute($route->getName());

        if (! $templateType || $templateType !== $routeType) {
            return $parameters;
        }

        $parameters = Arr::wrap($parameters);
        $parameters['_template'] = $template;

        return $parameters;
    }

    protected function templateTypeForRoute(?string $routeName): ?string
    {
        return match ($routeName) {
            'shop.products.index' => 'product',
            'shop.categories.index' => 'category',
            'shop.cms.page' => 'page',
            default => null,
        };
    }
}
