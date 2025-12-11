<?php

namespace BagistoPlus\Visual\Settings;

class RichText extends Base
{
    protected static string $type = 'richtext';

    public function inline(): self
    {
        $this->meta['inline'] = true;

        return $this;
    }
}
