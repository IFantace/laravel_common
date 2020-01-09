<?php
/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : Austin
 * @LastEditTime : 2020-01-09 20:58:02
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
    ],
    function () {
        // Route::get('/', 'CommonController@index');
        Route::get('/download_log', 'CommonController@downloadLog');
    }
);
