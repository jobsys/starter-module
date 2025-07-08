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
use Modules\Starter\Http\Controllers\UserCgiController;


Route::prefix("manager/starter")->name("page.manager.starter.")->group(function () {
	Route::get('/dict', 'DictController@pageDict')->name('dict');
	Route::get('/dict/item', 'DictController@pageDictItem')->name('dict.item');
	Route::get('/log', 'LogController@pageLog')->name('log');
});

Route::prefix("manager/export")->name("export.manager.starter.")->group(function () {
	Route::get('/dict', 'DictController@export')->name('dict');
});


Route::get('/login', [UserCgiController::class, 'pageLogin'])->name('page.login');
Route::get('/logout', [UserCgiController::class, 'logout'])->name('page.logout');
