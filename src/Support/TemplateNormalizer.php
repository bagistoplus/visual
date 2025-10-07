<?php

namespace BagistoPlus\Visual\Support;

/**
 * Normalizes legacy template format to Craftile's standard format.
 *
 * Transforms:
 * - sections → blocks
 * - settings → properties
 * - nested blocks → children
 */
class TemplateNormalizer
{
    /**
     * Normalize template data.
     */
    public function __invoke(array $templateData): array
    {
        if (isset($templateData['sections']) && ! isset($templateData['blocks'])) {
            $templateData['blocks'] = $templateData['sections'];
            unset($templateData['sections']);
        }

        if (isset($templateData['blocks']) && is_array($templateData['blocks'])) {
            $templateData['blocks'] = $this->normalizeBlocks($templateData['blocks']);
        }

        return $templateData;
    }

    /**
     * Normalize an array of blocks.
     */
    protected function normalizeBlocks(array $blocks): array
    {
        $normalized = [];

        foreach ($blocks as $key => $block) {
            if (is_array($block)) {
                $normalized[$key] = $this->normalizeBlock($block);
            } else {
                $normalized[$key] = $block;
            }
        }

        return $normalized;
    }

    /**
     * Normalize a single block.
     */
    protected function normalizeBlock(array $block): array
    {
        if (isset($block['settings']) && ! isset($block['properties'])) {
            $block['properties'] = $block['settings'];
            unset($block['settings']);
        }

        // Transform nested blocks → children if needed
        if (isset($block['blocks']) && ! isset($block['children'])) {
            // Check if this is nested blocks (array of block objects) vs. children (array of IDs)
            $firstItem = ! empty($block['blocks']) ? reset($block['blocks']) : null;
            $isNestedBlocks = is_array($firstItem) && (isset($firstItem['type']) || isset($firstItem['id']));

            if ($isNestedBlocks) {
                $block['children'] = $this->normalizeBlocks($block['blocks']);
            } else {
                // Just IDs, rename directly
                $block['children'] = $block['blocks'];
            }

            unset($block['blocks']);
        }

        // Recursively normalize children if they are nested block objects
        if (isset($block['children']) && is_array($block['children'])) {
            $firstChild = ! empty($block['children']) ? reset($block['children']) : null;
            $hasNestedChildren = is_array($firstChild) && (isset($firstChild['type']) || isset($firstChild['id']));

            if ($hasNestedChildren) {
                $block['children'] = $this->normalizeBlocks($block['children']);
            }
        }

        return $block;
    }
}
