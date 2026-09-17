<?php

namespace BagistoPlus\Visual\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm as UpdateCreateSearchTermJob;
use Webkul\Product\Enums\SearchContextEnum;
use Webkul\Product\Repositories\ProductRepository;

final readonly class GetProducts
{
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Get products
     *
     * The params are the whole query, so a caller that wants the shopper's URL
     * to filter passes it in. The request's own query is replaced for the
     * duration of the call, and restored afterwards.
     *
     * @return LengthAwarePaginator
     */
    public function execute(array $params, bool $trackSearch = true)
    {
        $request = request();
        $savedQuery = $request->query->all();

        try {
            $request->query->replace($params);

            // Mirrors the non-response-building parts of Bagisto's shop API ProductController::index().
            // Keep this in sync when Bagisto changes storefront search/listing behavior.
            $this->applySearchContext();

            $searchData = $this->resolveSearchQueryData($params);

            $query = $searchData['effective_query'] ?? $searchData['original_query'];

            $products = $this->productRepository->getAll(
                array_merge($params, [
                    'query' => $query,
                    'channel_id' => core()->getCurrentChannel()->id,
                    'status' => 1,
                    'visible_individually' => 1,
                ])
            );

            if ($trackSearch && $this->shouldTrackSearchTerm($query, $params)) {
                UpdateCreateSearchTermJob::dispatch([
                    'term' => $query,
                    'results' => $products->total(),
                    'channel_id' => core()->getCurrentChannel()->id,
                    'locale' => app()->getLocale(),
                ]);
            }

            return $products;
        } finally {
            $request->query->replace($savedQuery);
        }
    }

    protected function shouldTrackSearchTerm(string $query, array $params): bool
    {
        return ! empty($query)
            && array_keys(Arr::except($params, ['mode', 'sort', 'limit'])) === ['query'];
    }

    /**
     * Bagisto 2.5 replaced setSearchEngine(string) with setSearchContext(SearchContextEnum).
     */
    protected function applySearchContext(): void
    {
        if (class_exists(SearchContextEnum::class)) {
            $this->productRepository->setSearchContext(SearchContextEnum::STOREFRONT);

            return;
        }

        $this->productRepository->setSearchEngine($this->resolveSearchEngine());
    }

    protected function resolveSearchEngine(): string
    {
        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            return core()->getConfigData('catalog.products.search.storefront_mode');
        }

        return 'database';
    }

    protected function resolveSearchQueryData(array $params): array
    {
        $originalQuery = (string) ($params['query'] ?? '');

        if (($params['suggest'] ?? '') === '0' || empty($originalQuery)) {
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
