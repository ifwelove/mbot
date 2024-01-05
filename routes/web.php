<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineController;
use App\Http\Controllers\AlertController;
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

Route::post('/callback', [LineController::class, 'webhook']);
Route::get('/ping', [LineController::class, 'ping']);

Route::post('/alert', [AlertController::class, 'alert']);
//Route::get('/alert', [AlertController::class, 'alert']);

Route::get('/machines/{token}', [AlertController::class, 'showMachines']);
Route::get('/machines2/{token}', [AlertController::class, 'showMachines2']);
Route::post('/delete-machine', [AlertController::class, 'deleteMachine']);
Route::get('/monitor', [AlertController::class, 'monitor']);
Route::post('/heroku', [AlertController::class, 'heroku']);
