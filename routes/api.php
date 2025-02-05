<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\FileController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



//heroku
Route::get('/latest-filename', [ProxyController::class, 'getLatestFileName']);
Route::get('/apk-latest-filename', [ProxyController::class, 'getApkLatestFileName']);
Route::get('/apk-latest-filename-r2', [ProxyController::class, 'getApkLatestFileNameByR2']);
Route::get('/apk-64-latest-filename-r2', [ProxyController::class, 'getApk64LatestFileNameByR2']);
Route::get('/mpro-latest-filename-r2', [ProxyController::class, 'getMproLatestFileNameByR2']);
Route::delete('/cache/apk-latest-file-name-r2', [ProxyController::class, 'clearApkLatestFileNameR2Cache']);
Route::delete('/cache/apk-64-latest-file-name-r2', [ProxyController::class, 'clearApk64LatestFileNameR2Cache']);
Route::delete('/cache/mpro-latest-file-name-r2', [ProxyController::class, 'clearMproLatestFileNameR2Cache']);


//very6 有用到
//use App\Http\Controllers\FileController;
//
//Route::post('/upload', [FileController::class, 'upload']);
//Route::get('/download-latest', [FileController::class, 'downloadLatest']);
//Route::get('/files', [FileController::class, 'listFiles']);
//Route::get('/latest-filename', [FileController::class, 'getLatestFileName']);
//Route::post('/apk-upload', [FileController::class, 'apkUpload']);
//Route::get('/apk-download-latest', [FileController::class, 'apkDownloadLatest']);
//Route::get('/apk-files', [FileController::class, 'apkListFiles']);
//Route::get('/apk-latest-filename', [FileController::class, 'getApkLatestFileName']);

//Route::post('/apk-upload-r2', [FileController::class, 'apkUploadByR2']);
//Route::get('/apk-download-latest-r2', [FileController::class, 'apkDownloadLatestByR2']);
