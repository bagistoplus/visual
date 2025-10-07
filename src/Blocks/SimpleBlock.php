<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Concerns\HasBlockBehavior;
use Craftile\Core\Concerns\ContextAware;
use Craftile\Core\Contracts\BlockInterface;

abstract class SimpleBlock implements BlockInterface
{
    use ContextAware, HasBlockBehavior;
}
