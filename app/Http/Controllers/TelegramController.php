<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class TelegramController extends Controller
{

    public function __construct()
    {
    }


    public function webhookHandler(Request $request)
    {
//        $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
//        $client     = new Client();
//        $headers    = [
//            'Authorization' => sprintf('Bearer %s', $owen_token),
//            'Content-Type'  => 'application/x-www-form-urlencoded'
//        ];
//        $options    = [
//            'form_params' => [
//                //                'message' => $message
//                //                    'message' => $request->post('pc_name')
//                'message' => json_encode($request->all())
//            ]
//        ];
//        $response   = $client->request('POST', 'https://notify-api.line.me/api/notify', [
//            'headers'     => $headers,
//            'form_params' => $options['form_params']
//        ]);
        try {
            // Telegram 推送過來的 JSON
            // e.g. 取出 raw JSON（如果需要）
            // $input = $request->getContent();

            // 直接轉換為陣列
            $update = $request->all();
            // 可能會有以下結構
            // [
            //   "update_id" => 123456789,
            //   "message" => [
            //       "message_id" => 1,
            //       "from" => [...],
            //       "chat" => [
            //           "id" => 987654321,
            //           ...
            //       ],
            //       "text" => "/start"
            //   ],
            //   ...
            // ]

            // 在這裡，您就能讀取 $update["message"]["chat"]["id"]
            // 或 $update["callback_query"]["message"]["chat"]["id"] (視情況)
            // 並處理所有您需要的功能（儲存 chat_id、回覆訊息等等）

            // ================
            // 1. 取得 chat_id
            // ================
            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id'];
                $text   = $update['message']['text'] ?? '';
//                $chatId = 7989823638;
                // ... 您可以紀錄 user/客戶 => $chatId 的關係到 DB
                // 例如：
                // DB::table('users')->where('some_condition', ...)->update(['telegram_chat_id' => $chatId]);

                // 若需要，您也可以回覆訊息給此人
                // 例如：sendTelegramMessage($chatId, "Hello! 已經綁定你的 chat_id: $chatId");
                $this->sendTelegramMessage($chatId, "Hello! 已經綁定你的 chat_id: $chatId");
//            $this->sendTelegramMessage($chatId, json_encode([$update]));
            }

        } catch (\Exception $e) {
            $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
            $client     = new Client();
            $headers    = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options    = [
                'form_params' => [
                    //                'message' => $message
                    //                    'message' => $request->post('pc_name')
                    'message' => json_encode([$e->getMessage(), $request->all()])
                ]
            ];
            $response   = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);
        }
        // 記得回傳 200 OK，Telegram 要求 Webhook handler 需快速回覆
        return response('OK', 200);
    }

    public function sendTelegramMessage($chatId, $text)
    {
        $telegram = config('telegram');
        $token = $telegram['token'];
//        dd($token);
        $url   = "https://api.telegram.org/bot{$token}/sendMessage";

        $client = new Client();
        $params = [
            'chat_id' => 7989823638,
            'text'    => $text,
            // 'parse_mode' => 'HTML'
        ];
        $res    = $client->post($url, ['form_params' => $params]);

        return json_decode($res->getBody()
            ->getContents(), true);
    }
}
