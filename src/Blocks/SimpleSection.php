<?php

namespace BagistoPlus\Visual\Blocks;

class SimpleSection extends SimpleBlock
{
    public function data()
    {
        return [
            'section' => $this->block,
        ];
    }
}
