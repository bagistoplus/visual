<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Contracts\SettingTransformerInterface;
use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Illuminate\Support\Collection;
use Webkul\Product\Repositories\ProductRepository;

class ProductListTransformer implements SettingTransformerInterface
{
    public function transform(mixed $value, array $schema = []): mixed
    {
        if (! is_array($value) || empty($value)) {
            return collect();
        }

        $cache = app(ResolvedModelCache::class);

        $resolved = [];
        $missing = [];

        foreach (array_unique(array_map('intval', $value)) as $id) {
            $cached = $cache->getProduct($id);

            if ($cached) {
                $resolved[$id] = $cached;
            } else {
                $missing[] = $id;
            }
        }

        // One batch, because a relation is a query per batch rather than per
        // model. Fetching an id separately would pay for all of them again.
        if ($missing !== []) {
            $products = app(ProductRepository::class)
                ->with(ProductTransformer::RELATIONS)
                ->findWhereIn('id', $missing);

            foreach ($products as $product) {
                $id = (int) $product->id;

                $resolved[$id] = $product;
                $cache->rememberProduct($id, fn () => $product);
            }
        }

        $inDesignMode = ThemeEditor::inDesignMode();

        return Collection::make($value)
            ->map(function ($id) use ($resolved, $inDesignMode) {
                $product = $resolved[(int) $id] ?? null;

                if (! $product) {
                    return null;
                }

                if ($inDesignMode) {
                    ThemeEditor::preloadModel('products', $product);
                }

                return $product;
            })
            ->filter()
            ->values();
    }
}
