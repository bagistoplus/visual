<?php

return [
    'sidebar' => [
        'visual' => 'Bagisto Visual',
        'themes' => 'Themes',
    ],

    'acl' => [
        'visual' => 'Bagisto Visual',
        'themes' => 'Themes',
        'editor' => 'Theme Editor',
        'preview' => 'Preview drafts and themes',
        'duplicate' => 'Duplicate themes',
        'edit' => 'Edit theme copies',
        'delete' => 'Delete theme copies',
    ],

    'themes' => [
        'title' => 'Themes',
        'customize' => 'Customize',
        'preview' => 'Preview',
        'preview-published' => 'Published version',
        'preview-draft' => 'Draft',
        'by' => 'by',
        'view-documentation' => 'View Documentation',
        'no-themes' => 'No themes found',
        'more-actions' => 'More actions',
        'duplicate' => 'Duplicate',
        'duplicate-title' => 'Duplicate :name',
        'copy-default-name' => ':name (copy)',
        'copy-name' => 'Name',
        'copy-description' => 'Description',
        'edit' => 'Edit',
        'edit-title' => 'Edit theme copy',
        'save' => 'Save',
        'delete' => 'Delete',
        'delete-confirm' => 'Delete this theme copy and all its customizations? This cannot be undone.',
        'based-on' => 'Based on :name',
        'copy-unavailable' => 'The theme this copy is based on is not installed.',
        'copy-created' => 'Theme duplicated.',
        'copy-updated' => 'Theme copy updated.',
        'copy-deleted' => 'Theme copy deleted.',
        'copy-in-use' => 'This theme copy is active on: :channels. Activate another theme on these channels before deleting it.',
    ],

    'cms' => [
        'edit-in-visual-editor' => 'Edit in Visual Editor',
        'open-in-visual-editor' => 'Open in Visual Editor',
    ],

    'template-assignment' => [
        'label' => 'Theme template',
        'default' => 'Default :type',
        'no-theme' => 'No visual theme is active for this channel.',
        'unavailable' => 'The selected theme template is not available for this context, so the assignment was not changed.',
    ],
];
