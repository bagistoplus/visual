<?php

namespace BagistoPlus\Visual\Support;

use Closure;

/**
 * Remembers models resolved by setting transformers for the lifetime of a request.
 *
 * Callers asking for the same key receive the same instance, so a model mutated
 * by one block is seen mutated by every other block that reads it.
 */
class ResolvedModelCache
{
    /**
     * @var array<string, mixed>
     */
    protected array $items = [];

    public function remember(string $key, Closure $resolve): mixed
    {
        if (! array_key_exists($key, $this->items)) {
            $this->items[$key] = $resolve();
        }

        return $this->items[$key];
    }

    public function rememberCategory(int $id, Closure $resolve): mixed
    {
        return $this->remember("categories:{$id}", $resolve);
    }

    public function rememberProduct(int $id, Closure $resolve): mixed
    {
        return $this->remember("products:{$id}", $resolve);
    }

    public function rememberCmsPage(int $id, Closure $resolve): mixed
    {
        return $this->remember("cms_pages:{$id}", $resolve);
    }
}
