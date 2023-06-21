<?php

use Illuminate\Support\Facades\Route;
use Modules\Starter\Http\Controllers\UserCgiController;

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

$route_prefix = config('module.Starter.route_prefix', 'manager');
$route_url_prefix = $route_prefix ? $route_prefix . '/' : '';
$route_name_prefix = $route_prefix ? $route_prefix . '.' : '';

Route::prefix("{$route_url_prefix}starter")->name("api.{$route_name_prefix}starter.")->group(function () {
    Route::post('/dict', "DictController@edit")->name('dict.edit');
    Route::get('/dict/{id}', 'DictController@item')->where('id', '[0-9]+')->name('dict.item');
    Route::get('/dict', 'DictController@items')->name('dict.items');
    Route::post('/dict/delete', 'DictController@delete')->name('dict.delete');


    Route::get('/dict/item', 'DictController@itemItems')->name('dict.item.items');
    Route::post('/dict/item', 'DictController@itemEdit')->name('dict.item.edit');
    Route::post('/dict/item/delete', 'DictController@itemDelete')->name('dict.item.delete');

    Route::get('/notification', 'NotificationController@items')->name('notification.items');
    Route::post('/notification/read/{id}', 'NotificationController@read')->name('notification.read');
    Route::post('/notification/read-all', 'NotificationController@readAll')->name('notification.read-all');
    Route::post('/notification/delete/{id}', 'NotificationController@delete')->name('notification.delete');
    Route::post('/notification/delete-all', 'NotificationController@deleteAll')->name('notification.delete-all');
    Route::get('/notification/brief', 'NotificationController@brief')->name('notification.brief');
    //->middleware(['throttle:ip_address,12'])
});

Route::post('/login', [UserCgiController::class, 'login'])->name('api.login');
