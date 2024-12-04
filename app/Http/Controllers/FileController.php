<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName(); // 使用文件的原始名称

        // 直接存储到 'mpro' 磁盘根目录下
        $file->storeAs('/', $fileName, 'mpro');

        return response()->json(['message' => 'File uploaded successfully', 'fileName' => $fileName]);
    }


    public function downloadLatest()
    {
        $files = collect(Storage::disk('mpro')->files('/'))
            ->filter(function ($file) {
                // 更新正則表達式，支持多個小數點的版本號
                return preg_match('/大尾3-\d+(\.\d+){1,3}-v\d+\.\d+\.\d+\.rar$/', $file);
            })
            ->mapWithKeys(function ($file) {
                // 提取版本號進行排序
                preg_match('/大尾3-(\d+(\.\d+){1,3})-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
                return [$file => $matches[1]]; // 使用版本號中的第一部分進行排序
            })
            ->sort(function ($version1, $version2) {
                // 比較版本號
                $v1Parts = explode('.', $version1);
                $v2Parts = explode('.', $version2);

                // 獲取較長的版本號長度，並補齊短的部分
                $maxLength = max(count($v1Parts), count($v2Parts));
                $v1Parts = array_pad($v1Parts, $maxLength, 0);
                $v2Parts = array_pad($v2Parts, $maxLength, 0);

                // 比較每個版本號部分
                for ($i = 0; $i < $maxLength; $i++) {
                    if ((int)$v1Parts[$i] > (int)$v2Parts[$i]) {
                        return 1; // $version1 比較大
                    } elseif ((int)$v1Parts[$i] < (int)$v2Parts[$i]) {
                        return -1; // $version2 比較大
                    }
                }

                return 0; // 版本號相等
            })
            ->reverse() // 降序排序
            ->keys();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found']);
        }

        $latestFile = $files->first();

        // 返回下載最新版本的檔案
        return response()->download(storage_path('app/mpro/' . $latestFile));
    }

