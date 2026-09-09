<?php

namespace BagistoPlus\Visual\View;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Craftile\Laravel\View\JsonViewParser;

/**
 * Skips Craftile's template cache while previewing draft data.
 *
 * Craftile keys its cache on the template path and mtime. Draft templates only store a diff plus a
 * "parent" pointer, so editing the parent leaves the child mtime untouched and the merged result
 * would be served from a stale entry.
 */
class DraftAwareJsonViewParser extends JsonViewParser
{
    public function parse(string $path): array
    {
        if (ThemeEditor::inDraftPreviewMode()) {
            return $this->runPipeline($path);
        }

        return parent::parse($path);
    }
}
