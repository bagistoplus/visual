<?php

namespace BagistoPlus\Visual\Settings;

class RichText extends Base
{
    public static string $component = 'richtext-setting';

    public bool $inline = false;

    public function inline(): self
    {
        $this->inline = true;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => $this->inline ? 'inline_richtext' : 'richtext',
            'inline' => $this->inline,
        ]);
    }
}
