<?php

$namespace = 'Ifantace\Common\Http\Controllers';

// Route::group([
//     'namespace' => $namespace,
//     'prefix' => 'helloworld',
// ], function () {
//     Route::get('/', 'HelloWorldController@index');
// });

//http://localhost/my_package/helloworld
Route::group([
    'namespace' => $namespace,
    'prefix' => 'common',
], function () {
    Route::get('/', 'CommonController@index');
});
