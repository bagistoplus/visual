<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Concerns\HasBlockBehavior;
use BagistoPlus\Visual\Contracts\ConditionalBlockInterface;
use BagistoPlus\Visual\Data\BlockData;
use Craftile\Core\Concerns\ContextAware;
use Craftile\Core\Contracts\BlockInterface;

abstract class SimpleBlock implements BlockInterface, ConditionalBlockInterface
{
    use ContextAware, HasBlockBehavior;

    protected BlockData $block;

    public function setBlockData(BlockData $block)
    {
        $this->block = $block;
    }
}
