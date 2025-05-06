<?php

namespace BagistoPlus\Visual\Components;

use Closure;
use Illuminate\View\Component;

final class Svg extends Component
{
    public function render(): Closure
    {
        return function (array $data) {
            $attributes = $data['attributes']->getIterator()->getArrayCopy();

            $class = $attributes['class'] ?? '';

            unset($attributes['class']);

            $realIcon = config("bagisto_visual_iconmap.$this->componentName", 'lucide-file-question');

            return svg($realIcon, $class, $attributes)->toHtml();
        };
    }
}
