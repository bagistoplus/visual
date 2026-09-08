<?php

return [
    [
        'key' => 'bagisto_visual',
        'name' => 'visual::admin.acl.visual',
        'route' => 'visual.admin.themes.index',
        'sort' => 7,
    ],
    [
        'key' => 'bagisto_visual.themes',
        'name' => 'visual::admin.acl.themes',
        'route' => 'visual.admin.themes.index',
        'sort' => 1,
    ],
    [
        'key' => 'bagisto_visual.editor',
        'name' => 'visual::admin.acl.editor',
        'route' => [
            'visual.admin.editor',
            'visual.admin.editor.api.persist',
            'visual.admin.editor.api.persist_settings',
            'visual.admin.editor.api.publish',
            'visual.admin.editor.api.templates.create',
            'visual.admin.editor.api.upload',
            'visual.admin.editor.api.images',
            'visual.admin.editor.api.videos.upload',
            'visual.admin.editor.api.videos.index',
            'visual.admin.editor.api.cms_pages',
            'visual.admin.editor.api.icons',
        ],
        'sort' => 2,
    ],
];
