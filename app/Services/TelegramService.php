<?php

namespace App\Services;

use GuzzleHttp\Client;

class TelegramService
{
    protected $client;
    protected $token;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = config('telegram.token'); // 從 config 讀取 Telegram Token
    }

    /**
     * 發送 Telegram 訊息
     *
     * @param string|int $chatId
     * @param string $text
     * @return array
     */
    public function sendMessage($chatId, $text)
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $params = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];

        $res = $this->client->post($url, ['form_params' => $params]);

        return json_decode($res->getBody()->getContents(), true);
    }
}
