<?php

namespace BagistoPlus\Visual\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class TemplateFile implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $key,
        public string $type,
        public string $name,
        public string $label,
        public ?string $path = null,
        public ?string $extension = null,
        public string $source = 'default',
        public bool $isJsonTemplate = false,
    ) {}

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'path' => $this->path,
            'extension' => $this->extension,
            'source' => $this->source,
            'isJsonTemplate' => $this->isJsonTemplate,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
