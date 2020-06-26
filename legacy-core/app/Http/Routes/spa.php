<?php

Route::get('/{any}', function () {
    return view('spa.index');
})->where('any', '.*');
