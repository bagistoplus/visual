<?php

namespace BagistoPlus\Visual\Settings\Support;

use Webkul\CMS\Repositories\PageRepository;

class CmsPageTransformer
{
    public function __invoke(?int $id)
    {
        return $id ? app(PageRepository::class)->find($id) : null;
    }
}
