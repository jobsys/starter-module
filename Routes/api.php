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

	Route::get('/system/settings', [\Modules\Starter\Http\Controllers\SystemController::class, 'settings'])->name('system.settings');

	Route::get('/log', [\Modules\Starter\Http\Controllers\LogController::class, 'items'])->name('log.items');

	Route::post('/dict', [\Modules\Starter\Http\Controllers\DictController::class, 'edit'])->name('dict.edit');
	Route::get('/dict/{id}', [\Modules\Starter\Http\Controllers\DictController::class, 'item'])->where('id', '[0-9]+')->name('dict.item');
	Route::get('/dict', [\Modules\Starter\Http\Controllers\DictController::class, 'items'])->name('dict.items');
	Route::post('/dict/delete', [\Modules\Starter\Http\Controllers\DictController::class, 'delete'])->name('dict.delete');
	Route::get('/dict/item', [\Modules\Starter\Http\Controllers\DictController::class, 'itemItems'])->name('dict.item.items');
	Route::post('/dict/item', [\Modules\Starter\Http\Controllers\DictController::class, 'itemEdit'])->name('dict.item.edit');
	Route::post('/dict/item/delete', [\Modules\Starter\Http\Controllers\DictController::class, 'itemDelete'])->name('dict.item.delete');

	Route::post('/configuration', [\Modules\Starter\Http\Controllers\ConfigurationController::class, 'edit'])->name('configuration.edit');
	Route::get('/configuration', [\Modules\Starter\Http\Controllers\ConfigurationController::class, 'items'])->name('configuration.items');

	Route::post('/category', [\Modules\Starter\Http\Controllers\CategoryController::class, 'edit'])->name('category.edit');
	Route::get('/category/{id}', [\Modules\Starter\Http\Controllers\CategoryController::class, 'item'])->where('id', '[0-9]+')->name('category.item');
	Route::get('/category', [\Modules\Starter\Http\Controllers\CategoryController::class, 'items'])->name('category.items');
	Route::get('/category/homology', [\Modules\Starter\Http\Controllers\CategoryController::class, 'homologyItems'])->name('category.homology');
	Route::post('/category/delete', [\Modules\Starter\Http\Controllers\CategoryController::class, 'delete'])->name('category.delete');

	Route::get('/message-batch', [\Modules\Starter\Http\Controllers\MessageController::class, 'batchItems'])->name('message-batch.items');
	Route::get('/message-batch/{id}', [\Modules\Starter\Http\Controllers\MessageController::class, 'batchItem'])->where('id', '[0-9]+')->name('message-batch.item');
	Route::post('/message-batch', [\Modules\Starter\Http\Controllers\MessageController::class, 'batchEdit'])->name('message-batch.edit');
	Route::post('/message-batch/delete', [\Modules\Starter\Http\Controllers\MessageController::class, 'batchDelete'])->name('message-batch.delete');
	Route::post('/message-batch/cancel', [\Modules\Starter\Http\Controllers\MessageController::class, 'batchCancel'])->name('message-batch.cancel');
	Route::get('/message/config', [\Modules\Starter\Http\Controllers\MessageController::class, 'messageConfig'])->name('message.config');
	Route::post('/message', [\Modules\Starter\Http\Controllers\MessageController::class, 'messageItems'])->name('message.items');

});

Route::post('/login', [UserCgiController::class, 'login'])->name('api.login');
