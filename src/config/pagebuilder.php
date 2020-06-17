<?php

return [
    'admin_controller_class' => 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PagesCrudController',
    'page_model_class' => 'Clevyr\PageBuilder\app\Models\Page',
    'page_sections_model_class' => 'Clevyr\PageBuilder\app\Models\PageSections',

    'page_views_path' => resource_path() . '/views/vendor/pagebuilder/views/',
    'section_views_path' => resource_path() . '/views/vendor/pagebuilder/sections/',
];
