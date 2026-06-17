<?php

namespace BagistoPlus\Visual\Data;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Template implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $template,
        public string|array $route,
        public string $label,
        public string $icon,
        public string|Closure $previewUrl,
        public ?string $type = null,
        public bool $supportsVariants = false,
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
            type: $attributes['type'] ?? null,
            supportsVariants: $attributes['supportsVariants'] ?? false,
        );
    }

    public static function separator()
    {
        return new self('__separator__', '', '', 'lucide-x', '');
    }

    public function resolvePreviewUrl(): string
    {
        return (string) (is_callable($this->previewUrl)
            ? ($this->previewUrl)()
            : $this->previewUrl);
    }

    public function toArray()
    {
        return [
            'template' => $this->template,
            'route' => $this->route,
            'label' => $this->label,
            'icon' => $this->icon,
            'previewUrl' => $this->resolvePreviewUrl(),
            'type' => $this->type,
            'supportsVariants' => $this->supportsVariants,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
