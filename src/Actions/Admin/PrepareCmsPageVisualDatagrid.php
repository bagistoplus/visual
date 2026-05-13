<?php

namespace BagistoPlus\Visual\Actions\Admin;

use BagistoPlus\Visual\Support\ChannelThemeResolver;
use BagistoPlus\Visual\Support\CmsPageVisualEditorUrlResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\DataGrid\DataGrid;

class PrepareCmsPageVisualDatagrid
{
    protected const EVENT_NAMES = [
        'cms_page_data_grid',
        'c_m_s_page_data_grid',
    ];

    public function __construct(
        protected ChannelThemeResolver $themes,
        protected CmsPageVisualEditorUrlResolver $urls,
    ) {}

    public function listen(): void
    {
        foreach (self::EVENT_NAMES as $eventName) {
            Event::listen("datagrid.{$eventName}.query_builder.prepare.after", function (DataGrid $datagrid) {
                app(self::class)->prepareQuery($datagrid);
            });

            Event::listen("datagrid.{$eventName}.actions.prepare.after", function (DataGrid $datagrid) {
                app(self::class)->prepareActions($datagrid);
            });
        }
    }

    public function prepareQuery(DataGrid $datagrid): void
    {
        if (! $this->themes->resolveDefault()) {
            return;
        }

        $datagrid->getQueryBuilder()
            ->leftJoin('visual_template_assignments', function ($join) {
                $join->on('cms_pages.id', '=', 'visual_template_assignments.assignable_id')
                    ->where('visual_template_assignments.assignable_type', '=', 'page')
                    ->where('visual_template_assignments.template_type', '=', 'page')
                    ->whereNull('visual_template_assignments.channel')
                    ->whereColumn('visual_template_assignments.locale', 'cms_page_translations.locale');
            })
            ->addSelect(DB::raw('MAX(visual_template_assignments.template_key) as visual_template'));
    }

    public function prepareActions(DataGrid $datagrid): void
    {
        if (! $this->themes->resolveDefault()) {
            return;
        }

        $datagrid->addAction([
            'icon' => 'icon-magic',
            'title' => __('visual::admin.cms.open-in-visual-editor'),
            'method' => 'GET',
            'url' => fn ($row) => $this->urls->forRow($row),
        ]);
    }
}
