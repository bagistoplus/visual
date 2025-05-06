<?php

namespace BagistoPlus\Visual\Settings;

class Text extends Base
{
    public static string $component = 'text-setting';

    public string $placeholder = '';

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'placeholder' => $this->placeholder,
        ]);
    }
}
