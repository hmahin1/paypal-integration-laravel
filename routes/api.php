<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
 * Welcome route - link to any public API documentation here
 */

 
Route::get('/', 'PayPalController@index');

/* Route::get('/', function () {
    echo 'Welcome to our API abcas aaaaaaaaa';
});
 */
Route::post('sign-up','UserController@signUp');
Route::post('login','UserController@logIn');

Route::get('products', 'ProductController@show');
Route::get('products/{id}', 'ProductController@getById');
Route::get('news', 'NewsController@show');

//Route::post('paypal', 'PayPalController@payWithpaypal');
Route::get('status', 'PayPalController@getPaymentStatus');
    

Route::middleware(['auth'])->group(function (){
    
    Route::post('logout','UserTokenController@logOut');
    
    Route::get('user','UserController@getUser');
    //skrill routes
/*     Route::get('make-payment', 'SkrillPaymentController@makePayment');
    Route::get('do-refund', 'SkrillPaymentController@doRefund');
    Route::post('ipn', 'SkrillPaymentController@ipn');
    Route::get('payment-completed', function () {
        return view('payment-completed');
    });
    Route::get('payment-cancelled', function () {
        return view('payment-cancelled');
    });
 */    Route::post('place-order','OrderController@placeOrder');
    Route::get('order-list','OrderController@orderList');

    /* Route::get('payment', 'PayPalController@payment')->name('payment');
    Route::get('cancel', 'PayPalController@cancel')->name('payment.cancel');
    Route::get('payment/success', 'PayPalController@success')->name('payment.success');
     */ 
    Route::post('paypal', 'PayPalController@payWithpaypal'); 

    Route::middleware(['admin'])->group(function (){
        Route::post('products/{id?}', 'ProductController@products');
        Route::post('news/{id?}', 'NewsController@news');
        Route::get('users','UserController@userList');
        });

});





