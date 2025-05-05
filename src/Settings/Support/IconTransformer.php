<?php

namespace BagistoPlus\Visual\Settings\Support;

class IconTransformer
{
    public function __invoke(?string $icon = null)
    {
        if (! $icon) {
            return null;
        }

        return new Icon($icon);
    }
}
