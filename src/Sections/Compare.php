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
        app(ClearCompareList::class)->execute();
    }

    public function removeItem($id)
    {
        $this->productIds = array_diff($this->productIds, [$id]);
        app(RemoveItemFromCompareList::class)->execute($id);
    }

    public function getViewData(): array
    {
        return [
            'items' => app(GetCompareItems::class)->execute($this->productIds),
        ];
    }
}
