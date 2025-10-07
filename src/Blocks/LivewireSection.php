<?php

namespace BagistoPlus\Visual\Blocks;

class LivewireSection extends LivewireBlock
{
    public function __get($name)
    {
        if ($name === 'section') {
            return $this->block;
        }

        return parent::__get($name);
    }
}
