<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\ClearCompareList;
use BagistoPlus\Visual\Actions\GetCompareItems;
use BagistoPlus\Visual\Actions\RemoveItemFromCompareList;

class Compare extends LivewireSection
{
    public static string $view = 'shop::sections.compare';

    public $productIds = [];

    public function loadItems($productIds)
    {
        $this->productIds = $productIds;
    }

    public function removeAllItems()
    {
        $this->productIds = [];
        $response = app(ClearCompareList::class)->execute();

        if (isset($response['message'])) {
            session()->flash('success', $response['message']);
        }
    }

    public function removeItem($id)
    {
        $this->productIds = array_diff($this->productIds, [$id]);
        $response = app(RemoveItemFromCompareList::class)->execute($id);

        if (isset($response['message'])) {
            session()->flash('success', $response['message']);
        }
    }

    public function getViewData(): array
    {
        return [
            'items' => app(GetCompareItems::class)->execute($this->productIds),
        ];
    }
}
