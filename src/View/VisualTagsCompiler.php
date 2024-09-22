<?php

namespace BagistoPlus\Visual\View;

use BagistoPlus\Visual\Facades\Sections;
use Illuminate\View\Compilers\ComponentTagCompiler;

class VisualTagsCompiler extends ComponentTagCompiler
{
    public function __invoke(string $value)
    {
        return $this->compile($value);
    }

    public function compile($value)
    {
        $this->aliases = $this->blade->getClassComponentAliases();
        $this->namespaces = $this->blade->getClassComponentNamespaces();

        return $this->compileSelftClosingVisualTag($value);
    }

    public function compileSelftClosingVisualTag($value)
    {
        $pattern = '(<\s*visual:section\s*name=("|\')(?<name>[^\'"]+)("|\')\s*\/>)';

        return preg_replace_callback(
            $pattern,
            function (array $matches) {
                $sectionName = $matches['name'];
                if (! Sections::has($sectionName)) {
                    throw new \Exception(sprintf(
                        "Can not found section '%s'.",
                        $sectionName
                    ));
                }

                $section = Sections::get($sectionName);
                $template = $section->renderToBlade($section->slug);

                return parent::compile($template);
            },
            $value
        );
    }
}
