<?php

namespace BagistoPlus\Visual\Sections;

class LiveCounter extends LivewireSection
{
    protected static string $schema = __DIR__ . '/../../resources/schemas/live-counter.json';

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
}
