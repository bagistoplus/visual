<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Blocks\LivewireBlock;
use BagistoPlus\Visual\Blocks\LivewireSection;
use Livewire\ComponentHook;

class SupportsBlockData extends ComponentHook
{
    public function mount(array $params)
    {
        if (! ($this->component instanceof LivewireBlock)) {
            return;
        }

        $context = collect($params['context'] ?? [])->except(['errors', 'app', 'block', 'section', 'theme', 'cart'])->all();
        $context['comparableAttributes'] = collect();

        $this->component->setContext($context);
        $this->component->setBlock($params['block']);
    }

    public function render($view)
    {
        if (! ($this->component instanceof LivewireBlock)) {
            return;
        }

        $context = $this->component->getContext();
        $craftileContext = $context;

        if ($this->component instanceof LivewireSection) {
            $context['section'] = $this->component->getBlock();
        }

        if (method_exists($this->component, 'share')) {
            $craftileContext = array_merge($context, [$this->component->share()]);
        }

        $view->with(array_merge($context, ['__craftileContext' => $craftileContext]));
    }

    public function rerender($view)
    {
        return $this->render($view);
    }
}
