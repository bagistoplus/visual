<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use BagistoPlus\Visual\Blocks\LivewireBlock;
use BagistoPlus\Visual\Blocks\LivewireSection;
use BagistoPlus\Visual\Facades\Visual;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;
use Illuminate\View\InvokableComponentVariable;
use Livewire\ComponentHook;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

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
        $context = $context->reject(fn ($value) => $value instanceof ComponentAttributeBag
            || $value instanceof InvokableComponentVariable
            || $value instanceof ComponentSlot
            || $value instanceof AnonymousResourceCollection
            || $value instanceof JsonResource)
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

        $craftileContext = array_merge($context, $this->component->share());

        $view->with(array_merge($context, ['__craftileContext' => $craftileContext]));
    }

    public function rerender($view)
    {
        return $this->render($view);
    }

    public function dehydrate(ComponentContext $context)
    {
        if (! ($this->component instanceof LivewireBlock)) {
            return;
        }

        $context->memo['name'] = $this->component::class;
    }
}
