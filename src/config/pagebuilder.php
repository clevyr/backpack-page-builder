<?php

return [
    'pages_crud_controller' => 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderCrudController',
    'page_model_class' => 'Clevyr\PageBuilder\app\Models\Page',

    'pages_file_controller' => 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderFilesController',

    'pages_controller' => 'Clevyr\PageBuilder\app\Http\Controllers\PageController',

    'section_pivot_controller' => 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderSectionPivotCrudController',
    'page_sections_pivot_model' => 'Clevyr\PageBuilder\app\Models\PageSectionsPivot',
];
