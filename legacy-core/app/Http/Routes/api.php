<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes should only be used for accepting and responding
| to AJAX calls from our front-end JavaScript. Most if
| not all of these routes should be private.
|
*/

Route::group([
    'prefix' => '/api',
    'namespace' => 'Api',
    'middleware' => ['api.log', 'api.cors']
], function () {
    // NOTE: Public APIs (routes are logged and cors is enabled, but no authentication is necessary)
    Route::match(['options'], '{any?}'); // required for the cors middleware to be triggered on an options request
    Route::get('ping', 'MonitoringController@simplePing');

    // Route::group([ 'middleware' => ['api.auth'] ], function () {
    //     // NOTE: Private APIs (routes must be authenticated but no particular role is required)
    //     Route::group([ 'middleware' => ['api.roles:admin,superadmin'] ], function () {
    //         // NOTE: Role Specific APIs (routes require either an admin or superadmin role)
    //     });
    // });
});

Route::group([
    'prefix' => '/api/v1',
    'namespace' => 'Api\V1',
    'middleware' => ['api.log', 'api.cors']
], function () {
    // NOTE: Universal APIs (routes are logged and cors is enabled, but neither a jwt nor org id is necessary)

    // ensure moat middleware is triggered on all requests using the options method
    Route::match(['options'], '{any?}');

    Route::get('settings', 'SettingsController@index');

    Route::group([
        'middleware' => ['api.org']
    ], function () {
        // NOTE: Public APIs (routes are logged, cors enabled, org id required, but no authentication is necessary)

        // registration
        Route::group([
            'middleware' => 'api', //'setting:register_without_controlpad_api' // TODO is this middleware needed?
        ], function () {
            Route::post('register/validate-user', 'RegistrationController@validateUser');
            Route::get('register/plans', 'RegistrationController@getIndexOnSignUp');
            Route::get('user-token/{token}', 'RegistrationController@getUserToken');
            Route::post('register/validate-splash', 'RegistrationController@validateSplashAccount');
            Route::get('register/check-public-id/{public_id}', 'RegistrationController@checkPublicId');
        });

        // barcodes
        Route::get('barcode/img/{text}/{size?}/{scale?}/{codeType?}/{orientation?}', 'BarcodeController@barcodeAsPng');
        Route::get('barcode/url/{text}/{size?}/{scale?}/{codeType?}/{orientation?}', 'BarcodeController@barcodeAsDataUrl');

        // bundles
        Route::get('bundles/bundles-by-role', 'BundleController@bundlesByRole');
        Route::get('bundles/show/{id}', 'BundleController@show');

        // Newer cart routes to fix some issues
        Route::post('carts', 'CartController@create');
        Route::get('carts/{pid}', 'CartController@getByPid');
        Route::post('carts/{pid}/lines', 'CartController@addItem');
        Route::patch('carts/{pid}/lines', 'CartController@updateItem');
        Route::delete('carts/{pid}/lines/{itemID}', 'CartController@removeItem');

        // cart
        Route::post('cart/show', 'CartController@postShow');
        Route::patch('cart', 'CartController@patchIndex');
        Route::patch('cart/cartline', 'CartController@patchCartline');
        Route::delete('cart/cartline/{id}', 'CartController@deleteCartline');
        Route::patch('cart/bundle', 'CartController@patchBundle');
        Route::put('cart/bundle', 'CartController@putBundle');
        Route::post('cartlines/wholesale', 'CartController@createWholesaleCartlines');
        Route::post('cartlines/custom', 'CartController@createCustomCartlines');

        // categories
        Route::get('categories', 'CategoryController@index');
        Route::get('categories/hierarchy', 'CategoryController@getHierarchy');
        Route::get('categories/children', 'CategoryController@children');
        Route::get('categories/{id}', 'CategoryController@show');

        // checkout
        Route::post('coupons/apply/{code}', 'CouponController@apply');
        Route::get('invoice/{token}', 'InvoiceController@show');
        Route::post('invoices/{uid}/coupon', 'InvoiceController@applyCoupon');
        Route::get('inventory/check-availability', 'InventoryController@checkAvailability');
        Route::get('inventory/bundle-check-availability', 'InventoryController@bundleCheckAvailability');
        Route::post('custom-order/tax', 'CustomOrderController@tax');

        // items
        Route::get('items', 'ItemController@index');
        Route::get('items/{id}', 'ItemController@show');
        Route::delete('items/{id}', 'ItemController@delete');

        // live videos
        Route::get('live-videos/public/{id}', 'LiveVideoController@public');

        //pages
        Route::get('pages/{slug}', 'PublicPageController@show');

        Route::get('products/all', 'ProductController@all');
        Route::get('products/show/{id}', 'ProductController@show');
        Route::get('products/type', 'ProductController@type');
        Route::get('rep-locator/search/{zip}/{radius}', 'RepLocatorController@search');
        // rep locator get inventory
        Route::get('rep-locator/inventory/{id}', 'RepLocatorController@repInventory');
        Route::post('rep-locator/nearby-reps', 'RepLocatorController@nearbyReps');
        Route::get('rep-locator/geocoord/{latitude}/{longitude}', 'RepLocatorController@geocodeCoords');
        Route::get('rep-locator/products', 'RepLocatorController@productList');
        Route::post('rep-locator/searchProduct/{id}', 'RepLocatorController@searchProduct');

        // payQuicker
        Route::get('payment/paymentLists', 'PayQuickerController@getPaymentLists');
        Route::get('payment/payments', 'PayQuickerController@getPayment');
        Route::get('payment/submit/{id}', 'PayQuickerController@submitPayment');
        Route::get('payment/details/{id}', 'PayQuickerController@paymentDetails');
        Route::get('payment/csv/payment', 'PayQuickerController@downloadCsvPayQuickerPaymentList');
        Route::get('payment/csv/detail/{id}', 'PayQuickerController@downloadCsvPayQuickerDetail');

        // locators
        Route::get('locator/rep/search', 'LocatorController@searchRep');

        // registration
        Route::post('register', 'RegistrationController@register');

        // orders
        Route::get('orders/all', 'OrderController@allOrdersAndInvoices');
        Route::get('orders/order-types', 'OrderController@orderTypes');
        Route::get('orders/show/{receiptId}', 'OrderController@show');
        Route::post('orders/validate-basic-customer-info', 'OrderController@validateCustomerInfo');
        Route::get('orders', 'OrderController@index');


        // states
        Route::get('states', 'StateController@getIndex');

        // starter kits
        Route::get('starter-kits', 'BundleController@starterKits');

        // subscriptions
        Route::post('subscriptions/tax', 'SubscriptionController@getTax');
        Route::post('subscriptions/taxAdmin', 'SubscriptionController@getTaxAdmin');

        // tags
        Route::get('tags/product-tags', 'TagController@getProductTags');

        // login settings
        Route::get('settings/login-settings', 'SettingsController@showLoginSettings');

        // Shipping Rates
        Route::get('shipping-rate/shipping-cost', 'ShippingRateController@shippingCost');

        // user status index
        Route::get('user-status', 'UserStatusController@index');

        // store template routes (cors enabled)
        Route::post('store', 'TemplateStoreController@index');
        Route::post('store/inventory-check', 'TemplateStoreController@checkAvailability');
        Route::post('store/cart', 'TemplateStoreController@cart');
        Route::post('store/product', 'TemplateStoreController@product');
        Route::post('store/process', 'TemplateStoreController@process');
        Route::post('categories/{id}/products', 'TemplateStoreController@productsByCategory');

        Route::group([
            'middleware' => ['api.auth']
        ], function () {
            // NOTE: Private APIs (routes must be authenticated but no particular role is required)

            // setting locked user route
            Route::group([
                'middleware' => 'setting:rep_edit_information' // TODO is this middleware needed?
            ], function () {
                Route::put('user/update/{id}', 'UserController@update');
            });

            Route::group([
                'middleware' => ['api.roles:superadmin']
            ], function () {
                Route::get('direct-deposit/account-index', 'DirectDepositController@getUserAccountIndex');
                Route::get('direct-deposit/batch-index', 'DirectDepositController@getBatchIndex');
                Route::post('direct-deposit/batch-submit/{id}', 'DirectDepositController@postBatchSubmitted');
                Route::get('direct-deposit/detail', 'DirectDepositController@getDetail');
                Route::get('direct-deposit/validations', 'DirectDepositController@getValidations');
                Route::get('direct-deposit/download/{id}', 'DirectDepositController@getDownload');
                Route::get('direct-deposit/batch-id/{id}', 'DirectDepositController@getBatchID');
                Route::get('settings/commission-engine', 'CommissionEngineController@getCommissionEngineSettings');
                Route::get('subscriptions/transactions', 'SubscriptionController@transactionReport');
                Route::post('inventory/expiration', 'InventoryController@updateExpirationDate');
                Route::get('commission-engine/backfill/users', 'CommissionEngineController@backfillUsers');
                Route::get('commission-engine/backfill/error-users', 'CommissionEngineController@backfillErrorUsers');
                Route::get('commission-engine/backfill/error-orders', 'CommissionEngineController@backfillErrorOrders');
                Route::get('commission-engine/backfill', 'CommissionEngineController@backfill');
                Route::get('commission-engine/users/{status}', 'CommissionEngineController@getUsersByCommissionStatus');
                Route::get('commission-engine/orders/{status}', 'CommissionEngineController@getOrdersByCommissionStatus');
                Route::get('subscriptions/renewable', 'SubscriptionController@renewableUsers');
                Route::post('subscriptions/renewable-pay', 'SubscriptionController@adminPay');
                Route::post('user/edit-join-date', 'UserController@editJoinDate');
                Route::get('banking/csv/all-users', 'BankController@downloadCsvAllUsers');
            });

            Route::group([
                'middleware' => ['api.roles:admin,superadmin']
            ], function () {
                // NOTE: Role Specific APIs (routes require either an admin or superadmin role)

                // protected announcement routes
                Route::delete('announcements/{id}', 'AnnouncementController@delete');

                // protected batch labels routes
                Route::get('batch', 'BatchLabelController@index');
                Route::get('batch/{id}', 'BatchLabelController@show');
                Route::post('batch', 'BatchLabelController@create');
                Route::post('batch/validate', 'BatchLabelController@validateBatch');
                Route::post('batch/update', 'BatchLabelController@update');
                Route::post('batch/shipment', 'BatchLabelController@updateShipment');
                Route::delete('batch/shipment/{id}', 'BatchLabelController@deleteShipment');
                Route::post('batch/purchase', 'BatchLabelController@purchase');
                Route::delete('batch/{id}', 'BatchLabelController@delete');

                // protected category routes
                Route::post('categories', 'CategoryController@create');
                Route::patch('categories/{id}', 'CategoryController@update');
                Route::delete('categories/{id}', 'CategoryController@delete');
                Route::patch('categories/placement/{id}', 'CategoryController@placement');

                // custom links
                Route::get('custom-links', 'CustomLinksController@getIndexByType');
                Route::post('custom-links', 'CustomLinksController@create');
                Route::delete('custom-links/{id}', 'CustomLinksController@delete');

                // protected email routes
                Route::get('email', 'EmailMessageController@customEmailIndex');
                Route::post('email/update/{title}', 'EmailMessageController@updatecustomEmailIndex');
                Route::get('email/show/{title}', 'EmailMessageController@showEmail');

                // protected dircet deposit routes
                Route::get('direct-deposit/users', 'DirectDepositController@getValidatedUsers');

                // protected history controller
                Route::get('history', 'HistoryController@getIndex');
                Route::get('history/model/{model}', 'HistoryController@getModel');
                Route::get('history/id/{model}/{id}', 'HistoryController@getId');


                // protected inventory routes
                Route::post('inventory/csv-import', 'InventoryController@csvImport');
                Route::get('inventory/csv-export', 'InventoryController@csvExport');
                Route::post('inventory/relist', 'InventoryController@relistFulfilledByCorporate');

                // protected media routes
                Route::patch('media/enable', 'MediaController@enable');
                Route::patch('media/disable', 'MediaController@disable');

                // protected order routes
                Route::post('orders/export', 'ShippingController@exportOrders');

                //Page
                Route::post('pages/create', 'PublicPageController@createUpdate');
                Route::post('pages/create-revised', 'PublicPageController@createRevisedUpdate');
                Route::get('pages', 'PublicPageController@index');

                // Parcel Templates
                Route::post('parcels/rep-enable', 'ParcelTemplateController@repEnable');

                // protected report routes
                Route::get('report/sales/affiliate', 'SalesReportController@getAffiliateIndex');
                Route::get('report/sales/affiliate_user/{id}', 'SalesReportController@getAffiliate');
                Route::get('report/sales/rep', 'SalesReportController@getRepIndex');
                Route::get('report/sales/rep-transfer', 'SalesReportController@getRepTransferIndex');
                Route::get('report/sales/fbc', 'SalesReportController@getFbcIndex');
                Route::get('report/sales/corp', 'SalesReportController@getCorpIndex');
                Route::get('report/sales/cust', 'SalesReportController@getCustIndex');
                Route::get('report/sales/corp/total', 'SalesReportController@getCorpTotal');
                Route::get('report/sales/corp/{orderType}', 'SalesReportController@getCorpIndex');
                Route::get('report/sales/{orderType}', 'SalesReportController@getCorpIndex');
                Route::get('report/sales/cust/{orderType}', 'SalesReportController@getCustIndex');
                Route::get('report/sales/{orderType}', 'SalesReportController@getCustIndex');
                Route::get('report/tax/total', 'SalesReportController@getTaxTotal');
                Route::get('report/tax/owedByUser', 'SalesReportController@taxOwedByUser');
                Route::get('report/sales/affiliate/total', 'SalesReportController@getAffiliateTotal');
                Route::get('report/csv/corp', 'SalesReportController@downloadCsvCorpSales');
                Route::get('report/csv/corpsendmail', 'SalesReportController@sendmailCsvCorpSales');
                Route::get('report/csv/repTotal', 'SalesReportController@downloadCsvRepSalesTotal');
                Route::get('report/csv/repOrder', 'SalesReportController@downloadCsvRepSalesOrder');
                Route::get('report/csv/fbcOrder', 'SalesReportController@downloadCsvFbcSalesOrder');
                Route::get('report/csv/tax', 'SalesReportController@downloadCsvTaxSales');
                Route::get('report/csv/fbc', 'SalesReportController@downloadCsvFbcSales');
                Route::get('report/csv/affiliateTotal', 'SalesReportController@downloadCsvAffiliateSalesTotal');
                Route::get('report/csv/affiliateOrder', 'SalesReportController@downloadCsvAffiliateSalesOrder');
                Route::get('report/csv/taxOwedCSV', 'SalesReportController@downloadTaxesOwedReportCSV');
                Route::get('reports/emails', 'EmailMessageController@emailLogs');
                Route::get('report/isAffiliate', 'ReportsController@isAffiliate');

                // protected sales routes
                Route::get('sales/reps', 'SalesController@getRepSales');

                // protected user routes
                Route::get('user', 'UserController@index');
                Route::get('user/reps', 'UserController@reps');
                Route::get('users/search/reps', 'UserController@searchReps');
                Route::get('users/search/sponsors', 'UserController@searchSponsors');
                Route::post('user/create', 'UserController@create');
                Route::post('user/delete', 'UserController@delete');
                Route::get('user/csv', 'UserController@downloadCsv');
                Route::post('user-status/update-status', 'UserStatusController@updateUserStatus');

                // Protect shipping routes
                Route::get('shipping/settings', 'ShippingController@settings');

                // Protected subscription routes
                Route::post('subscriptions/create', 'SubscriptionController@create');
                Route::put('subscriptions/edit/{id}', 'SubscriptionController@edit');
                Route::delete('subscriptions/{id}', 'SubscriptionController@delete');
                Route::get('subscriptions/show-plan/{id}', 'SubscriptionController@showPlan');
                Route::get('subscriptions/user-subscriptions', 'SubscriptionController@userSubscriptions');
                Route::post('subscriptions/update-auto-renew', 'SubscriptionController@autoRenewUpdate');
                Route::post('subscriptions/update-ends-at', 'SubscriptionController@updateEndsAt');
                Route::get('subscriptions/csv-download', 'SubscriptionController@csvDownloadSubscriptions');
                Route::get('subscriptions/csv-download-receipt', 'SubscriptionController@csvDownloadSubscriptionsReceipt');
                Route::get('subscriptions/csv-sendmail-receipt', 'SubscriptionController@csvsendmailSubscriptionsReceipt');
                Route::get('subscriptions/all-receipt', 'SubscriptionController@allReceipt');

                // protected settings routes
                Route::get('settings/rep', 'SettingsController@showRepSettings');
                Route::get('settings/inventory', 'SettingsController@showInventorySettings');
                Route::get('settings/registration', 'SettingsController@showRegistrationSettings');
                Route::post('settings/rep/save', 'SettingsController@updateRepSettings');
                Route::get('settings/shipping', 'SettingsController@showShippingSettings');
                Route::get('settings/taxes', 'SettingsController@showTaxSettings');
                Route::get('settings/general-store', 'SettingsController@showGeneralStoreSettings');
                Route::get('settings/events', 'SettingsController@showEventSettings');

                //settings
                Route::post('settings/update', 'SettingsController@update');
                Route::get('settings/show/{user_id}', 'SettingsController@show'); // TODO: only used in a test?
                Route::get('settings/category/{category}', 'SettingsController@showSettingsCategory');
                Route::get('settings/blacklist', 'SettingsController@showBlacklisted');
                Route::post('settings/updateBlacklist', 'SettingsController@updateBlacklist');

                // release notes
                Route::get('/release-notes', 'ReleaseNotesController@getMerges');

                // Admin protected announcement routes
                Route::post('announcements', 'AnnouncementController@create');
                Route::patch('announcements/{id}', 'AnnouncementController@update');

                Route::post('variants/claim-number', 'ItemController@updateVariantClaimNumber');
            });
            // Announcements
            Route::get('announcements', 'AnnouncementController@getIndex');
            // Address
            Route::post('address/create', 'AddressController@postCreate');
            Route::post('address/create-or-update', 'AddressController@postCreateOrUpdate');
            Route::get('address/show/', 'AddressController@getShow');

            Route::get('bank/show/{user_id}', 'BankController@show');
            Route::put('bank/update', 'BankController@updateCreate');
            Route::put('bank/verify', 'BankController@verify');
            Route::get('bank/check-billing', 'BankController@checkBillingForCardUpdate');

            // protected bundle routes
            Route::get('bundles', 'BundleController@index');
            Route::post('bundles/create', 'BundleController@create');
            Route::put('bundles/edit/{id}', 'BundleController@edit');
            Route::delete('bundles/delete/{id}', 'BundleController@delete');
            Route::get('bundles/bundles-by-role-fulfilled', 'BundleController@bundlesByRoleFulfilled');

            // Coupons
            Route::get('coupons', 'CouponController@index');
            Route::get('coupons/show-order/{id}', 'CouponController@showCoupon');
            Route::get('coupons/csv', 'CouponController@downloadCsvCoupon');
            Route::get('coupons/applied/csv', 'CouponController@downloadAppliedCoupons');
            Route::get('coupons/{id}', 'CouponController@show');
            Route::post('coupons', 'CouponController@store');
            Route::delete('coupons/{id}', 'CouponController@destroy');

            // Dashboard
            Route::get('dashboard/sales-volume', 'DashboardController@salesVolume');

            //Email
            Route::get('emails', 'SettingsController@emailIndex');
            Route::get('emails/byslug/{slug}', 'SettingsController@getEmailBySlug');
            Route::get('emails/show/{user_id}', 'SettingsController@showEmail');
            Route::get('emails/show_id/{id}', 'SettingsController@showId');
            Route::post('emails/create', 'SettingsController@createEmail');
            Route::put('emails/update/{id}', 'SettingsController@updateEmail');

            Route::get('emailMessages', 'EmailMessageController@getIndex');

            //Inventory
            Route::post('inventory/save-quantity', 'InventoryController@saveQuantity');
            Route::get('inventory/fulfilled-by-corporate', 'InventoryController@fulfilledByCorporate');

            // Note
            Route::get('notes', 'NoteController@getIndex');
            Route::get('notes/related-notes/{id}/{type}', 'NoteController@getRelatedNotes');
            Route::post('notes/create', 'NoteController@postCreate');
            Route::post('notes/edit', 'NoteController@postEdit');
            Route::delete('notes/delete/{id}', 'NoteController@deleteDelete');

            Route::get('order-status', 'OrderStatusController@index');

            // Parcel Templates
            Route::get('parcels', 'ParcelTemplateController@index');
            Route::get('parcels/all', 'ParcelTemplateController@all');
            Route::get('parcels/{id}', 'ParcelTemplateController@show');
            Route::post('parcels', 'ParcelTemplateController@create');
            Route::post('parcels/update', 'ParcelTemplateController@update');
            Route::post('parcels/enable', 'ParcelTemplateController@enable');
            Route::delete('parcels/{id}', 'ParcelTemplateController@delete');

            // protected product routes
            Route::get('products', 'ProductController@index');
            Route::post('products/create', 'ProductController@create');
            Route::get('products/show-edit/{id}', 'ProductController@showEdit');
            Route::put('products/edit', 'ProductController@edit');
            Route::delete('products/delete/{id}', 'ProductController@delete');

            // Reports
            Route::get('report/sales/rep/total', 'SalesReportController@getRepTotal');
            Route::get('report/sales/rep/{id}', 'SalesReportController@getRep');
            Route::get('report/sales/rep/total/{id}', 'SalesReportController@getRepTotal');
            Route::get('report/sales/rep-transfer/total', 'SalesReportController@getRepTransferTotal');
            Route::get('report/sales/fbc/total', 'SalesReportController@getFbcTotal');
            Route::get('report/sales/fbc/{id}', 'SalesReportController@getFbcUser');
            Route::get('report/sales/fbc/total/{id}', 'SalesReportController@getFbcTotal');

            // roles
            Route::get('roles', 'RoleController@getIndex');
            Route::get('roles/admin-creatable', 'RoleController@adminCreatableRoles');

            //Sales
            Route::get('sales', 'SalesController@getIndex');

            //Shipping rates
            Route::get('custom-shipping-rate', 'ShippingRateController@customOrderShippingRates');

            // Sms Messages
            Route::get('smsMessages', 'SmsMessageController@index');

            // ewallet
            Route::get('ewallet/sales-tax', 'EwalletController@getSalesTax');
            Route::get('ewallet/processing-fees', 'EwalletController@getProcessingFees');
            Route::get('ewallet/dashboard-report', 'EwalletController@getDashboardReport');
            Route::get('eWallet/user', 'EwalletController@userWithAddress');
            Route::get('ewallet/transactions', 'EwalletController@getTransactions');
            Route::get('ewallet/transaction/{transactionId}', 'EwalletController@getTransaction');
            Route::get('ewallet/payments', 'EwalletController@getPayments');
            Route::get('ewallet/ledger', 'EwalletController@getLedger');
            Route::get('ewallet/tax-ledger/', 'EwalletController@getCashTax');
            Route::get('ewallet/csv-sales-tax', 'EwalletController@downloadCsvSaleTaxLedger');
            Route::get('ewallet/csv-ledger', 'EwalletController@downloadCsvBalanceLedger');
            Route::post('ewallet/withdraw', 'EwalletController@postWithdraw');

            Route::get('ewallet/pay-taxes/credit-card', 'EwalletTaxesController@payByCreditCard');
            Route::get('ewallet/pay-taxes/echeck', 'EwalletTaxesController@payByEcheck');
            Route::get('ewallet/pay-taxes/ewallet', 'EwalletTaxesController@payByEwallet');

            // media
            Route::get('media', 'MediaController@index');
            Route::post('media/user-product-images', 'MediaController@userProductImages');
            Route::post('media/corporate-product-images', 'MediaController@corporateProductImages');
            Route::post('media/create', 'MediaController@create');
            Route::patch('media/{id}', 'MediaController@update');
            Route::post('media/process', 'MediaController@process');
            Route::post('media/create-user-image', 'MediaController@createUserImage');
            Route::delete('media/{id}', 'MediaController@delete');
            Route::get('media/filter', 'MediaController@indexFilter');
            Route::get('media/count', 'MediaController@count');

            // payquicker
            Route::get('payquicker/invite', 'PayQuickerController@invite');

            // store settings
            Route::post('store-settings/category-header', 'StoreSettingsController@updateCategoryHeader');
            Route::post('store-settings/update', 'StoreSettingsController@update');
            Route::get('store-settings', 'StoreSettingsController@getStore');
            Route::get('store-settings/user/{id}', 'StoreSettingsController@getUserStoreSettings');

            // subscriptions
            Route::get('subscriptions/all-subscriptions', 'SubscriptionController@allSubscriptions');
            Route::get('subscriptions', 'SubscriptionController@index');
            Route::get('subscriptions/show', 'SubscriptionController@show');
            Route::post('subscriptions/renew-subscription', 'SubscriptionController@renewSubscription');
            Route::get('subscriptions/renew-amount/{id}', 'SubscriptionController@renewSubscriptionAmount');
            Route::put('subscriptions/token-update', 'SubscriptionController@tokenUpdate');
            Route::get('subscriptions/receipt/{id}', 'SubscriptionController@userReceipt');

            Route::get('orders/by-date', 'OrderController@showOrderTotalsByDate');
            Route::get('orders/by-rep/{id?}', 'OrderController@ordersByRep');
            Route::post('orders/transfer-inventory/{receiptId}', 'OrderController@transferInventory');

            Route::post('orders/update-status', 'OrderController@updateStatus');
            Route::post('invoice/update-invoice-status', 'InvoiceController@updateInvoiceStatus');

            Route::post('order-status', 'OrderStatusController@create');
            Route::patch('order-status/{id}', 'OrderStatusController@update');
            Route::delete('order-status/{id}', 'OrderStatusController@delete');

            Route::get('products/search', 'ProductController@search');

            Route::post('price/update-premium', 'PriceController@updatePremium');

            //returns
            Route::post('returns/request', 'ReturnController@postRequest');
            Route::patch('returns/update/{id}', 'ReturnController@patchUpdate');
            Route::get('return-statuses/all', 'ReturnStatusController@getAll');
            Route::get('returns/return-orders/{order_id}', 'ReturnController@orderReturned');
            Route::get('returns/reason', 'ReturnController@reasons');
            Route::get('returns/quantity', 'ReturnController@returnQuantity');
            Route::get('returns/show/{id}', 'ReturnController@showReturnedById');
            Route::post('returns/updateStatus', 'ReturnController@updateStatus');
            Route::post('return/refundRequest', 'ReturnController@submitRefund');
            Route::get('returns', 'ReturnController@index');

            // return history
            Route::get('returnhistory/{orderId}', 'ReturnHistoryController@show');

            //registration
            Route::post('payment-account', 'RegistrationController@createPaymentAccount');
            Route::get('payment-account/user/{id}', 'RegistrationController@checkUserAccount');

            // invoice routes
            Route::get('invoice', 'InvoiceController@getAllInvoices');
            Route::get('invoices-pdf', 'InvoiceController@getAllInvoicesForPdf');
            Route::get('invoice-pdf/{uid}', 'InvoiceController@getPdfInvoice');

            // inventory routes
            Route::get('inventory', 'InventoryController@index');
            Route::get('inventory/rep', 'InventoryController@rep');
            Route::get('inventory/by-product', 'InventoryController@getByProduct');
            Route::post('inventory/save-price', 'InventoryController@savePrice');
            Route::get('inventory/toggle/{id}', 'InventoryController@toggleDisable');

            // products
            Route::get('products/auth-store', 'ProductController@authStore');
            Route::get('products/wholesale-store', 'ProductController@wholesaleStore');
            Route::get('products/wholesale/{id}', 'ProductController@showWholesale');
            Route::get('products/wholesale/product-items/{id}', 'ItemController@getWholesaleItemsByProduct');

            // Shipping Rates
            Route::get('shipping-rate', 'ShippingRateController@index');
            Route::get('shipping-rate-wholesale', 'ShippingRateController@indexWholesale');
            Route::get('shipping-rate/shipping-cost-by-auth', 'ShippingRateController@shippingCostByAuth');
            Route::post('shipping-rate/create', 'ShippingRateController@create');

            // shipping routes
            Route::get('shipping/carriers', 'ShippingController@getCarriers');
            Route::post('shipping/rates', 'ShippingController@rates');
            Route::post('shipping', 'ShippingController@createShipping');

            // user routes
            Route::get('user/names', 'UserController@names');
            Route::get('user/search', 'UserController@search');
            Route::get('user/search-reps', 'UserController@searchReps');
            Route::get('user/show/{id}', 'UserController@show');
            Route::post('user/validate', 'UserController@validate');
            Route::get('user/company/', 'UserController@getCompanyInfo');
            Route::post('user/createUpdateCompany', 'UserController@createCompanyInfo');
            Route::get('user/acceptTerms', 'UserController@acceptTermsAndConditions');
            Route::get('user/isAffiliate', 'UserController@isAffiliate');

            // user status
            Route::post('user-status', 'UserStatusController@create');
            Route::patch('user-status/{id}', 'UserStatusController@update');
            Route::delete('user-status/{id}', 'UserStatusController@delete');

            Route::get('my-account/{id}', 'UserController@myAccount');
            Route::get('user/auth', 'UserController@auth');
            Route::get('user/auth-id', 'UserController@authId');

            // user settings routes
            Route::get('user-settings/{user_id}', 'UserSettingsController@show');
            Route::get('user-settings/card/show', 'UserSettingsController@cardInfoShow');
            Route::delete('user-settings/card-delete/{id}', 'UserSettingsController@deleteCardInfo');
            Route::put('user-settings/update', 'UserSettingsController@update');
            Route::post('user-settings/card/card-token', 'UserSettingsController@createToken');
            Route::get('user-settings/terms/{user_id}', 'UserSettingsController@mySettings');

            // dashboard
            Route::get('dashboard', 'DashboardController@index');
        });
    });
});

