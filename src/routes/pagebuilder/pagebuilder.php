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

use Clevyr\PageBuilder\app\Console\Commands\CreatePage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Admin
Route::group([
    'namespace' => '',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix' => config('backpack.base.route_prefix', 'admin'),
], function () {
    $page_builder_crud_controller = config('backpack.pagebuilder.pages_crud_controller',
        'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderCrudController');

    $page_builder_files_controller = config('backpack.pagebuilder.pages_file_controller',
        'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderFilesController');

    // Crud Controller
    Route::crud('pages', $page_builder_crud_controller);

    // Files controller
    Route::get('pages/sync', $page_builder_files_controller. '@sync');

    // Restore Operation
    Route::post('pages/{id}/restore', $page_builder_crud_controller . '@restore');

    // Force Delete
    Route::delete('pages/{id}/forceDelete', $page_builder_crud_controller . '@forceDelete');
});

// Frontend
Route::group([
    'namespace' => '',
    'middleware' => ['web'],
], function () {
    Route::get('/aa', function () {
        Artisan::call('pagebuilder:page', ['name' => 'command']);
    });

    // Catch all for pages
    Route::get('{page}/{subs?}',
        ['uses' =>
            config('backpack.pagebuilder.pages_controller',
                'Clevyr\PageBuilder\app\Http\Controllers\PageController')
            . '@index'
        ])
        ->where(['page' => '^(((?=(?!admin))(?=(?!\/)).))*$', 'subs' => '.*']);
});
