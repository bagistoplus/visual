<?php

namespace BagistoPlus\Visual\Support;

use Craftile\Core\Data\PresetChild;

class PresetBlock extends PresetChild
{
    public function settings(array $settings): static
    {
        /** @var static */
        return $this->properties($settings);
    }
}
