<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;


Route::prefix("manager/starter")->name("page.manager.starter.")->group(function () {
	Route::get('/dict', [\Modules\Starter\Http\Controllers\DictController::class, 'pageDict'])->name('dict');
	Route::get('/dict/item', [\Modules\Starter\Http\Controllers\DictController::class, 'pageDictItem'])->name('dict.item');
	Route::get('/log', [\Modules\Starter\Http\Controllers\LogController::class, 'pageLog'])->name('log');
	Route::get('/message', [\Modules\Starter\Http\Controllers\MessageController::class, 'pageMessage'])->name('message');
	Route::get('/message/{id}', [\Modules\Starter\Http\Controllers\MessageController::class, 'pageMessageDetail'])->name('message.detail');
});

Route::prefix("manager/export")->name("export.manager.starter.")->group(function () {
	Route::get('/dict', [\Modules\Starter\Http\Controllers\DictController::class, 'export'])->name('dict');
});


Route::get('/login', [Modules\Starter\Http\Controllers\UserCgiController::class, 'pageLogin'])->name('page.login');
Route::get('/logout', [Modules\Starter\Http\Controllers\UserCgiController::class, 'logout'])->name('page.logout');
