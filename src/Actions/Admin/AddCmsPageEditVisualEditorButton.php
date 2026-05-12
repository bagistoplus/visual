<?php

namespace BagistoPlus\Visual\Actions\Admin;

use BagistoPlus\Visual\Support\CmsPageVisualEditorUrlResolver;
use Webkul\Theme\ViewRenderEventManager;

class AddCmsPageEditVisualEditorButton
{
    public function __construct(protected CmsPageVisualEditorUrlResolver $urls) {}

    public function __invoke(ViewRenderEventManager $viewRenderEventManager): void
    {
        $url = $this->urls->forPage($viewRenderEventManager->getParam('page'));

        if (! $url) {
            return;
        }

        $viewRenderEventManager->addTemplate(
            view()->make('visual::admin.cms.edit-in-visual-editor', ['url' => $url])->render()
        );
    }
}
