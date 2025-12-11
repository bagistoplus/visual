<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Contracts\Support\Htmlable;

/**
 * This class is used to build live updates metadata for a specific block in the theme editor.
 * It allows adding various types of updates such as text, HTML, outer HTML, attributes, and styles.
 */
class LiveUpdatesBuilder implements Htmlable
{
    protected array $updates = [];

    public function __construct(protected string $blockId) {}

    protected function makeKey(string $propertyId): string
    {
        return collect([
            'block' => $this->blockId,
            'propertyId' => $propertyId,
        ])->filter()->implode('.');
    }

    protected function add(string $propertyId, string $type): self
    {
        $this->updates[] = [
            'key' => $this->makeKey($propertyId),
            'type' => $type,
        ];

        return $this;
    }

    public function text(string $propertyId): self
    {
        return $this->add($propertyId, 'text');
    }

    public function html(string $propertyId): self
    {
        return $this->add($propertyId, 'html');
    }

    public function outerHtml(string $propertyId): self
    {
        return $this->add($propertyId, 'outerHTML');
    }

    public function attr(string $propertyId, string $attr): self
    {
        return $this->add($propertyId, 'attr:'.$attr);
    }

    public function style(string $propertyId, string $style): self
    {
        return $this->add($propertyId, 'style:'.$style);
    }

    public function toggleClass(string $propertyId, string $class): self
    {
        return $this->add($propertyId, 'toggleClass:'.$class);
    }

    public function toHtml(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        if (! ThemeEditor::inDesignMode()) {
            return '';
        }

        return collect($this->updates)->map(function ($update) {
            $attr = 'data-live-update-'.$update['key'];

            return $attr.'="'.htmlspecialchars($update['type'], ENT_QUOTES, 'UTF-8').'"';
        })->implode(' ');
    }
}
