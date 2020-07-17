<?php

/*
|--------------------------------------------------------------------------
| Clevyr\PageBuilder Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Clevyr\PageBuilder package.
|
*/

use Illuminate\Support\Facades\Route;

// Admin
Route::group([
    'namespace' => '',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix' => config('backpack.base.route_prefix', 'admin'),
], function () {
    Route::crud('pages', config('backpack.pagebuilder.pages_crud_controller',
        'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderCrudController'));

    Route::get('pages/sync',
        config('backpack.pagebuilder.pages_file_controller',
            'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderFilesController')
        . '@sync');
});

// Frontend
Route::group([
    'namespace' => '',
    'middleware' => ['web'],
], function () {
    // Catch all for pages
    Route::get('{page}/{subs?}',
        ['uses' =>
            config('backpack.pagebuilder.pages_controller',
                'Clevyr\PageBuilder\app\Http\Controllers\PageController')
            . '@index'
        ])
        ->where(['page' => '^(((?=(?!admin))(?=(?!\/)).))*$', 'subs' => '.*']);
});
