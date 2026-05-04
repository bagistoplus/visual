<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Data\BlockData;

/**
 * @property-read BlockData $section
 */
class LivewireSection extends LivewireBlock
{
    public function __get(mixed $name)
    {
        if ($name === 'section') {
            return $this->block;
        }

        return parent::__get($name);
    }
}
