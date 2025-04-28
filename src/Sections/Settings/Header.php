<?php

namespace BagistoPlus\Visual\Sections\Settings;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/** @phpstan-consistent-constructor */
class Header implements Arrayable, JsonSerializable
{
    public function __construct(public string $label) {}

    public static function make(string $label): self
    {
        return new static($label);
    }

    public function toArray()
    {
        return ['type' => 'header', 'label' => $this->label];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
