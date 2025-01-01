<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Facades\Visual;
use BagistoPlus\Visual\Sections\LivewireSection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\ComponentHook;

class SupportSectionData extends ComponentHook
{
    protected function getSectionCacheKey(): string
    {
        return session()->getId().':'.$this->component->visualId;
    }

    public function mount($params)
    {
        if (! ($this->component instanceof LivewireSection)) {
            return;
        }

        $sectionData = Visual::themeDataCollector()->getSectionData($this->component->visualId);
        $viewData = $params['viewData'] ?? [];
        $viewData['section'] = $sectionData;

        $this->component->setContext($viewData);
        $this->component->setSection($sectionData);

        Cache::put($this->getSectionCacheKey(), $viewData, now()->addHours(1));
    }

    public function hydrate()
    {
        if (! ($this->component instanceof LivewireSection)) {
            return;
        }

        $viewData = Cache::get($this->getSectionCacheKey());

        if ($viewData) {
            $this->component->setContext($viewData);
            $this->component->setSection($viewData['section']);
        }
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
