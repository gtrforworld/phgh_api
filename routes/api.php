<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiCtrl;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/wallet', [ApiCtrl::class, 'getWalletInfo']);
Route::get('/top-referal', [ApiCtrl::class, 'getTopReferal']);
Route::post('/wallet', [ApiCtrl::class, 'storeWallet']);

// Route::post('/wallet', [ApiCtrl::class, 'storeWallet']);
Route::post('/wallet', [ApiCtrl::class, 'updateWallet']);
Route::post('/newph', [ApiCtrl::class, 'storePH']);
Route::post('/getph', [ApiCtrl::class, 'getPH']);
Route::post('/bonus-ref', [ApiCtrl::class, 'bonusRef']);
Route::post('/bonus-man', [ApiCtrl::class, 'bonusMan']);
Route::get('/bonus-referal', [ApiCtrl::class, 'getBonusTransaction']);
Route::get('/bonus-manager', [ApiCtrl::class, 'getManagerTransaction']);
Route::post('/register-airdrop', [ApiCtrl::class, 'registerAirdrop']);
Route::get('/register-airdrop', [ApiCtrl::class, 'registerAirdropByAddress']);



