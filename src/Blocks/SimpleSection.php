<?php

namespace BagistoPlus\Visual\Blocks;

class SimpleSection extends SimpleBlock
{
    public function __get($name)
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
