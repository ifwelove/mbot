<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class IconController extends Controller
{
//    protected $latestFilename = 'https://very6.tw/api/tools'; // 文件服务器的 API URL
//
//    public function getTools(Request $request)
//    {
//        // 从 Redis 缓存中获取最新文件名，如果不存在或已过期则执行回调
//        $tools = Cache::remember('tools', 60, function () {
//            // 向文件服务器发送请求获取最新文件名
//            $response = Http::get($this->latestFilename);
//
//            // 确认响应成功并获取文件名，否则返回默认值
//            if ($response->successful() && $response->json('tools')) {
//                return $response->json('tools');
//            } else {
//                // 根据需要处理错误或返回默认值
//                return 'No file found';
//            }
//        });
//
//        return response($tools, 200);
//    }

    public function index()
    {
        $imageCounts = [7, 35, 28, 27, 41];

        return view('icons.index', compact('imageCounts'));
    }
}
