<?php
use App\Http\Controllers\TestController;
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

Route::get('test',[TestController::class,'test']);
Route::get('test2',[TestController::class,'test2']);
Route::get('test3Mol',[TestController::class,'test3Mol']);
Route::get('test4Mol',[TestController::class,'test4Mol']);