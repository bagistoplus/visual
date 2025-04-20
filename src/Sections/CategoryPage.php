<?php

namespace BagistoPlus\Visual\Sections;

use BagistoPlus\Visual\Actions\GetProducts;
use BagistoPlus\Visual\Support\HandlesProductListing;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Webkul\Attribute\Repositories\AttributeRepository;

class CategoryPage extends LivewireSection
{
    use HandlesProductListing;
    use WithPagination;

    public static string $view = 'shop::sections.category-page';

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
        if (empty($filterableAttributes = $this->context['category']->filterableAttributes)) {
            $filterableAttributes = app(AttributeRepository::class)->getFilterableAttributes();
        }

        return $filterableAttributes->filter(function ($filter) {
            return $filter->type === 'price' || $filter->options->isNotEmpty();
        });
    }

    public function mount()
    {
        $this->initializeMaxPrice(['category_id' => $this->context['category']->id]);
        $this->initializeFilters();
    }

    public function getViewData(): array
    {
        return [
            'products' => app(GetProducts::class)->execute(
                $this->buildProductParams(['category_id' => $this->context['category']->id])
            ),
        ];
    }
}
