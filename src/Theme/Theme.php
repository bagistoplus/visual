<?php

namespace BagistoPlus\Visual\Theme;

use BagistoPlus\Visual\Facades\Visual;
use Webkul\Theme\Theme as BagistoTheme;

class Theme extends BagistoTheme
{
    protected $isVisualTheme = false;

    public function __construct(
        string $code,
        string $name,
        ?string $assetsPath = null,
        ?string $viewsPath = null,
        array $vite = [],
        bool $isVisualTheme = false
    ) {
        parent::__construct($code, $name, $assetsPath, $viewsPath, $vite);

        $this->isVisualTheme = $isVisualTheme;
    }

    public function isVisualTheme(): bool
    {
        return $this->isVisualTheme;
    }

    /**
     * Return all the possible view paths.
     */
    public function getViewPaths(): array
    {
        $paths = parent::getViewPaths();

        $visualPaths = Visual::getVisualThemePaths($this->code);

        return array_merge($visualPaths, $paths);
    }
}
