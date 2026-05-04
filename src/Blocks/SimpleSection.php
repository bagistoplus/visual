<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Data\BlockData;

/**
 * @property-read BlockData $section
 */
class SimpleSection extends SimpleBlock
{
    public function __get(string $name)
    {
        if ($name === 'section') {
            return $this->block;
        }

        return null;
    }

    public function data()
    {
        return [
            'section' => $this->block,
        ];
    }
}
