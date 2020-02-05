<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-02-05 20:07:51
 */

$namespace = 'Ifantace\Common\Http\Controllers';

use Illuminate\Support\Facades\Route;

// Route::group([
//     'namespace' => $namespace,
//     'prefix' => 'helloworld',
// ], function () {
//     Route::get('/', 'HelloWorldController@index');
// });
//http://localhost/my_package/helloworld
Route::group(
    [
        'namespace' => $namespace,
        'prefix' => 'common',
        'middleware' => [
            'log.request'
        ],
    ],
    function () {
        // Route::get('/', 'CommonController@index');
        Route::get('/download/{type}', 'CommonController@download');
    }
);
