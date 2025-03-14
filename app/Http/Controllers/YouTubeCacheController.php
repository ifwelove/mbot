<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis; // 使用 Redis 閃存
use Illuminate\Support\Arr;

class YouTubeCacheController extends Controller
{
    // Redis 中使用的 Key
    protected $redisKey = 'youtube_channels_cache';

    // TTL (秒) = 24小時
//    protected $cacheTtl = 86400;
    protected $cacheTtl = 172800;

    public function index()
    {
        // 1) 先嘗試從 Redis 拿
        $cacheJson = Redis::get($this->redisKey);

        if ($cacheJson) {
            // 有資料 => 直接 decode
            $cacheData = json_decode($cacheJson, true);
            $channelsData = $cacheData['channels'] ?? [];
        } else {
            // 2) 若 Redis 沒資料 => 呼叫 API, 存到 Redis
            $channelsData = $this->fetchFromApi();
            // 建立快取格式
            $cacheToSave = [
                'updated_at' => time(),
                'channels'   => $channelsData,
            ];
            // 存入 Redis (ex -> set 有效期 = 86400 秒)
            Redis::setex(
                $this->redisKey,
                $this->cacheTtl,
                json_encode($cacheToSave, JSON_UNESCAPED_UNICODE)
            );
        }

        // 3) 排序：訂閱數由大到小
        uasort($channelsData, function ($a, $b) {
            $subA = (int) ($a['subscriberCount'] ?? 0);
            $subB = (int) ($b['subscriberCount'] ?? 0);
            // 大到小
            return $subB - $subA;
        });

        // 4) 丟給 Blade
        return view('youtube.index', [
            'channelsData' => $channelsData,
        ]);
    }

    /**
     * 呼叫 YouTube Data API, 抓 snippet & statistics
     * 傳回 channels 陣列 (channelId => [資訊])
     */
    private function fetchFromApi(): array
    {
        $apiKey = config('services.youtube.api_key');
        if (!$apiKey) {
            return [];
        }

        $allChannelIds = array_keys(config('youtube.channels_map') ?? []);
        if (empty($allChannelIds)) {
            return [];
        }

        // 分批，每次最多 50
        $chunks = array_chunk($allChannelIds, 50);

        $fetchedData = [];
        foreach ($chunks as $channelBatch) {
            $idString = implode(',', $channelBatch);

            $url = "https://www.googleapis.com/youtube/v3/channels"
                . "?part=snippet,statistics"
                . "&id={$idString}"
                . "&key={$apiKey}";

            $response = Http::get($url);
            if (!$response->successful()) {
                // 若失敗就跳過 or log
                continue;
            }

            $json = $response->json();
            $items = $json['items'] ?? [];

            foreach ($items as $item) {
                $cid = $item['id'];
                $snippet = $item['snippet'] ?? [];
                $stats   = $item['statistics'] ?? [];
                $thumbs  = $snippet['thumbnails'] ?? [];

                $fetchedData[$cid] = [
                    'title'           => $snippet['title'] ?? config('youtube.channels_map')[$cid] ?? '未知頻道',
                    'description'     => $snippet['description'] ?? '',
                    'publishedAt'     => $snippet['publishedAt'] ?? '',
                    'subscriberCount' => $stats['subscriberCount'] ?? '',
                    'viewCount'       => $stats['viewCount'] ?? '',
                    'videoCount'      => $stats['videoCount'] ?? '',
                    'thumbnails'      => $thumbs,
                ];
            }
        }

        return $fetchedData;
    }
}
