<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Facades\Sections;
use BagistoPlus\Visual\Sections\Section;
use Illuminate\Support\Facades\Blade;

class Visual
{
    public function registerSection(string $componentClass, string $prefix): void
    {
        $section = Section::createFromComponent($componentClass);
        $section->slug = $prefix.'-'.$section->slug;

        Sections::add($section);

        Blade::component($componentClass, $section->slug, 'visual-section');
    }
}
