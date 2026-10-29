<?php

return [
    'sidebar' => [
        'visual' => 'Bagisto Visual',
        'themes' => 'Temas',
    ],

    'acl' => [
        'visual' => 'Bagisto Visual',
        'themes' => 'Temas',
        'editor' => 'Editor de temas',
        'preview' => 'Previsualizar borradores y temas',
        'duplicate' => 'Duplicar temas',
        'edit' => 'Editar copias de temas',
        'delete' => 'Eliminar copias de temas',
    ],

    'themes' => [
        'title' => 'Temas',
        'customize' => 'Personalizar',
        'preview' => 'Vista previa',
        'preview-published' => 'Versión publicada',
        'preview-draft' => 'Borrador',
        'by' => 'por',
        'view-documentation' => 'Ver documentación',
        'no-themes' => 'No se encontraron temas',
        'more-actions' => 'Más acciones',
        'duplicate' => 'Duplicar',
        'duplicate-title' => 'Duplicar :name',
        'copy-default-name' => ':name (copia)',
        'copy-name' => 'Nombre',
        'copy-description' => 'Descripción',
        'edit' => 'Editar',
        'edit-title' => 'Editar copia del tema',
        'save' => 'Guardar',
        'delete' => 'Eliminar',
        'delete-confirm' => '¿Eliminar esta copia del tema y todas sus personalizaciones? Esta acción no se puede deshacer.',
        'based-on' => 'Basado en :name',
        'copy-unavailable' => 'El tema en el que se basa esta copia no está instalado.',
        'copy-created' => 'Tema duplicado.',
        'copy-updated' => 'Copia del tema actualizada.',
        'copy-deleted' => 'Copia del tema eliminada.',
        'copy-in-use' => 'Esta copia del tema está activa en: :channels. Activa otro tema en estos canales antes de eliminarla.',
    ],

    'cms' => [
        'edit-in-visual-editor' => 'Editar en Visual Editor',
        'open-in-visual-editor' => 'Abrir en Visual Editor',
    ],

    'template-assignment' => [
        'label' => 'Plantilla del tema',
        'default' => ':type predeterminado',
        'no-theme' => 'No hay un tema visual activo para este canal.',
        'unavailable' => 'La plantilla de tema seleccionada no está disponible para este contexto, por lo que la asignación no se cambió.',
    ],
];
