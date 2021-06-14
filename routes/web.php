<?php

use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;

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
Route::get('/', function () {
    return view('welcome');
});
Route::get('/password/reset/{token}', [App\Http\Controllers\auth\ResetPasswordController::class,"index"])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\auth\ResetPasswordController::class,"reset_password"])->name('password.update');