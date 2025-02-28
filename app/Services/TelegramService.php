<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class TelegramService
{
    protected $client;    // 給 Line 使用
    protected $tgClient;  // 給 Telegram 使用
    protected $tgToken;

    public function __construct()
    {
        // Line Notify 與 Telegram 可以分開使用兩個 Client (也可以共用同一個沒關係)
        $this->client   = new Client();
        $this->tgClient = new Client();

        // 讀取 Telegram Bot Token
        $this->tgToken = config('telegram.token');
    }

    /**
     * 「過渡期」自動判斷：有綁 Telegram 就推 Telegram, 否則推 Line
     * 3/1 之後若沒綁，就不推。
     *
     * @param string $lineToken 客戶的Line Token(或您系統發給客戶的token)
     * @param string $message
     */
    public function sendAlertMessage($lineToken, $message)
    {
        // 1. 判斷當前日期是否已過 3/1
        $now = Carbon::now();
        // 假設您的截止日期是 2025-03-01
        $cutoff = Carbon::createFromFormat('Y-m-d', '2025-03-29');

        // 2. 從 Redis 拿看看是否有客戶綁定的 Telegram chat_id
        $chatId = Redis::get("token:{$lineToken}:chat_id");

        if ($now->lessThan($cutoff)) {
            // (A) 還沒到 3/1
//            if ($chatId) {
                // 已綁定 Telegram
//                $this->sendToTelegram($chatId, $message);
//            } else {
                // 尚未綁定 => 改用 Line
                $this->sendToLine($lineToken, $message);
//            }
        } else {
            // (B) 已到(或超過) 3/1
            if ($chatId) {
                // 有 chat_id => 用 Telegram
                $this->sendToTelegram($chatId, $message);
            } else {
                // 沒有 => 直接不推
                // (若您要改成繼續推Line，也可以在此呼叫 sendToLine())
            }
        }
    }

    /**
     * 基礎方法：直接發送訊息給 Telegram
     */
    public function sendToTelegram($chatId, $text)
    {
        $url = "https://api.telegram.org/bot{$this->tgToken}/sendMessage";

        $params = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];

        try {
            $res = $this->tgClient->post($url, ['form_params' => $params, 'timeout' => 5,]);
            return json_decode($res->getBody()
                ->getContents(), true);
        } catch (\Exception $e) {
            // 錯誤紀錄
        }
    }

    /**
     * 基礎方法：發送訊息到 Line Notify
     */
    public function sendToLine($lineToken, $text)
    {
        $url = "https://notify-api.line.me/api/notify";

        $headers = [
            'Authorization' => "Bearer {$lineToken}",
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];

        $params = [
            'message' => $text,
        ];

        try {
            $res = $this->client->post($url, [
                'headers'     => $headers,
                'form_params' => $params,
                'timeout'     => 5,
            ]);
            return json_decode($res->getBody()
                ->getContents(), true);
        } catch (\Exception $e) {
            // 錯誤紀錄
        }
    }

    public function sendToLineOwner($text)
    {
        $lineToken = config('line.owner_token');
        $url       = "https://notify-api.line.me/api/notify";

        $headers = [
            'Authorization' => "Bearer {$lineToken}",
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];

        $params = [
            'message' => $text,
        ];

        try {
            $res = $this->client->post($url, [
                'headers'     => $headers,
                'form_params' => $params,
                'timeout'     => 5,
            ]);
            return json_decode($res->getBody()
                ->getContents(), true);
        } catch (\Exception $e) {
            // 錯誤紀錄
        }
    }

    /**
     * 如果您還是需要原本的「直接傳 Telegram」功能，就保留這個。
     */
    public function sendMessage($chatId, $text)
    {
        return $this->sendToTelegram($chatId, $text);
    }
}
