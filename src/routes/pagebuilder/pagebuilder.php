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
    'namespace' => 'Clevyr\PageBuilder\app\Http\Controllers\Admin',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix' => config('backpack.base.route_prefix', 'admin'),
], function () {
    Route::crud('pages', 'PagesCrudController');
    Route::get('pages/sync', 'PageBuilderFilesController@sync');
});

Route::group([
    'namespace' => 'Clevyr\PageBuilder\app\Http\Controllers',
    'middleware' => ['web'],
], function () {
    // Catch all for pages
    Route::get('{page}/{subs?}', ['uses' => 'PageController@index'])
        ->where(['page' => '^(((?=(?!admin))(?=(?!\/)).))*$', 'subs' => '.*']);
});
