<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class YouTubeController extends Controller
{
    /**
     * 從 @handle 查詢頻道資訊 (頻道ID、訂閱數、標題...)
     */
//    public function getChannelInfoByHandle($handle)
//    {
//        // 1. 取得 API Key
//        $apiKey = config('services.youtube.api_key');
//        if (!$apiKey) {
//            return response()->json(['error' => 'YouTube API key not configured'], 500);
//        }
//
//        // 2. 處理 handle 字串，確保帶 '@'
//        //    如果傳入已經包含 '@'，則 ltrim() 不會移除多餘的；若沒有，就加上
//        $query = '@' . ltrim($handle, '@');
//
//        // 3. 第一步：使用 search.list 搜尋，拿到 channelId
//        $searchUrl = "https://www.googleapis.com/youtube/v3/search"
//            . "?part=snippet"
//            . "&type=channel"
//            . "&q=" . urlencode($query)
//            . "&maxResults=1"
//            . "&key={$apiKey}";
//
//        $searchResponse = Http::get($searchUrl);
//        if (!$searchResponse->successful()) {
//            return response()->json(['error' => 'Failed to call search.list'], 500);
//        }
//
//        $searchData = $searchResponse->json();
//        if (empty($searchData['items'])) {
//            return response()->json(['error' => 'No channel found for handle: ' . $handle], 404);
//        }
//
//        // 從搜尋結果拿到 channelId
//        $channelId = $searchData['items'][0]['id']['channelId'] ?? null;
//        if (!$channelId) {
//            return response()->json(['error' => 'Channel ID not found'], 404);
//        }
//
//        // 4. 第二步：用 channels.list 查詢該 channelId 詳細資訊 (訂閱數, 標題, ...)
//        $channelUrl = "https://www.googleapis.com/youtube/v3/channels"
//            . "?part=snippet,statistics"
//            . "&id={$channelId}"
//            . "&key={$apiKey}";
//
//        $channelResponse = Http::get($channelUrl);
//        if (!$channelResponse->successful()) {
//            return response()->json(['error' => 'Failed to call channels.list'], 500);
//        }
//
//        $channelData = $channelResponse->json();
//        if (empty($channelData['items'])) {
//            return response()->json(['error' => 'Channel data not returned'], 404);
//        }
//
//        // 5. 取得所需的資訊
//        $item = $channelData['items'][0];
//        $snippet = $item['snippet'] ?? [];
//        $statistics = $item['statistics'] ?? [];
//
//        $result = [
//            'channelId'       => $channelId,
//            'title'           => $snippet['title'] ?? '',
//            'description'     => $snippet['description'] ?? '',
//            'subscriberCount' => $statistics['subscriberCount'] ?? 0,
//            'videoCount'      => $statistics['videoCount'] ?? 0,
//            'viewCount'       => $statistics['viewCount'] ?? 0,
//            // 看需要，可再加更多欄位
//        ];
//
//        // 6. 回傳 JSON
//        return response()->json($result);
//    }

    public function showChannelInfo()
    {
        // 這裡假設你已經透過之前的方法拿到 $channelData
        // 範例的資料長這樣:
        // $channelData = [
        //     "channelId"       => "UCjMBtSoVSmqE2jTqwKh3ttg",
        //     "title"           => "Andy老師",
        //     "description"     => "讓平凡的生活，變成好玩的冒險旅程。\nEmail - info.lightofheart@gmail.com",
        //     "subscriberCount" => "1740000",
        //     "videoCount"      => "112",
        //     "viewCount"       => "23313690",
        // ];

        // 假設已經把這段拿到的 JSON decode 成 array，或是本來就已是 array
        $channelData = [
            'channelId' => 'UCjMBtSoVSmqE2jTqwKh3ttg',
            'title' => 'Andy老師',
            'description' => "讓平凡的生活，變成好玩的冒險旅程。\nEmail - info.lightofheart@gmail.com",
            'subscriberCount' => '1740000',
            'videoCount' => '112',
            'viewCount' => '23313690',
        ];

        // 將這些資料丟給 Blade (resources/views/youtube/show.blade.php)
        return view('youtube.show', [
            'channel' => $channelData
        ]);
    }

    public function showVS()
    {
        // 這裡假設你已經透過之前的方法拿到 $channelData
        // 範例的資料長這樣:
        // $channelData = [
        //     "channelId"       => "UCjMBtSoVSmqE2jTqwKh3ttg",
        //     "title"           => "Andy老師",
        //     "description"     => "讓平凡的生活，變成好玩的冒險旅程。\nEmail - info.lightofheart@gmail.com",
        //     "subscriberCount" => "1740000",
        //     "videoCount"      => "112",
        //     "viewCount"       => "23313690",
        // ];

        // 假設已經把這段拿到的 JSON decode 成 array，或是本來就已是 array
        $channelData = [
            'channelId' => 'UCjMBtSoVSmqE2jTqwKh3ttg',
            'title' => 'Andy老師',
            'description' => "讓平凡的生活，變成好玩的冒險旅程。\nEmail - info.lightofheart@gmail.com",
            'subscriberCount' => '1740000',
            'videoCount' => '112',
            'viewCount' => '23313690',
        ];

        // 將這些資料丟給 Blade (resources/views/youtube/show.blade.php)
        return view('youtube.vs', [
            'channel' => $channelData
        ]);
    }
    /**
     * 從 @handle 找到頻道詳細資訊 (channelId, 訂閱數, 頻道標題, 頻道縮圖URL 等)
     */
    private function getChannelInfoByHandle(string $handle)
    {
        // 確保包含 @
        $handle = '@' . ltrim($handle, '@');

        $apiKey = config('services.youtube.api_key');
        if (!$apiKey) {
            return null; // 或丟出例外
        }

        // 第一步：search.list 找 channelId
        $searchUrl = "https://www.googleapis.com/youtube/v3/search"
            . "?part=snippet"
            . "&type=channel"
            . "&q=" . urlencode($handle)
            . "&maxResults=1"
            . "&key={$apiKey}";

        $searchResponse = Http::get($searchUrl);
        if (!$searchResponse->successful()) {
            return null;
        }
        $searchData = $searchResponse->json();
        $channelId = $searchData['items'][0]['id']['channelId'] ?? null;
        if (!$channelId) {
            return null;
        }

        // 第二步：channels.list 用 channelId 查詳細資料
        $channelUrl = "https://www.googleapis.com/youtube/v3/channels"
            . "?part=snippet,statistics"
            . "&id={$channelId}"
            . "&key={$apiKey}";

        $channelResponse = Http::get($channelUrl);
        if (!$channelResponse->successful()) {
            return null;
        }
        $channelData = $channelResponse->json();
        if (empty($channelData['items'])) {
            return null;
        }

        $item = $channelData['items'][0];
        $snippet = $item['snippet'] ?? [];
        $statistics = $item['statistics'] ?? [];

        // 找可用的縮圖 URL（預設拿 high，如沒 high 就拿 medium，再沒就 default）
        $thumbnails = $snippet['thumbnails'] ?? [];
        $thumbnailUrl = $thumbnails['high']['url']
            ?? $thumbnails['medium']['url']
            ?? $thumbnails['default']['url']
            ?? '';

        return [
            'channelId'       => $channelId,
            'title'           => $snippet['title'] ?? '',
            'description'     => $snippet['description'] ?? '',
            'subscriberCount' => (int)($statistics['subscriberCount'] ?? 0),
            'videoCount'      => (int)($statistics['videoCount'] ?? 0),
            'viewCount'       => (int)($statistics['viewCount'] ?? 0),
            'thumbnailUrl'    => $thumbnailUrl, // <--- 把縮圖網址加進來
        ];
    }

    /**
     * 顯示兩個頻道訂閱數對決的頁面
     * ex: /youtube/faceoff?handle1=andy0623&handle2=pewdiepie
     */
    public function faceOff(Request $request)
    {
        // 從 Query String 取得想對決的 handle（若沒帶，預設兩個頻道）
        $handle1 = $request->input('handle1', 'andy0623');
        $handle2 = $request->input('handle2', 'crowd1111');

        // 撈取兩個頻道資訊
        $channelA = $this->getChannelInfoByHandle($handle1);
        $channelB = $this->getChannelInfoByHandle($handle2);

        // 錯誤處理省略，視需求自行補上

        return view('youtube.faceoff', [
            'channelA' => $channelA,
            'channelB' => $channelB,
        ]);
    }
    public function vs(Request $request)
    {
        // 從 Query String 取得想對決的 handle（若沒帶，預設兩個頻道）
        $handle1 = 'andy0623';
        $handle2 = 'crowd1111';

        // 撈取兩個頻道資訊
        $channelA = $this->getChannelInfoByHandle($handle1);
        $channelB = $this->getChannelInfoByHandle($handle2);

        // 錯誤處理省略，視需求自行補上

        return view('youtube.faceoff', [
            'channelA' => $channelA,
            'channelB' => $channelB,
        ]);
    }
}
