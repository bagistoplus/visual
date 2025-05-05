<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Settings\Number;

class LiveCounter extends LivewireSection
{
    protected static string $view = 'shop::sections.live-counter';

    public $count = 0;

    public function increment()
    {
        $this->count += $this->section->settings->increment;
    }

    public function decrement()
    {
        $this->count -= $this->section->settings->increment;
    }

    public static function settings(): array
    {
        return [
            Number::make('increment', 'Live counter increment')
                ->default(1)
                ->min(1)
                ->max(3),
        ];
    }
}
