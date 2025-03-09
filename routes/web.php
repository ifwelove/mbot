<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\CommandController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

Route::get('/log-ip', function () {
    $ip_address = request()->ip();  // 獲取 IP 地址
    $key = 'ip_log';  // Redis 中的鍵
    Redis::rpush($key, $ip_address);  // 將 IP 地址存入 Redis 的列表中
    return response()->json(['message' => 'IP Address logged', 'ip' => $ip_address]);
});
Route::get('/view-ips', function () {
    $key = 'ip_log';  // Redis 中的鍵
    $ips = Redis::lrange($key, 0, -1);  // 從 Redis 中獲取所有 IP 地址
    return response()->json(['ips' => $ips]);
});

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
use App\Http\Controllers\TelegramController;

Route::post('/telegram/webhook', [TelegramController::class, 'webhookHandler']);
Route::get('/dump-chat-ids', [TelegramController::class, 'dumpAllChatIds']);
Route::get('/clear-chat-ids', [TelegramController::class, 'clearAllChatIds']);

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
Route::post('/apk/check/token', [AlertController::class, 'apkCheckToken']);
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

Route::get('/dump', function (Request $request) {
    $keys = Redis::keys("api_calls:*"); // 獲取所有統計鍵
    foreach ($keys as $key) {
        dump($key);
        dump(Redis::get(str_replace('laravel_database_', '', $key))); // 獲取次數
    }
});


// 清除特定主機的 Redis 統計數據
Route::delete('/clear', function (Request $request) {
    $host = $request->getHost(); // 獲取當前主機名稱
    $keys = Redis::keys("api_calls:{$host}:*"); // 獲取該主機的所有鍵

    // 刪除所有相關鍵
    foreach ($keys as $key) {
        Redis::del($key);
    }

    return response()->json([
        'status' => 'success',
        'message' => "All statistics for host {$host} have been cleared.",
        'cleared_keys' => count($keys),
    ]);
});

Route::get('/track-and-dump', function (Request $request) {
//    $host = $request->getHost(); // 獲取主機名稱
    $currentMinute = now()->format('YmdH'); // 格式化為當前分鐘
    $redisKey = "api_calls:{$currentMinute}"; // 鍵名

    // 新增數據，設置 TTL 為 1 天（86400 秒）
    Redis::incr($redisKey);
    Redis::expire($redisKey, 86400/24);
    dump($redisKey);
    dd(Redis::get($redisKey));
    // 獲取所有鍵
    $keys = Redis::keys("api_calls:*"); // 查詢當前主機的所有統計鍵
    $data = [];

    foreach ($keys as $key) {
        $minute = str_replace("api_calls:", '', $key); // 提取時間部分
        $data[$minute] = Redis::get($key); // 獲取次數
    }

    return response()->json([
        'status' => 'success',
        'data' => $data,
    ]);
});


Route::post('/store-url', function (\Illuminate\Http\Request $request) {
    // 驗證並接收 URL 參數
    $url = $request->input('url');

    if (!$url) {
        return response()->json(['message' => 'URL is required.'], 400);
    }

    // 存到 Redis，使用 'latest_url' 作為 key
    Redis::set('netflex_latest_url', $url);

    return response()->json(['message' => 'URL stored successfully.']);
});

Route::get('/get-url', function () {
    // 從 Redis 取得網址
    $url = Redis::get('netflex_latest_url');

    if (!$url) {
        return response()->json(['message' => 'No URL found.']);
    }

    // 返回可點擊的 a 標籤連結
    return response()->make(
        base64_decode($url),
        200
    )->header('Content-Type', 'text/html');
//    return response()->make(
//        "<html>
//            <head><title>Stored URL</title></head>
//            <body>
//                <p>認證連結：</p>
//                <a href=\"{$url}\" target=\"_blank\">{$url}</a>
//            </body>
//        </html>",
//        200
//    )->header('Content-Type', 'text/html');
});


Route::get('/clear-url', function () {
    // 清除 Redis 中的網址 (key: latest_netflix_url)
    Redis::del('netflex_latest_url');

    return response()->json(['message' => 'URL has been cleared.']);
});


use App\Http\Controllers\QuotationController;

Route::get('/quotation', [QuotationController::class, 'create']);
Route::post('/quotation', [QuotationController::class, 'store']);

Route::get('/pdf', [QuotationController::class, 'create2']);
Route::post('/pdf', [QuotationController::class, 'store2']);
