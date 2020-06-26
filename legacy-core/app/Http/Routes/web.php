<?php

Route::group([
    'middleware' => ['auth', 'userStatus:login'],
], function () {
    // media
    Route::get('media/create', 'MediaController@create');
    Route::get('media/{id}', 'MediaController@edit');
    Route::get('media/ajax/{id}', 'MediaController@showAJAX');
    Route::post('media/disable', 'MediaController@disable');
    Route::post('media/enable', 'MediaController@enable');
    Route::post('media/delete', 'MediaController@delete');
    Route::post('upload-media', 'MediaController@store');
});
