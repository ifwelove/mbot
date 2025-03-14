<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FileService
{
    protected $fileUrl = 'https://very6.tw/api/download-latest'; // 文件服务器的 API URL
    protected $latestFilename = 'https://very6.tw/api/latest-filename'; // 文件服务器的 API URL

    protected $apkFileUrl = 'https://very6.tw/api/apk-download-latest'; // 文件服务器的 API URL
    protected $apkLatestFilename = 'https://lbs.a5963745.workers.dev/api/apk-64-latest-filename-r2'; // 文件服务器的 API URL
//    protected $apkLatestFilename = 'https://very6.tw/api/apk-latest-filename'; // 文件服务器的 API URL

    public function getLatestFileName()
    {
        // 从 Redis 缓存中获取最新文件名，如果不存在或已过期则执行回调
        $latestFileName = Cache::remember('latest_file_name', 300, function () {
            // 向文件服务器发送请求获取最新文件名
            $response = Http::get($this->latestFilename);

            // 确认响应成功并获取文件名，否则返回默认值
            if ($response->successful() && $response->json('latestFileName')) {
                return $response->json('latestFileName');
            } else {
                // 根据需要处理错误或返回默认值
                return false;
            }
        });

        return $latestFileName;
    }

    public function getLatestFileNameByR2()
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
                ->temporaryUrl($latestFile, now()->addMinutes(60*24), // 设置 URL 的有效期
                    [
                        'ResponseContentType'        => 'application/x-rar-compressed',
                        'ResponseContentDisposition' => 'attachment; filename="' . basename($latestFile) . '"',
                    ]);

            return [
                'file_name' => basename($latestFile),
                'url'       => $temporaryUrl,
            ];
        });

        return $data['file_name'];
    }

    public function getApkLatestFileName()
    {
        // 从 Redis 缓存中获取最新文件名，如果不存在或已过期则执行回调
        $latestFileName = Cache::remember('apk_latest_file_name', 300*6, function () {
            // 向文件服务器发送请求获取最新文件名
            $response = Http::get($this->apkLatestFilename);

            // 确认响应成功并获取文件名，否则返回默认值
            if ($response->successful() && $response->json('fileName')) {
                return $response->json('fileName');
            } else {
                // 根据需要处理错误或返回默认值
                return false;
            }
        });

        return $latestFileName;
    }
}
