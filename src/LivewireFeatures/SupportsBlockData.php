<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Blocks\LivewireBlock;
use BagistoPlus\Visual\Blocks\LivewireSection;
use BagistoPlus\Visual\Facades\Visual;
use Livewire\ComponentHook;

class SupportsBlockData extends ComponentHook
{
    public function mount(array $params)
    {
        if (! ($this->component instanceof LivewireBlock)) {
            return;
        }

        $context = collect($params['context'] ?? [])
            ->except(['cart', 'componentName', 'ignoredParameterNames', 'component', 'theme', 'loop']);

        // Apply global context filters
        foreach (Visual::getLivewireContextFilters() as $filter) {
            $context = $filter($context);
        }

        // Remove Laravel component objects
        $context = $context->reject(fn ($value) => $value instanceof \Illuminate\View\ComponentAttributeBag
            || $value instanceof \Illuminate\View\InvokableComponentVariable
            || $value instanceof \Illuminate\View\ComponentSlot
            || $value instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection
            || $value instanceof \Illuminate\Http\Resources\Json\JsonResource)
            ->all();

        $context['comparableAttributes'] = [];

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
            $craftileContext = array_merge($context, $this->component->share());
        }

        $view->with(array_merge($context, ['__craftileContext' => $craftileContext]));
    }

    public function rerender($view)
    {
        return $this->render($view);
    }
}
