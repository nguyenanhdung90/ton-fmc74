<?php

use App\Http\Controllers\TonController;
use Illuminate\Support\Facades\Route;

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

Route::get('/valid', [TonController::class, 'valid']);
Route::get('/withdrawJettonExample', [TonController::class, 'withdrawJettonExample']);
Route::get('/withdrawTONExample', [TonController::class, 'withdrawTONExample']);

