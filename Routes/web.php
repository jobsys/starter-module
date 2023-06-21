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

use Modules\Starter\Http\Controllers\UserCgiController;

$route_prefix = config('module.Starter.route_prefix', 'manager');
$route_url_prefix = $route_prefix ? $route_prefix . '/' : '';
$route_name_prefix = $route_prefix ? $route_prefix . '.' : '';

Route::prefix("{$route_url_prefix}starter")->name("page.{$route_name_prefix}starter.")->group(function () {
    Route::get('/dict', 'DictController@pageDict')->name('dict');
});


Route::prefix("{$route_url_prefix}export")->name("export.{$route_name_prefix}starter")->group(function () {
    Route::get('/dict', 'DictController@export')->name('dict');
});


Route::get('/login', [UserCgiController::class, 'pageLogin'])->name('page.login');
Route::get('/redirect', [UserCgiController::class, 'redirect'])->name('page.redirect');
Route::get('/logout', [UserCgiController::class, 'logout'])->name('page.logout');