// TODO are these being used? Can they be removed?
Route::group([
    'prefix' => '/api/v2',
    'namespace' => 'Api\V2\MCommServices',
], function () {
    Route::get('mcomm/addOrder/{order?}/{initialize?}', 'MCommCommissionService@addReceipt');
    Route::get('mcomm/commitNewOrders', 'MCommCommissionService@commitNewOrders');
});
Route::group([
    'prefix'     => '/api/v2',
    'namespace'  => 'Api\V2',
], function () {
    Route::get('mcomm/currentPeriod/{returnLocally?}', 'MCommController@getCurrentPeriod');
    Route::get('mcomm/activePeriods/{userId?}', 'MCommController@getActivePeriods');
    Route::get('mcomm/kpi/{userId?}/{period?}', 'MCommController@getMemberPeriodKPIdata');
    Route::get('mcomm/user/{userId?}', 'MCommController@addUser');
    Route::get('mcomm/period', 'MCommController@getCurrentPeriod');
    Route::get('mcomm/genealogy/{userId?}', 'MCommController@getGenealogyReport');
    Route::get('mcomm/members/{userId?}', 'MCommController@getMemberData');
    
    Route::group([
        'middleware' => 'api.cpauth'
    ], function () {

        // notifications
        Route::post('notification-event/order-fulfilled', 'NotificationEventController@orderFulfilledNotification');
        Route::post('notification-event/coupon-created', 'NotificationEventController@couponCreatedNotification');
        Route::post('notification-event/autoship-sub-created', 'NotificationEventController@autoshipSubCreatedNotification');
        Route::post('notification-event/autoship-sub-cancelled', 'NotificationEventController@autoshipSubCancelNotification');
        Route::post('notification-event/user-created', 'NotificationEventController@newUserNotification');
    });

    Route::group([
        'middleware' => 'auth'
    ], function () {
        // oauth
        Route::get('oauth/associate/facebook', 'OauthController@connect');
        Route::get('oauth/disconnect/{driver}', 'OauthController@disconnect');

        // live video
        Route::get('live-video-products', 'LiveVideoController@personalProducts');
        Route::post('live-video/create-product', 'LiveVideoController@createProduct');
        Route::get('live-videos/personal-product/{id}', 'LiveVideoController@showPersonalProduct');
        Route::get('live-videos/personal-product/delete/{id}', 'LiveVideoController@removePersonalProduct');
        Route::get('live-videos/AllVideoInventory', 'LiveVideoController@getAllVideoInvetoryByUser');
        Route::get('live-videos/{service}', 'LiveVideoController@index');
        Route::post('live-videos/{service}', 'LiveVideoController@store');
        Route::post('live-videos/{service}/inventory', 'LiveVideoController@commitInventory');
        Route::get('live-videos/{service}/video/{id}', 'LiveVideoController@show');
        Route::post('live-videos/{service}/video/{id}', 'LiveVideoController@update');
        Route::get('live-videos/{service}/check', 'LiveVideoController@checkVideoFeed');
        Route::get('live-videos/{service}/delete', 'LiveVideoController@delete');
        Route::get('live-videos/{service}/delete/{id}', 'LiveVideoController@delete');
        Route::get('live-videos/{service}/end-session', 'LiveVideoController@endLiveSession');
    });
});
