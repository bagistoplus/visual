<?php

namespace BagistoPlus\Visual\Support;

use BagistoPlus\Visual\Data\BlockData;

class BlockRenderFilter
{
    protected array $blockIds;

    /**
     * Ids this filter let through during the current request.
     */
    protected array $renderedIds = [];

    /**
     * Ids rendered because they sit under a static block that followed its parent.
     */
    protected array $followedIds = [];

    public function __construct()
    {
        $key = request()->query('_visual_render');

        if ($key) {
            $this->blockIds = session()->get("visual.render.{$key}", []);
            session()->forget("visual.render.{$key}");
        } else {
            $this->blockIds = [];
        }
    }

    /**
     * Check if a block should be rendered.
     *
     * The render set only knows blocks reachable from the persisted children
     * lists. A static block is declared by its parent's template, so it renders
     * whenever its parent renders, and so does everything under it.
     */
    public function shouldRender(BlockData $blockData): bool
    {
        // No filter = render all blocks
        if (empty($this->blockIds)) {
            return true;
        }

        if (! $this->allows($blockData)) {
            return false;
        }

        $this->renderedIds[$blockData->id] = true;

        return true;
    }

    protected function allows(BlockData $blockData): bool
    {
        if (in_array($blockData->id, $this->blockIds)) {
            return true;
        }

        $parentId = $blockData->parentId;

        if (! $parentId) {
            return false;
        }

        if ($blockData->static && isset($this->renderedIds[$parentId])) {
            $this->followedIds[$blockData->id] = true;

            return true;
        }

        if (isset($this->followedIds[$parentId])) {
            $this->followedIds[$blockData->id] = true;

            return true;
        }

        return false;
    }

    /**
     * Reset the filter state (useful for testing).
     */
    public function reset(): void
    {
        $this->blockIds = [];
        $this->renderedIds = [];
        $this->followedIds = [];
    }
}
