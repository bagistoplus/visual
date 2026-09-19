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

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function rememberCategory(int $id, Closure $resolve): mixed
    {
        return $this->remember($this->key('categories', $id), $resolve);
    }

    public function getCategory(int $id): mixed
    {
        return $this->get($this->key('categories', $id));
    }

    public function rememberProduct(int $id, Closure $resolve): mixed
    {
        return $this->remember($this->key('products', $id), $resolve);
    }

    public function getProduct(int $id): mixed
    {
        return $this->get($this->key('products', $id));
    }

    public function rememberCmsPage(int $id, Closure $resolve): mixed
    {
        return $this->remember($this->key('cms_pages', $id), $resolve);
    }

    public function getCmsPage(int $id): mixed
    {
        return $this->get($this->key('cms_pages', $id));
    }

    protected function key(string $type, int $id): string
    {
        return "{$type}:{$id}";
    }
}
