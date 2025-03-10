<?php

namespace BagistoPlus\Visual\Support;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Webkul\Product\Helpers\Toolbar;
use Webkul\Product\Repositories\ProductRepository;

trait HandlesProductListing
{
    public $maxPrice = 0;

    #[Url]
    public $sort = '';

    #[Url]
    public $limit;

    #[Url]
    public $filters = [];

    #[Url(as: 'mode')]
    public $displayMode = 'grid';

    public function setFilter($code, $value)
    {
        $this->filters[$code] = $value;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->availableFilters->each(function ($filter) {
            if ($filter->type === 'price') {
                $this->filters[$filter->code] = [0, $this->maxPrice];
            } else {
                $this->filters[$filter->code] = [];
            }
        });

        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function updatedLimit()
    {
        $this->resetPage();
    }

    public function updatedDisplayMode()
    {
        $this->resetPage();
    }

    #[Computed(persist: true)]
    public function availableSortOptions()
    {
        return app(Toolbar::class)->getAvailableOrders();
    }

    #[Computed(persist: true)]
    public function availablePaginationLimits()
    {
        return app(Toolbar::class)->getAvailableLimits();
    }

    protected function initializeMaxPrice(array $params = [])
    {
        $this->maxPrice = app('core')->convertPrice(
            app(ProductRepository::class)->getMaxPrice($params)
        );
    }

    protected function initializeFilters()
    {
        if (! isset($this->availableFilters)) {
            return;
        }

        $this->availableFilters->each(function ($filter) {
            if (isset($this->filters[$filter->code])) {
                return;
            }

            if ($filter->type === 'price') {
                $this->filters[$filter->code] = [0, $this->maxPrice];
            } else {
                $this->filters[$filter->code] = [];
            }
        });
    }

    protected function buildProductParams(array $additionalParams = []): array
    {
        return array_merge(
            request()->query(),
            [
                'limit' => $this->limit,
                'sort' => $this->sort,
            ],
            collect($this->filters)
                ->map(fn ($value) => is_array($value) ? implode(',', $value) : $value)
                ->filter()
                ->toArray(),
            $additionalParams
        );
    }
}
