<?php

namespace BagistoPlus\Visual\Settings;

class RichText extends Base
{
    protected static string $type = 'richtext';

    protected bool $localized = true;

    public function inline(): self
    {
        $this->meta['inline'] = true;

        return $this;
    }
}