//    public function downloadLatest()
//    {
//        // $files = collect(Storage::disk('mpro')->files('/'))
//        //             ->filter(function ($file) {
//        //                 return preg_match('/大尾3-\d+\.\d+\.\d+-v\d+\.\d+\.\d+\.rar$/', $file);
//        //             })
//        //             ->sortByDesc(function ($file) {
//        //                 preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
//        //                 return $matches[2]; // 使用版本号排序
//        //             });
//
//        //     $files = collect(Storage::disk('mpro')->files('/'))
//        // ->filter(function ($file) {
//        //     return preg_match('/大尾3-\d+\.\d+\.\d+-v(\d+)\.(\d+)\.(\d+)\.rar$/', $file);
//        // })
//        // ->sortByDesc(function ($file) {
//        //     preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+)\.(\d+)\.(\d+)\.rar$/', $file, $matches);
//        //     // 填充每个版本号部分到相同的长度，确保数字排序正确
//        //     return sprintf('%03d%03d%03d', $matches[2], $matches[3], $matches[4]);
//        // });
//        $files = collect(Storage::disk('mpro')->files('/'))
//            ->filter(function ($file) {
//                return preg_match('/大尾3-\d+\.\d+\.\d+-v\d+\.\d+\.\d+\.rar$/', $file);
//            })
//            ->mapWithKeys(function ($file) {
//                preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
//                return [$file => $matches[2]]; // 使用版本号作为排序依据
//            })
//            ->sortBy(function ($version, $file) {
//                return $version;
//            })
//            ->reverse() // 颠倒排序顺序以实现降序
//            ->keys();
//        // ->map(function ($file) {
//        // return ['name' => basename($file)];
//        // });
//
//        if ($files->isEmpty()) {
//            return response()->json(['message' => 'No files found']);
//        }
//
//        $latestFile = $files->first();
//
//        return response()->download(storage_path('app/mpro/' . $latestFile));
//    }

    //看起來用不到
    public function listFiles()
    {
        $files = collect(Storage::disk('mpro')->files('/'))
            ->filter(function ($file) {
                return preg_match('/大尾3-\d+\.\d+\.\d+-v\d+\.\d+\.\d+\.rar$/', $file);
            })
            ->mapWithKeys(function ($file) {
                preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
                return [$file => $matches[2]]; // 使用版本号作为排序依据
            })
            ->sortBy(function ($version, $file) {
                return $version;
            })
            ->reverse() // 颠倒排序顺序以实现降序
            ->keys()
            ->map(function ($file) {
                return ['name' => basename($file)];
            });

        return response()->json(['files' => $files]);
    }


    public function getLatestFileName()
    {
        $files = collect(Storage::disk('mpro')->files('/'))
            ->filter(function ($file) {
                // 更新正則表達式來匹配新的版本格式（支持多個小數點）
                return preg_match('/大尾3-\d+(\.\d+){1,3}-v\d+\.\d+\.\d+\.rar$/', $file);
            })
            ->mapWithKeys(function ($file) {
                // 提取新的版本號
                preg_match('/大尾3-(\d+(\.\d+){1,3})-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
                return [$file => $matches[1]]; // 使用新的格式中的版本号作为排序依据
            })
            ->sort(function ($version1, $version2) {
                // 比较版本号，并处理不同长度的版本格式
                $v1Parts = explode('.', $version1);
                $v2Parts = explode('.', $version2);

                $maxLength = max(count($v1Parts), count($v2Parts));

                // 补齐短的版本号
                $v1Parts = array_pad($v1Parts, $maxLength, 0);
                $v2Parts = array_pad($v2Parts, $maxLength, 0);

                // 逐个比较每部分版本号
                for ($i = 0; $i < $maxLength; $i++) {
                    if ((int)$v1Parts[$i] > (int)$v2Parts[$i]) {
                        return 1; // $version1 比较大
                    } elseif ((int)$v1Parts[$i] < (int)$v2Parts[$i]) {
                        return -1; // $version2 比较大
                    }
                }

                return 0; // 两个版本号相等
            })
            ->reverse() // 颠倒排序顺序以实现降序
            ->keys();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found']);
        }

        $latestFile = $files->first();

        return response()->json(['latestFileName' => $latestFile]);
    }

    public function getLatestFileNameOld()
    {
        // $files = collect(Storage::disk('mpro')->files('/'))
        //             ->filter(function ($file) {
        //                 return preg_match('/大尾3-\d+\.\d+\.\d+-v\d+\.\d+\.\d+\.rar$/', $file);
        //             })
        //             ->sortByDesc(function ($file) {
        //                 preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
        //                 return $matches[2]; // 使用版本号排序
        //             });
        //     $files = collect(Storage::disk('mpro')->files('/'))
        // ->filter(function ($file) {
        //     return preg_match('/大尾3-\d+\.\d+\.\d+-v(\d+)\.(\d+)\.(\d+)\.rar$/', $file);
        // })
        // ->sortByDesc(function ($file) {
        //     preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+)\.(\d+)\.(\d+)\.rar$/', $file, $matches);
        //     // 填充每个版本号部分到相同的长度，确保数字排序正确
        //     return sprintf('%03d%03d%03d', $matches[2], $matches[3], $matches[4]);
        // });
        $files = collect(Storage::disk('mpro')->files('/'))
            ->filter(function ($file) {
                return preg_match('/大尾3-\d+\.\d+\.\d+-v\d+\.\d+\.\d+\.rar$/', $file);
            })
            ->mapWithKeys(function ($file) {
                preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
                return [$file => $matches[2]]; // 使用版本号作为排序依据
            })
            ->sortBy(function ($version, $file) {
                return $version;
            })
            ->reverse() // 颠倒排序顺序以实现降序
            ->keys();
        // ->map(function ($file) {
        //     return ['name' => basename($file)];
        // });

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found']);
        }

        $latestFile = $files->first();

        return response()->json(['latestFileName' => $latestFile]);
    }

    public function apkUpload(Request $request)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName(); // 使用文件的原始名称

        // 直接存储到 'mpro' 磁盘根目录下
        $file->storeAs('/', $fileName, 'apks');

        return response()->json(['message' => 'File uploaded successfully', 'fileName' => $fileName]);
    }


    public function apkDownloadLatest()
    {
        // 获取所有 .xapk 文件
        $files = collect(Storage::disk('apks')->files('/'))
            ->filter(function ($file) {
                return preg_match('/\.xapk$/', $file); // 只匹配 .xapk 檔案
            })
            ->mapWithKeys(function ($file) {
                return [$file => Storage::disk('apks')->lastModified($file)]; // 使用檔案的最後修改時間
            })
            ->sortByDesc(function ($timestamp, $file) {
                return $timestamp; // 依照最後修改時間排序
            })
            ->keys();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found']);
        }

        // 获取最新的檔案
        $latestFile = $files->first();

        return response()->download(storage_path('app/apks/' . $latestFile));
    }



    public function getApkLatestFileName()
    {
        // 获取所有 .xapk 文件
        $files = collect(Storage::disk('apks')->files('/'))
            ->filter(function ($file) {
                return preg_match('/\.xapk$/', $file); // 只匹配 .xapk 檔案
            })
            ->mapWithKeys(function ($file) {
                return [$file => Storage::disk('apks')->lastModified($file)]; // 使用檔案的最後修改時間
            })
            ->sortByDesc(function ($timestamp, $file) {
                return $timestamp; // 依照最後修改時間排序
            })
            ->keys();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found']);
        }

        // 获取最新的檔案
        $latestFile = $files->first();

        return response()->json(['latestFileName' => $latestFile]);
    }

    //看起來用不到
    //    public function apkListFiles()
    //    {
    //        $files = collect(Storage::disk('apks')->files('/'))
    //            ->filter(function ($file) {
    //                return preg_match('/大尾3-\d+\.\d+\.\d+-v\d+\.\d+\.\d+\.rar$/', $file);
    //            })
    //            ->mapWithKeys(function ($file) {
    //                preg_match('/大尾3-(\d+\.\d+\.\d+)-v(\d+\.\d+\.\d+)\.rar$/', $file, $matches);
    //                return [$file => $matches[2]]; // 使用版本号作为排序依据
    //            })
    //            ->sortBy(function ($version, $file) {
    //                return $version;
    //            })
    //            ->reverse() // 颠倒排序顺序以实现降序
    //            ->keys()
    //            ->map(function ($file) {
    //                return ['name' => basename($file)];
    //            });
    //
    //        return response()->json(['files' => $files]);
    //    }
    public function apkUploadByR2(Request $request)
    {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName(); // 使用文件的原始名稱

        // 存儲到 R2 的根目錄
        Storage::disk('r2')->putFileAs('/', $file, $fileName);

        return response()->json(['message' => 'File uploaded successfully', 'fileName' => $fileName]);
    }

    public function apkDownloadLatestByR2()
    {
        // 獲取 R2 Bucket 中所有 .xapk 文件
        $files = collect(Storage::disk('r2')->files('/'))
            ->filter(function ($file) {
                return preg_match('/\.xapk$/', $file); // 只匹配 .xapk 檔案
            })
            ->mapWithKeys(function ($file) {
                return [$file => Storage::disk('r2')->lastModified($file)]; // 使用檔案的最後修改時間
            })
            ->sortByDesc(function ($timestamp, $file) {
                return $timestamp; // 依照最後修改時間排序
            })
            ->keys();

        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found']);
        }

        // 獲取最新的檔案
        $latestFile = $files->first();

        // 生成下載 URL
        $temporaryUrl = Storage::disk('r2')->temporaryUrl(
            $latestFile,
            now()->addMinutes(10) // 設置 URL 的有效期
        );

        return response()->json(['message' => 'Download URL generated', 'url' => $temporaryUrl]);
    }

}
