<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProxyController extends Controller
{
    protected $fileUrl        = 'https://very6.tw/api/download-latest'; // 文件服务器的 API URL
    protected $latestFilename = 'https://very6.tw/api/latest-filename'; // 文件服务器的 API URL

    protected $apkFileUrl        = 'https://very6.tw/api/apk-download-latest'; // 文件服务器的 API URL
    protected $apkLatestFilename = 'https://very6.tw/api/apk-latest-filename'; // 文件服务器的 API URL

    //改 r2 直接給連結？ 還是 py 那邊在組 r2 總控在 php比較好更新, 改app客戶要更新
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

    public function getApkLatestFileNameByR2(Request $request)
    {
        $data = Cache::remember('apk_latest_file_name_r2', 300 * 6, function () {
            $files = collect(Storage::disk('r2')
                ->files('/'))
                ->filter(function ($file) {
                    return preg_match('/\.xapk$/', $file); // 只匹配 .xapk 文件
                })
                ->mapWithKeys(function ($file) {
                    return [
                        $file => Storage::disk('r2')
                            ->lastModified($file)
                    ]; // 使用文件的最后修改时间
                })
                ->sortByDesc(function ($timestamp, $file) {
                    return $timestamp; // 按最后修改时间排序
                })
                ->keys();

            if ($files->isEmpty()) {
                return null; // 如果没有文件，返回 null
            }

            $latestFile = $files->first();

            // 生成下载 URL
            $temporaryUrl = Storage::disk('r2')
                ->temporaryUrl($latestFile, now()->addMinutes(60), // 设置 URL 的有效期
                    [
                        'ResponseContentType'        => 'application/vnd.android.package-archive',
                        'ResponseContentDisposition' => 'attachment; filename="' . basename($latestFile) . '"',
                    ]);

            return [
                'file_name' => basename($latestFile),
                'url'       => $temporaryUrl,
            ];
        });

        // 如果没有找到文件
        if (is_null($data)) {
            return 'No file found';
            //                return response()->json(['message' => 'No files found'], 404);
        }

        // 返回结果
        return response()->json([
            'fileName' => $data['file_name'],
            'url'      => $data['url'],
        ]);
    }

    public function getApk64LatestFileNameByR2(Request $request)
    {
        $data = Cache::remember('apk_64_latest_file_name_r2', 300 * 6, function () {
            $files = collect(Storage::disk('64r2')
                ->files('/'))
                ->filter(function ($file) {
                    return preg_match('/\.xapk$/', $file); // 只匹配 .xapk 文件
                })
                ->mapWithKeys(function ($file) {
                    return [
                        $file => Storage::disk('64r2')
                            ->lastModified($file)
                    ]; // 使用文件的最后修改时间
                })
                ->sortByDesc(function ($timestamp, $file) {
                    return $timestamp; // 按最后修改时间排序
                })
                ->keys();

            if ($files->isEmpty()) {
                return null; // 如果没有文件，返回 null
            }

            $latestFile = $files->first();

            // 生成下载 URL
            $temporaryUrl = Storage::disk('64r2')
                ->temporaryUrl($latestFile, now()->addMinutes(60), // 设置 URL 的有效期
                    [
                        'ResponseContentType'        => 'application/vnd.android.package-archive',
                        'ResponseContentDisposition' => 'attachment; filename="' . basename($latestFile) . '"',
                    ]);

            return [
                'file_name' => basename($latestFile),
                'url'       => $temporaryUrl,
            ];
        });

        // 如果没有找到文件
        if (is_null($data)) {
            return 'No file found';
            //                return response()->json(['message' => 'No files found'], 404);
        }

        // 返回结果
        return response()->json([
            'fileName' => $data['file_name'],
            'url'      => $data['url'],
        ]);
    }


    public function clearApkLatestFileNameR2Cache()
    {
        // 清除指定的快取
        $cacheKey = 'apk_latest_file_name_r2';
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey); // 清除快取
            return response()->json(['message' => 'Cache cleared successfully.']);
        }

        return response()->json(['message' => 'Cache does not exist.'], 404);
    }

    public function clearApk64LatestFileNameR2Cache()
    {
        // 清除指定的快取
        $cacheKey = 'apk_64_latest_file_name_r2';
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey); // 清除快取
            return response()->json(['message' => 'Cache cleared successfully.']);
        }

        return response()->json(['message' => 'Cache does not exist.'], 404);
    }

    public function clearMproLatestFileNameR2Cache()
    {
        // 清除指定的快取
        $cacheKey = 'mpro_latest_file_name_r2';
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey); // 清除快取
            return response()->json(['message' => 'Cache cleared successfully.']);
        }

        return response()->json(['message' => 'Cache does not exist.'], 404);
    }

    public function getMproLatestFileNameByR2(Request $request)
    {
        $data = Cache::remember('mpro_latest_file_name_r2', 600 * 6, function () {
            $files = collect(Storage::disk('mpror2')
                ->files('/'))
                ->filter(function ($file) {
                    return preg_match('/\.rar$/', $file); // 只匹配 .rar 文件
                })
                ->mapWithKeys(function ($file) {
                    return [
                        $file => Storage::disk('mpror2')
                            ->lastModified($file)
                    ]; // 使用文件的最后修改时间
                })
                ->sortByDesc(function ($timestamp, $file) {
                    return $timestamp; // 按最后修改时间排序
                })
                ->keys();

            if ($files->isEmpty()) {
                return null; // 如果没有文件，返回 null
            }

            $latestFile = $files->first();

            // 生成下载 URL
            $temporaryUrl = Storage::disk('mpror2')
                ->temporaryUrl($latestFile, now()->addMinutes(60), // 设置 URL 的有效期
                    [
                        'ResponseContentType'        => 'application/x-rar-compressed',
                        'ResponseContentDisposition' => 'attachment; filename="' . basename($latestFile) . '"',
                    ]);

//            return redirect()->away($temporaryUrl, 302);
            return [
                'file_name' => basename($latestFile),
                'url'       => $temporaryUrl,
            ];
        });

        // 如果没有找到文件
        if (is_null($data)) {
            return 'No file found';
            //                return response()->json(['message' => 'No files found'], 404);
        }

        return redirect()->away($data['url'], 302);
        // 返回结果
//        return response()->json([
//            'fileName' => $data['file_name'],
//            'url'      => $data['url'],
//        ]);
    }
}
