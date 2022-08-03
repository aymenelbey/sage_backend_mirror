<?php

use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use App\Rules\Siren;
use App\Rules\Siret;
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
Route::get('/test', [App\Http\Controllers\TestController::class,"export"]);
Route::get('/', function () {
    return view('welcome');
});
Route::get("imports/download/{filename}",[App\Http\Controllers\ImportData::class,"download_excel"]);
Route::get('/password/reset/{token}', [App\Http\Controllers\auth\ResetPasswordController::class,"index"])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\auth\ResetPasswordController::class,"reset_password"])->name('password.update');