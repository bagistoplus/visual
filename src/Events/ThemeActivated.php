<?php

namespace BagistoPlus\Visual\Events;

use BagistoPlus\Visual\Theme\Theme;

class ThemeActivated
{
    public function __construct(public Theme $theme) {}
}
