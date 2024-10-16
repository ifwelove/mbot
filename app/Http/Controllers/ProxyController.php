<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    protected $fileUrl = 'https://very6.tw/api/download-latest'; // 文件服务器的 API URL
    protected $latestFilename = 'https://very6.tw/api/latest-filename'; // 文件服务器的 API URL

    protected $apkFileUrl = 'https://very6.tw/api/apk-download-latest'; // 文件服务器的 API URL
    protected $apkLatestFilename = 'https://very6.tw/api/apk-latest-filename'; // 文件服务器的 API URL

    public function getLatestFileName(Request $request)
    {
        // 从 Redis 缓存中获取最新文件名，如果不存在或已过期则执行回调
        $latestFileName = Cache::remember('latest_file_name', 10, function () {
            // 向文件服务器发送请求获取最新文件名
            $response = Http::get($this->latestFilename);

            // 确认响应成功并获取文件名，否则返回默认值
            if ($response->successful() && $response->json('latestFileName')) {
                return $response->json('latestFileName');
            } else {
                // 根据需要处理错误或返回默认值
                return 'No file found';
            }
        });
//        $latestFileName = '大尾3-9.7.9-v1.7.19.rar';
//        $latestFileName = '大尾3-2.0.0.2-v1.7.25.rar';
        return response()->json(['latestFileName' => $latestFileName]);
//        return response($latestFileName, 200)->header('Content-Type', 'text/plain');
    }

    public function getApkLatestFileName(Request $request)
    {
        // 从 Redis 缓存中获取最新文件名，如果不存在或已过期则执行回调
        $latestFileName = Cache::remember('apk_latest_file_name', 300, function () {
            // 向文件服务器发送请求获取最新文件名
            $response = Http::get($this->apkLatestFilename);

            // 确认响应成功并获取文件名，否则返回默认值
            if ($response->successful() && $response->json('apkLatestFileName')) {
                return $response->json('apkLatestFileName');
            } else {
                // 根据需要处理错误或返回默认值
                return 'No file found';
            }
        });
        //        $latestFileName = '大尾3-9.7.9-v1.7.19.rar';
        //        return response()->json(['latestFileName' => $latestFileName]);
        return response($latestFileName, 200)->header('Content-Type', 'text/plain');
    }
}
