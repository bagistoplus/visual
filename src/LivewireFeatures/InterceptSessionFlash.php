<?php

namespace BagistoPlus\Visual\LivewireFeatures;

use Livewire\ComponentHook;

class InterceptSessionFlash extends ComponentHook
{
    private const DISPATCHED_FLASH_KEY = '_dispatched_flash';

    public function dehydrate($arg)
    {
        // Skip if this is the initial Livewire request
        if (! request()->headers->has('x-livewire')) {
            return;
        }

        $flashTypes = ['info', 'warning', 'error', 'success'];

        $alreadyDispatched = request()->attributes->get(self::DISPATCHED_FLASH_KEY, []);

        $messages = collect($flashTypes)
            ->filter(fn ($type) => session()->has($type))
            ->mapWithKeys(fn ($type) => [$type => session()->get($type)]);

        $messages->each(function ($message, $type) use (&$alreadyDispatched) {
            $toast = base64_encode(json_encode(compact('type', 'message')));

            if (in_array($toast, $alreadyDispatched)) {
                return; // Skip if message was already dispatched in this request
            }

            $this->component->dispatch('toasts:create', type: $type, title: $message);
            session()->forget($type);

            $alreadyDispatched[] = $toast;
        });

        request()->attributes->set(self::DISPATCHED_FLASH_KEY, $alreadyDispatched);
    }
}
