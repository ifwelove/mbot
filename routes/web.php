<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\CommandController;
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
use App\Http\Controllers\IconController;

Route::get('/icons', [IconController::class, 'index'])->name('icons.index');


Route::post('/callback', [LineController::class, 'webhook']);
Route::get('/ping', [LineController::class, 'ping']);

Route::post('/alert', [AlertController::class, 'alert']);
Route::post('/alert2', [AlertController::class, 'alert2']);
//Route::get('/alert', [AlertController::class, 'alert']);

Route::get('/machines/{token}', [AlertController::class, 'showMachines']);
Route::get('/demo/{token}', [AlertController::class, 'showDemo']);
Route::get('/test/{token}', [AlertController::class, 'showTest']);
Route::get('/bill/{token}', [AlertController::class, 'showBill']);
Route::get('/pro/{token}', [AlertController::class, 'showDemo']);
Route::get('/show/{token}', [AlertController::class, 'showToken']);
Route::post('/check/token', [AlertController::class, 'checkToken']);
Route::post('/olin/check/token', [AlertController::class, 'checkOlinToken']);
Route::post('/olin/tap', [AlertController::class, 'execOlinTap']);
Route::post('/delete-machine', [AlertController::class, 'deleteMachine']);
Route::get('/delete-machine', [AlertController::class, 'deleteMachineFromLine']);
Route::get('/monitor', [AlertController::class, 'monitor']);
Route::get('/monitor2', [AlertController::class, 'monitor2']);
Route::get('/test', [\App\Http\Controllers\TestController::class, 'ping']);
Route::post('/heroku', [AlertController::class, 'heroku']);

Route::get('/share', [AlertController::class, 'shareToken']);
Route::post('/share/apply', [AlertController::class, 'shareApply']);

Route::match(['get', 'post'],'/notify', [\App\Http\Controllers\LineNotifyController::class, 'index']);
Route::match(['get'],'/apply', [\App\Http\Controllers\LineNotifyController::class, 'apply']);

Route::get('/send/message/{token}', [AlertController::class, 'sendMessage']);


// 存储命令
Route::post('/store-command', [CommandController::class, 'storeCommand']);
Route::post('/store-all-mac-command', [CommandController::class, 'storeAllMacCommand']);
// 获取并清除命令
Route::post('/get-clear-command', [CommandController::class, 'getAndClearCommand']);
//Route::get('/get-clear-command', [CommandController::class, 'getAndClearCommand']);
