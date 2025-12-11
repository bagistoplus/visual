<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\BlockData;

class BlockRenderFilter
{
    protected array $blockIds;

    public function __construct()
    {
        $key = request()->query('_vkey');

        if ($key) {
            $this->blockIds = session()->get("visual.render.{$key}", []);
            session()->forget("visual.render.{$key}");
        } else {
            $this->blockIds = [];
        }
    }

    /**
     * Check if a block should be rendered.
     */
    public function shouldRender(BlockData $blockData): bool
    {
        // No filter = render all blocks
        if (empty($this->blockIds)) {
            return true;
        }

        // Check if block is in render set
        return in_array($blockData->id, $this->blockIds);
    }

    /**
     * Reset the filter state (useful for testing).
     */
    public function reset(): void
    {
        $this->blockIds = [];
    }
}
