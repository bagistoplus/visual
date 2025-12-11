<?php

namespace BagistoPlus\Visual\Support;

use Craftile\Core\Data\BlockPreset;

class Preset extends BlockPreset
{
    public function settings(array $settings): static
    {
        /** @var static */
        return $this->properties($settings);
    }
}
