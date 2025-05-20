<?php

namespace BagistoPlus\Visual\Events;

use BagistoPlus\Visual\Theme\Theme;
use Illuminate\Foundation\Events\Dispatchable;

class ThemeActivated
{
    use Dispatchable;

    public function __construct(public Theme $theme) {}
}
