<?php

namespace BagistoPlus\Visual\Sections\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Contracts\Support\Htmlable;

/**
 * This class is used to build live updates metadata for a specific section or block in the theme editor.
 * It allows adding various types of updates such as text, HTML, outer HTML, attributes, and styles.
 */
class LiveUpdatesBuilder implements Htmlable
{
    protected string $sectionId;

    protected ?string $blockId;

    protected array $updates = [];

    public function __construct(string $sectionId, ?string $blockId = null)
    {
        $this->sectionId = $sectionId;
        $this->blockId = $blockId;
    }

    protected function makeKey(string $settingId): string
    {
        return collect([
            'section' => $this->sectionId,
            'block' => $this->blockId,
            'setting' => $settingId,
        ])->filter()->implode('.');
    }

    protected function add(string $settingId, string $type): self
    {
        $this->updates[] = [
            'key' => $this->makeKey($settingId),
            'type' => $type,
        ];

        return $this;
    }

    public function text(string $settingId): self
    {
        return $this->add($settingId, 'text');
    }

    public function html(string $settingId): self
    {
        return $this->add($settingId, 'html');
    }

    public function outerHtml(string $settingId): self
    {
        return $this->add($settingId, 'outerHTML');
    }

    public function attr(string $settingId, string $attr): self
    {
        return $this->add($settingId, 'attr:' . $attr);
    }

    public function style(string $settingId, string $style): self
    {
        return $this->add($settingId, 'style:' . $style);
    }

    public function toggleClass(string $settingId, string $class): self
    {
        return $this->add($settingId, 'toggleClass:' . $class);
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
            $attr = 'data-live-update-' . $update['key'];

            return $attr . '="' . htmlspecialchars($update['type'], ENT_QUOTES, 'UTF-8') . '"';
        })->implode(' ');
    }
}
