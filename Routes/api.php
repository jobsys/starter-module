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

Route::prefix("manager/starter")->name("api.manager.starter.")->group(function () {

	Route::get('/system/settings', "SystemController@settings")->name('system.settings');

	Route::get('/log', 'LogController@items')->name('log.items');

	Route::post('/dict', "DictController@edit")->name('dict.edit');
	Route::get('/dict/{id}', 'DictController@item')->where('id', '[0-9]+')->name('dict.item');
	Route::get('/dict', 'DictController@items')->name('dict.items');
	Route::post('/dict/delete', 'DictController@delete')->name('dict.delete');
	Route::get('/dict/item', 'DictController@itemItems')->name('dict.item.items');
	Route::post('/dict/item', 'DictController@itemEdit')->name('dict.item.edit');
	Route::post('/dict/item/delete', 'DictController@itemDelete')->name('dict.item.delete');

	Route::post('/configuration', 'ConfigurationController@edit')->name('configuration.edit');
	Route::get('/configuration', 'ConfigurationController@items')->name('configuration.items');

	Route::post('/category', "CategoryController@edit")->name('category.edit');
	Route::get('/category/{id}', 'CategoryController@item')->where('id', '[0-9]+')->name('category.item');
	Route::get('/category', 'CategoryController@items')->name('category.items');
	Route::get('/category/homology', 'CategoryController@homologyItems')->name('category.homology');
	Route::post('/category/delete', 'CategoryController@delete')->name('category.delete');

	Route::get('/notification', 'NotificationController@items')->name('notification.items');
	Route::post('/notification/read/{id}', 'NotificationController@read')->name('notification.read');
	Route::post('/notification/read-all', 'NotificationController@readAll')->name('notification.read-all');
	Route::post('/notification/delete/{id}', 'NotificationController@delete')->name('notification.delete');
	Route::post('/notification/delete-all', 'NotificationController@deleteAll')->name('notification.delete-all');
	Route::get('/notification/brief', 'NotificationController@brief')->name('notification.brief');
});

Route::post('/login', [UserCgiController::class, 'login'])->name('api.login');
