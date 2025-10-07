<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use Illuminate\View\ComponentAttributeBag;
use Livewire\ComponentHook;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

class SupportsComponentAttributes extends ComponentHook
{
    protected static $attributesByComponents = [];

    public function mount(array $params)
    {
        $attributes = collect($params)
            // TODO: check on why context invalidate livewire checksum
            ->reject(fn ($_, $key) => in_array($key, ['context', ...array_keys($this->component->all())]))
            ->mapWithKeys(function ($value, $key) {
                // convert alpine attributes to kebab-case as livewire convert all attributes to camelCase
                if (preg_match('/^x[A-Z]/', $key)) {
                    $key = (string) str($key)->kebab();
                }

                return [$key => $value];
            })
            ->all();

        self::$attributesByComponents[$this->component->getId()] = $attributes;
    }

    public function dehydrate(ComponentContext $context)
    {
        $context->memo['attributes'] = self::$attributesByComponents[$this->component->getId()] ?? [];
    }

    public function hydrate($params)
    {
        self::$attributesByComponents[$this->component->getId()] = $params['attributes'] ?? [];
    }

    public function render($view)
    {
        $attributes = new ComponentAttributeBag(
            self::$attributesByComponents[$this->component->getId()] ?? []
        );

        $view->with([
            'attributes' => $attributes,
        ]);
    }
}
