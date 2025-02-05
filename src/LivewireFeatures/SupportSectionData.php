<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\LivewireSection;
use Illuminate\Contracts\View\View;
use Livewire\ComponentHook;

class SupportSectionData extends ComponentHook
{
    public function mount($params)
    {
        if (! ($this->component instanceof LivewireSection)) {
            return;
        }

        $sectionData = Visual::themeDataCollector()->getSectionData($this->component->visualId);
        $context = collect($params['viewData'])->except(['errors', 'theme', 'cart'])->all();
        $context['section'] = $sectionData;

        $this->component->setContext($context);
    }

    public function hydrate()
    {
        if (! ($this->component instanceof LivewireSection)) {
            return;
        }

        $this->component->setSection($this->component->context['section']);
    }

    public function render($view)
    {
        if (! ($this->component instanceof LivewireSection)) {
            return;
        }

        $view->with($this->component->getContext());
    }

    public function rerender(View $view)
    {
        if (! ($this->component instanceof LivewireSection)) {
            return;
        }

        $view->with($this->component->getContext());
    }
}
