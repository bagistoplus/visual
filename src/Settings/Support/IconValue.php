<?php

namespace BagistoPlus\Visual\Settings\Support;

use BladeUI\Icons\Factory;

class IconValue
{
    public function __construct(public string $icon) {}

    public function __toString()
    {
        return $this->icon;
    }

    public function render($class = '', ?array $attrs = [])
    {
        return app(Factory::class)->svg($this->icon, $class, $attrs);
    }
}
