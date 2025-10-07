<?php

namespace BagistoPlus\Visual\Blocks;

abstract class BladeSection extends BladeBlock
{
    public function __get($name)
    {
        if ($name === 'section') {
            return $this->block;
        }

        return null;
    }

    protected function getVisualData()
    {
        return [
            'block' => $this->block,
            'section' => $this->block,
        ];
    }
}
