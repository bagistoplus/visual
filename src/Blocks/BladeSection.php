<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Data\BlockData;

/**
 * @property-read BlockData $section
 */
abstract class BladeSection extends BladeBlock
{
    public function __get(string $name)
    {
        if ($name === 'section') {
            return $this->block;
        }

        return null;
    }

    protected function getVisualData()
    {
        return array_merge(parent::getVisualData(), [
            'section' => $this->block,
        ]);
    }
}
