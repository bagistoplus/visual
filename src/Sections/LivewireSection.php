<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Sections\Concerns\SectionTrait;
use BagistoPlus\Visual\Sections\Support\SectionData;
use Livewire\Component;

class LivewireSection extends Component implements SectionInterface
{
    use SectionTrait;

    public $visualId;

    public $context;

    protected $section;

    public function setContext($context)
    {
        $this->context = $context;
        $this->setSection($context['section']);
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
