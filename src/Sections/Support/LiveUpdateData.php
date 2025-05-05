<?php

namespace BagistoPlus\Visual\Sections\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Illuminate\Contracts\Support\Htmlable;

class LiveUpdateData implements Htmlable
{
    public function __construct(public string $sectionId, public ?string $blockId = null, public ?string $settingId = null, public ?string $attribute = null) {}

    public function key(): string
    {
        return collect([
            'section' => $this->sectionId,
            'block' => $this->blockId,
            'setting' => $this->settingId,
        ])->filter()->implode(':');
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

        return 'data-live-update-key="'.$this->key().'"'
            .($this->attribute ? ' data-live-update-attr="'.$this->attribute.'"' : '');
    }
}
