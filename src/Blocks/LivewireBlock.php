<?php

namespace BagistoPlus\Visual\Blocks;

use BagistoPlus\Visual\Concerns\HasBlockBehavior;
use BagistoPlus\Visual\Contracts\ConditionalBlockInterface;
use BagistoPlus\Visual\Data\BlockData;
use Craftile\Core\Contracts\BlockInterface;
use Livewire\Component;

class LivewireBlock extends Component implements BlockInterface, ConditionalBlockInterface
{
    use HasBlockBehavior;

    public array $context;

    public BlockData $block;

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setBlock(BlockData $block)
    {
        $this->block = $block;
    }

    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Get a value from the context by key.
     *
     * @param  string  $key  The context key to retrieve
     * @param  mixed  $default  Default value if key doesn't exist
     * @return mixed The context value or default
     */
    public function context(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
}
