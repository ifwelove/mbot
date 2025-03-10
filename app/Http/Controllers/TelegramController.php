<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Telegram;

class TelegramController extends Controller
{

    public function __construct()
    {
    }

    private function getTokens()
    {
        $tokens = config('monitor-token');

        return $tokens;
    }

    private function checkAllowToken($token)
    {
        // todo 幾台 和 日期
        $tokens = $this->getTokens();
        if (isset($tokens[$token])) {
            return true;
        } else {
            return false;
        }
    }

    public function dumpAllChatIds()
    {
        $tokens = $this->getTokens();
        $result = [];

        foreach ($tokens as $token => $info) {
            $chatId = Redis::get("token:$token:chat_id");
            if ($chatId) {
                $result[$token] = $chatId;
            } else {
                $result[$token] = "未綁定";
            }
        }
        dd($result);
        return response()->json($result);
    }

    public function clearAllChatIds()
    {
        $tokens = $this->getTokens();
        $deletedCount = 0;

        foreach ($tokens as $token => $info) {
            $key = "token:$token:chat_id";
            if (Redis::exists($key)) {
                Redis::del($key);
                $deletedCount++;
            }
        }
        dd($deletedCount);
        return response()->json([
            'message' => "成功清除 $deletedCount 個綁定",
            'status' => 'success'
        ]);
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
            if (isset($update['message']) && !empty($update['message']['from']['is_bot'])) {
                return response('OK', 200);
            }
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
                if ($this->checkAllowToken($text)) {
                    Redis::set("token:$text:chat_id", $chatId);
//                    Redis::expire("token:$text:chat_id", 86400 * 2); // 設置 2 天有效期
                    Telegram::sendMessage($chatId, sprintf("成功綁定監視器 「token %s] [chat_id %s] 如無法正常通知請將該訊息提供給作者 line id:ifwelove", $text, $chatId));
                } else {
                    Telegram::sendMessage($chatId, ("綁定失敗請輸入正確監視器 token 如無法正常綁定請將該訊息提供給作者 line id:ifwelove"));
                }
            }

        } catch (\Exception $e) {
            Telegram::sendToLineOwner(json_encode([$e->getMessage(), $request->all()]));
        }
        // 記得回傳 200 OK，Telegram 要求 Webhook handler 需快速回覆
        return response('OK', 200);
    }
}
