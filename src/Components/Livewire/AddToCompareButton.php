<?php

namespace BagistoPlus\Visual\Components\Livewire;

use BagistoPlus\Visual\Actions\AddProductToCompare;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddToCompareButton extends Component
{
    #[Locked]
    public $productId;

    public function handle()
    {
        app(AddProductToCompare::class)->execute($this->productId);
    }

    public function render()
    {
        return view('shop::livewire.add-to-compare-button');
    }
}
