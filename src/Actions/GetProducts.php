<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm as UpdateCreateSearchTermJob;
use Webkul\Product\Repositories\ProductRepository;

final readonly class GetProducts
{
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Get products
     *
     * @return LengthAwarePaginator
     */
    public function execute(array $params)
    {
        request()->query->add($params);

        // Mirrors the non-response-building parts of Bagisto's shop ProductController::index().
        // Keep this in sync when Bagisto changes storefront search/listing behavior.
        $searchEngine = $this->resolveSearchEngine();

        $this->productRepository->setSearchEngine($searchEngine);

        $searchData = $this->resolveSearchQueryData();

        $query = $searchData['effective_query'] ?? $searchData['original_query'];

        $products = $this->productRepository->getAll(
            array_merge(request()->query(), [
                'query' => $query,
                'channel_id' => core()->getCurrentChannel()->id,
                'status' => 1,
                'visible_individually' => 1,
            ])
        );

        if ($this->shouldTrackSearchTerm($query)) {
            UpdateCreateSearchTermJob::dispatch([
                'term' => $query,
                'results' => $products->total(),
                'channel_id' => core()->getCurrentChannel()->id,
                'locale' => app()->getLocale(),
            ]);
        }

        return $products;
    }

    protected function shouldTrackSearchTerm(string $query): bool
    {
        return ! empty($query)
            && array_keys(request()->except(['mode', 'sort', 'limit'])) === ['query'];
    }

    protected function resolveSearchEngine(): string
    {
        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            return core()->getConfigData('catalog.products.search.storefront_mode');
        }

        return 'database';
    }

    protected function resolveSearchQueryData(): array
    {
        if (request()->query('suggest', '') === '0') {
            return [
                'original_query' => request()->query('query', ''),
                'effective_query' => null,
            ];
        }

        $originalQuery = request()->query('query', '');

        if (empty($originalQuery)) {
            return [
                'original_query' => $originalQuery,
                'effective_query' => null,
            ];
        }

        return [
            'original_query' => $originalQuery,
            'effective_query' => $this->resolveSuggestedQuery($originalQuery),
        ];
    }

    protected function resolveSuggestedQuery(string $query): ?string
    {
        if (! method_exists(get_class($this->productRepository), 'getSuggestions')) {
            return null;
        }

        return $this->productRepository->getSuggestions($query);
    }
}
