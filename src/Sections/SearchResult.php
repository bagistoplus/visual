<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\GetProducts;
use BagistoPlus\Visual\Support\HandlesProductListing;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Webkul\Attribute\Repositories\AttributeRepository;

class SearchResult extends LivewireSection
{
    use HandlesProductListing;
    use WithPagination;

    protected static array $enabledOn = ['search'];

    protected static string $view = 'shop::sections.search-result';

    public function paginationView()
    {
        return 'shop::pagination.default';
    }

    public function paginationSimpleView()
    {
        return 'shop::pagination.default';
    }

    #[Computed(persist: true)]
    public function availableFilters()
    {
        $filterableAttributes = app(AttributeRepository::class)->getFilterableAttributes();

        return $filterableAttributes->filter(function ($filter) {
            return $filter->type === 'price' || $filter->options->isNotEmpty();
        });
    }

    public function mount()
    {
        $this->initializeMaxPrice();
        $this->initializeFilters();
    }

    public function getViewData(): array
    {
        return [
            'products' => app(GetProducts::class)->execute(
                $this->buildProductParams()
            ),
        ];
    }
}
