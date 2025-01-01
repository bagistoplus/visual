<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Concerns\SectionData;
use BagistoPlus\Visual\Sections\Concerns\SectionTrait;
use Livewire\Component;

class LivewireSection extends Component implements SectionInterface
{
    use SectionTrait;

    public $visualId;

    protected $context;

    protected $section;

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setSection(SectionData $section)
    {
        $this->section = $section;
    }

    public function getSection()
    {
        return $this->section;
    }
}
