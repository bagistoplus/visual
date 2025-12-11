<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\BlockData;
use Craftile\Laravel\Facades\BlockDatastore;

class BlockRenderFilter
{
    protected ?array $renderSet = null;

    protected array $processed = [];

    /**
     * Check if a block should be rendered based on the _blocks query parameter.
     */
    public function shouldRender(BlockData $blockData): bool
    {
        if ($this->renderSet === null) {
            $this->buildRenderSet();
        }

        return isset($this->renderSet[$blockData->id]);
    }

    /**
     * Build the set of blocks to render from query parameter.
     */
    protected function buildRenderSet(): void
    {
        $changedBlocks = explode(',', request()->query('_blocks', ''));
        $this->renderSet = array_flip($changedBlocks);

        foreach ($changedBlocks as $blockId) {
            $this->expandBlockTree($blockId);
        }
    }

    /**
     * Recursively expand block tree to include all parents and children.
     */
    protected function expandBlockTree(string $blockId): void
    {
        if (isset($this->processed[$blockId])) {
            return;
        }

        $this->processed[$blockId] = true;

        try {
            $blockData = BlockDatastore::getBlock($blockId);

            if (! $blockData) {
                return;
            }

            $this->renderSet[$blockData->id] = true;

            // Walk up: include all parents
            if ($blockData->parentId) {
                $this->expandBlockTree($blockData->parentId);
            }

            // Walk down: include all children
            if ($blockData->hasChildren()) {
                foreach ($blockData->childrenIds() as $childId) {
                    $this->expandBlockTree($childId);
                }
            }
        } catch (\Exception $e) {
            // Block not found or error - skip silently
            return;
        }
    }

    /**
     * Reset the filter state (useful for testing).
     */
    public function reset(): void
    {
        $this->renderSet = null;
        $this->processed = [];
    }
}
