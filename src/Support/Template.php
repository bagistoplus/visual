<?php

namespace BagistoPlus\Visual\Support;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Template implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $template,
        public string|array $route,
        public string $label,
        public string $icon,
        public string $previewUrl
    ) {}

    public function matchRoute($route)
    {
        return in_array($route, (array) $this->route);
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            template: $attributes['template'],
            route: $attributes['route'],
            label: $attributes['label'],
            icon: $attributes['icon'],
            previewUrl: $attributes['previewUrl'],
        );
    }

    public static function separator()
    {
        return new self('__separator__', '', '', 'lucide-x', '');
    }

    public function toArray()
    {
        return [
            'template' => $this->template,
            'route' => $this->route,
            'label' => $this->label,
            'icon' => $this->icon,
            'previewUrl' => $this->previewUrl,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
