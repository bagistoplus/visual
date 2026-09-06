<?php

namespace BagistoPlus\Visual\Settings;

use Craftile\Core\Data\Rule;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/** @phpstan-consistent-constructor */
class Description implements Arrayable, JsonSerializable
{
    protected array $meta = [];

    public function __construct(public string $content) {}

    public static function make(string $content): self
    {
        return new static($content);
    }

    public function visibleIf(callable $callback): static
    {
        $rule = new Rule;
        $callback($rule);

        $this->meta['visibleIf'] = $rule->toArray();

        return $this;
    }

    public function visibleWhen(callable $callback): static
    {
        return $this->visibleIf($callback);
    }

    public function toArray()
    {
        return array_merge(['type' => 'description', 'content' => $this->content], $this->meta);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
