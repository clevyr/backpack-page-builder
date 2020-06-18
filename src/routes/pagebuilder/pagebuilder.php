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

Route::group([
    'namespace' => '',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix' => config('backpack.base.route_prefix', 'admin'),
], function () {
    Route::crud('pages', config('backpack.pagebuilder.admin_controller_class', 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PagesCrudController'));
    Route::get('pages/sync', 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderFilesController@sync');
    Route::get('pages/field', 'Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderFieldController@get');
});
