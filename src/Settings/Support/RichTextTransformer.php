<?php

namespace BagistoPlus\Visual\Settings\Support;

use Mews\Purifier\Facades\Purifier;

class RichTextTransformer
{
    public function __invoke(?string $html, array $schema = [])
    {
        return $html ? Purifier::clean($html) : null;
    }
}
