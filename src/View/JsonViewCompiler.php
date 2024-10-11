<?php

namespace BagistoPlus\Visual\View;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Sections\Section;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Symfony\Component\Yaml\Yaml;

class JsonViewCompiler extends Compiler implements CompilerInterface
{
    public const EXTENSIONS = ['json', 'yml', 'yaml'];

    private BladeCompiler $blade;

    /**
     * Create a new compiler instance.
     *
     * @param  string  $cachePath
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Filesystem $files, $cachePath, BladeCompiler $blade)
    {
        parent::__construct($files, $cachePath);
        $this->blade = $blade;
    }

    /**
     * Compile the view at the given path.
     *
     * @param  string|null  $path
     */
    public function compile($path = null): void
    {
        if (is_null($path) || is_null($this->cachePath)) {
            return;
        }

        $bladeTemplate = $this->compileToBlade($path);

        $compiled = $this->blade->compileString($bladeTemplate);
        $compiled = $this->addThemeEditorScript($compiled);
        $compiled = $this->appendFilePath($compiled, $path);

        $this->ensureCompiledDirectoryExists(
            $compiledPath = $this->getCompiledPath($path)
        );

        $this->files->put($compiledPath, $compiled);
    }

    protected function compileToBlade(string $path): string
    {
        $jsonView = $this->loadViewContent($path);

        if ($jsonView === null || ! isset($jsonView['sections'])) {
            return '';
        }

        $order = $jsonView['order'] ?? array_keys($jsonView['sections']);

        return collect($order)->map(function ($sectionId) use ($jsonView, $path) {
            $sectionData = $jsonView['sections'][$sectionId];

            /** @var Section */
            $section = Sections::get($sectionData['type']);

            if (! $section) {
                throw new Exception(sprintf(
                    "Can not found section '%s' used in template %s",
                    $sectionData['type'],
                    $path
                ));
            }

            [$templateName] = explode('.', basename($path));

            return <<<PHP
            <?php
            Visual::collectSectionData('$sectionId', '$path');
            if(ThemeEditor::inDesignMode()) {
                ThemeEditor::collectRenderedSection('{$section->slug}', 'templates', '$templateName', '$sectionId');
            }
            ?>
            <?php if(Visual::isSectionEnabled('$sectionId')): ?>
            {$section->renderToBlade($sectionId)}
            <?php endif; ?>
            PHP;
        })->join("\n");
    }

    protected function loadViewContent(string $path)
    {
        $viewExtension = pathinfo($path, PATHINFO_EXTENSION);

        if ($viewExtension === 'json') {
            return json_decode($this->files->get($path), true);
        }

        return Yaml::parse($this->files->get($path));
    }

    protected function addThemeEditorScript(string $content)
    {
        return <<<PHP
        <?php if (ThemeEditor::inDesignMode()) {
            ThemeEditor::startRenderingContent();
        } ?>
        {$content}
        <?php if (ThemeEditor::inDesignMode()) {
            ThemeEditor::stopRenderingContent();
        } ?>
        PHP;
    }

    /**
     * Append the file path to the compiled string.
     *
     * @param  string  $contents
     * @return string
     */
    protected function appendFilePath($contents, $path)
    {
        $tokens = $this->getOpenAndClosingPhpTokens($contents);

        if ($tokens->isNotEmpty() && $tokens->last() !== T_CLOSE_TAG) {
            $contents .= ' ?>';
        }

        return $contents."<?php /**PATH {$path} ENDPATH**/ ?>";
    }

    /**
     * Get the open and closing PHP tag tokens from the given string.
     *
     * @param  string  $contents
     * @return \Illuminate\Support\Collection
     */
    protected function getOpenAndClosingPhpTokens($contents)
    {
        return collect(token_get_all($contents))
            ->pluck(0)
            ->filter(function ($token) {
                return in_array($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG]);
            });
    }
}
