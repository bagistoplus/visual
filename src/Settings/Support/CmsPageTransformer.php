<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use Webkul\CMS\Repositories\PageRepository;

class CmsPageTransformer
{
    public function __invoke(?int $id)
    {
        $page = $id ? app(PageRepository::class)->find($id) : null;

        if (ThemeEditor::inDesignMode() && $page) {
            ThemeEditor::preloadModel('categories', $page);
        }

        return $page;
    }
}
