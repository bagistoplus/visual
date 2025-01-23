<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\GetProducts;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Product\Helpers\Toolbar;
use Webkul\Product\Repositories\ProductRepository;

class CategoryPage extends LivewireSection
{
    public static string $view = 'shop::sections.category-page';

    #[Url]
    public $filters = [];

    #[Url]
    public $sort = '';

    #[Url]
    public $limit;

    #[Url(as: 'mode')]
    public $displayMode = 'grid';

    public $maxPrice = 0;

    public function setFilter($code, $value)
    {
        $this->filters[$code] = $value;
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
    }

    #[Computed(persist: true)]
    public function availableFilters()
    {
        if (empty($filterableAttributes = $this->context['category']->filterableAttributes)) {
            $filterableAttributes = app(AttributeRepository::class)->getFilterableAttributes();
        }

        return $filterableAttributes->filter(function ($filter) {
            return $filter->type === 'price' || $filter->options->isNotEmpty();
        });
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

    public function boot()
    {
        $this->availableFilters->each(function ($filter) {
            if (isset($this->filters[$filter->code])) {
                return;
            }

            $this->filters[$filter->code] = [];
        });
    }

    public function mount()
    {
        $this->maxPrice = app('core')->convertPrice(
            app(ProductRepository::class)->getMaxPrice(['category_id' => $this->context['category']->id])
        );
    }

    public function getViewData(): array
    {
        $params = array_merge(
            request()->query(),
            [
                'category_id' => $this->context['category']->id,
            ],
            collect($this->filters)
                ->map(fn ($value) => implode(',', $value))
                ->filter()
                ->toArray()
        );

        return [
            'products' => app(GetProducts::class)->execute($params),
        ];
    }
}
