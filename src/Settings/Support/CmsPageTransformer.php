<?php

namespace BagistoPlus\Visual\Settings\Support;

use BagistoPlus\Visual\Facades\ThemeEditor;
use BagistoPlus\Visual\Support\ResolvedModelCache;
use Webkul\CMS\Repositories\PageRepository;

class CmsPageTransformer
{
    public function __invoke(?int $id, array $schema = [])
    {
        if (! $id) {
            return null;
        }

        $page = app(ResolvedModelCache::class)->rememberCmsPage(
            $id,
            fn () => app(PageRepository::class)->find($id)
        );

        if (ThemeEditor::inDesignMode() && $page) {
            ThemeEditor::preloadModel('cms_pages', $page);
        }

        return $page;
    }
}
