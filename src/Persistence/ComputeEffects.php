<?php

namespace BagistoPlus\Visual\Persistence;

class ComputeEffects
{
    public function __construct(
        protected RenderPreview $renderPreview
    ) {}

    public function execute(string $url, array $blocksData, array $loadedBlocks): array
    {
        $html = $this->renderPreview->execute($url);

        $blocksToProcess = $this->determineBlocksToProcess($blocksData, $loadedBlocks);

        $effects = [
            'html' => [],
            'css' => $this->extractStyles($html),
            'js' => $this->extractScripts($html),
        ];

        foreach ($blocksToProcess as $blockId) {
            $effects['html'][$blockId] = $this->extractBlockHtml($html, $blockId);
        }

        return $effects;
    }

    protected function determineBlocksToProcess(array $blocksData, array $loadedBlocks): array
    {
        $blocksToProcess = [];

        foreach ($blocksData as $blockId => $blockData) {
            if (isset($blockData['parentId']) && isset($blocksData[$blockData['parentId']])) {
                continue;
            }

            $repeatedAncestor = $this->findRepeatedAncestor($blockId, $loadedBlocks);

            if ($repeatedAncestor !== null) {
                $parentOfRepeated = $loadedBlocks[$repeatedAncestor]['parentId'] ?? null;
                if ($parentOfRepeated) {
                    $blocksToProcess[] = $parentOfRepeated;
                }

                continue;
            }

            if (isset($loadedBlocks[$blockId]['ghost']) && $loadedBlocks[$blockId]['ghost'] === true) {
                $parentOfGhost = $loadedBlocks[$blockId]['parentId'] ?? null;
                if ($parentOfGhost) {
                    $blocksToProcess[] = $parentOfGhost;
                }

                continue;
            }

            $blocksToProcess[] = $blockId;
        }

        return array_unique($blocksToProcess);
    }

    protected function findRepeatedAncestor(string $blockId, array $loadedBlocks): ?string
    {
        $currentId = $blockId;

        while (isset($loadedBlocks[$currentId]['parentId'])) {
            $parentId = $loadedBlocks[$currentId]['parentId'];

            if (! isset($loadedBlocks[$parentId])) {
                break;
            }

            if (
                isset($loadedBlocks[$parentId]['repeated']) &&
                $loadedBlocks[$parentId]['repeated'] === true
            ) {
                return $parentId;
            }

            $currentId = $parentId;
        }

        return null;
    }

    protected function extractBlockHtml(string $html, string $blockId): ?string
    {
        $pattern = '/<([^>]+)data-block="'.preg_quote($blockId, '/').'"([^>]*)>/';

        if (! preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $startPos = $matches[0][1];
        $openingTag = $matches[0][0];

        if (! preg_match('/<(\w+)/', $openingTag, $tagMatches)) {
            return null;
        }

        $tagName = $tagMatches[1];

        if (str_ends_with(trim($openingTag), '/>')) {
            return $openingTag;
        }

        return $this->findClosingTag($html, $tagName, $startPos, $openingTag);
    }

    protected function findClosingTag(string $html, string $tagName, int $startPos, string $openingTag): ?string
    {
        $searchPos = $startPos + strlen($openingTag);
        $depth = 1;
        $currentPos = $searchPos;

        while ($depth > 0 && $currentPos < strlen($html)) {
            $nextOpen = strpos($html, "<{$tagName}", $currentPos);
            $nextClose = strpos($html, "</{$tagName}>", $currentPos);

            if ($nextClose === false) {
                break;
            }

            if ($nextOpen !== false && $nextOpen < $nextClose) {
                $depth++;
                $currentPos = $nextOpen + strlen("<{$tagName}");
            } else {
                $depth--;
                if ($depth === 0) {
                    $endPos = $nextClose + strlen("</{$tagName}>");

                    return substr($html, $startPos, $endPos - $startPos);
                }
                $currentPos = $nextClose + strlen("</{$tagName}>");
            }
        }

        return null;
    }

    protected function extractStyles(string $html): array
    {
        $styles = [];

        if (preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $matches)) {
            $styles = array_merge($styles, $matches[0]);
        }

        if (preg_match_all('/<style[^>]*>.*?<\/style>/is', $html, $matches)) {
            $styles = array_merge($styles, $matches[0]);
        }

        return $styles;
    }

    protected function extractScripts(string $html): array
    {
        $scripts = [];

        if (preg_match_all('/<script[^>]*>.*?<\/script>/is', $html, $matches)) {
            $scripts = $matches[0];
        }

        return $scripts;
    }
}
