<?php

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| These routes are the publicly available generic routes, such
| as the landing, about us, FAQ, and contact pages.
|
*/
// subdomain routing for rep stores
Route::group(
    [
        'domain' => '{subdomain}.'. env('STORE_DOMAIN'),
    ],
    function () {
        Route::get('login', function ($subdomain) {
            // From Kelly Anderson: makes backoffice subdomain dynamic depending what is in .env
            if ($subdomain === strstr(env('APP_URL'), '.', true)) {
                return view('spa.index');
            }
            return redirect('//' . config('app.url') . '/login');
        });
        Route::get('/', function ($subdomain) {
            if ($subdomain === strstr(env('APP_URL'), '.', true)) {
                return view('spa.index');
            } elseif ($subdomain === 'cart') {
                return App::make('App\Http\Controllers\CartController')->show();
            }
            return App::make('App\Http\Controllers\UserSiteController')->repStore($subdomain);
        });
    }
);
Route::post('woocom/event', 'MyZoomLiveController@onWooComSubEvent');

Route::group([
    'middleware' => 'store'
], function () {
    Route::resource('cart', 'CartController');
    Route::get('customer-receipt', 'OrderController@receipt'); // Old

    Route::get('orders/create', 'OrderController@create');
    Route::post('orders/validate', 'OrderController@validateCustom');
    Route::post('orders', 'OrderController@store');
    Route::get('orders/complete', 'OrderController@complete');
    Route::get('orders/receipt', 'OrderController@localReceipt');
    // events store
    Route::get('store/events', 'EventsController@eventStore');
    Route::get('store/events/{id}', 'EventsController@eventShow');
    Route::get('store/events/{id}/products/{slug}', 'EventsController@productShow');
    // standard store
    Route::get('store/{id?}', ['as' => 'store', 'uses' => 'ProductController@storeFront']);
    Route::get('store/product/{id}', 'ProductController@publicShow');
    // public facing pages
    Route::get('/', 'PublicPagesController@getIndex');
    Route::get('/contact', 'PublicPagesController@getContact')->name('contact');
    Route::post('send-contact-form', 'EmailMessageController@contactUs');
    Route::get('/faq', 'PublicPagesController@getPage')->name('faq');
    Route::get('/privacy', 'PublicPagesController@getPage')->name('privacy');
    Route::get('return-policy', 'PublicPagesController@getPage')->name('return-policy');
    Route::get('/about', 'PublicPagesController@getAbout')->name('about');
    Route::get('/store/my-life', 'PublicPagesController@getMyLife')->name('my-life');
    Route::get('/terms', 'PublicPagesController@getPage')->name('terms');
});
