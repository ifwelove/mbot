<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineController extends Controller
{
    private $client;
    private $bot;
    private $channel_access_token;
    private $channel_secret;
    private $breakLine = "\n";

    public function __construct()
    {
    }

    public function ping(Request $request)
    {
        return response('', 200);
    }

    public function webhook(Request $request)
    {
        // $allowGroupIds = [
        //     'Cbe3b58457b221fbab87a6ea4fc511e62' => '2028-04-25',
        //     //'C1fee20af965f5cf24a9aa357fd06de6d' => '2099-12-31',
        //     'C56f363b379a3ef6186315a91cff355b1' => '2099-12-31',
        //     'Ce3a03bd9ebad87b646d1528f0758cda4' => '2024-06-01',//蝦皮
        //     'C10a641bab5f805653e27a2b519d9af51' => '2024-06-01',//蝦皮
        //     'C292ba114e6161490516e4a03cca82e08' => '2024-06-01',//蝦皮
        //     'Cff69e4cfbc5e5d41d59daf2e8fa4861c' => '2024-06-01',//蝦皮
        //     'C3e03ac3e3416858aa99131b59547e02b' => '2024-06-01',//蝦皮
        //     'Cbff792456a5cb6d5edc2cc9849dc6fe0' => '2024-06-01',//鴨頭
        //     'C534eab6482631b4877ac3cb8f33cd6c8' => '2024-06-01',//蝦皮
        //     'C6059f16ea528f423131ca252ae7d0ce9' => '2024-06-01',//蝦皮
        //     'C01019f22ae445fce3840025a13eb0ae0' => '2024-06-01',//蝦皮
        //     'C5541a5e07a0233b13b8e14949c8677d1' => '2024-06-01',//重生
        //     'Cb727c56179e5f63beedded9e32e70bef' => '2024-06-01',//蝦皮
        //     'C87689c2a84276887e5aef332281813a7' => '2024-06-01',//蝦皮
        //     'C2d35e05a49974bb17ea2444587ca5649' => '2024-06-01',//蝦皮
        //     'Cbb08d25318d8fd7c1defeabd92028f96' => '2024-06-01',//蝦皮
        //     'C4a8f47b56b1f8f878abf56b22df23e25' => '2024-06-01',//蝦皮
        //     'Cc7b984307259c3f4e450a8e491fa37be' => '2025-07-01',//蝦皮
        //     'Cf17fd384a3eb13d056f254cc8c9fd233' => '2024-06-01',//蝦皮
        //     'Cdf353fd56d21f4459f5e049f474ce865' => '2024-06-01',//蝦皮
        //     'C887e26561840dbbdf1187f70dac3f254' => '2024-06-01',//蝦皮
        //     'C3cabdd732b0002d40f8fdef052be8981' => '2024-06-01',//蝦皮
        //     'C31af26e3e788390f7e4952f3a7418065' => '2024-06-01',//蝦皮
        //     'C03281a213813241724c684c99ba1eaea' => '2026-08-01',//蝦皮
        //     'C52b13554f3e886c625fd0b4be4a1051c' => '2026-07-01',//蝦皮
        //     'Ceddfe33df1a1117827a985f9e9558afd' => '2024-06-01',//蝦皮
        //     'C26a13fe72d172668ba88c8d633c1acc2' => '2024-06-01',//蝦皮
        //     'Ce3713bb584982781c0cd86b4b00657f2' => '2024-06-01',//蝦皮
        //     'C15ef73c66d51a14d6a3a9c26ccadc267' => '2024-06-01',//重生
        //     'C614965ecff5d1959f1a999042ace7e10' => '2024-06-01',//蝦皮
        //     'Cc27f0f72a6b0391d94ed4d506f7e73f9' => '2024-06-01',//蝦皮
        //     'C8c3a4c4657a790a2592ecf5ea5aacfb8' => '2024-06-01',//蝦皮
        //     'Ce5484db98a0d0abc36b845244a8731ca' => '2023-06-01',//蝦皮試用
        //     'C2f505367be5589295fbd14f11a8c30a8' => '2023-12-31',//蝦皮
        //     'C91ef86138dc05f4ea7b0aa65ff8473e9' => '2023-12-31',//蝦皮
        //     'C12ad4e1a13253a67cea424f10f68725f' => '2023-12-31',//蝦皮
        //     'C222ac62b8f8826f5d85dd86f25b03e7c' => '2023-09-30',//蝦皮
        //     'C61881bd8783b3e648bc0d76a0c1de09f' => '2023-10-03',//蝦皮
        // ];
        // $pon = $request->get('events')[0]['source']['groupId'];
        // if (!isset($allowGroupIds[$pon])){
        //             return [];
        //         }

        $config = config('line');
        $this->channel_access_token = $config['token'];
        $this->channel_secret = $config['secret'];
        $httpClient   = new CurlHTTPClient($this->channel_access_token);
        $this->bot    = new LINEBot($httpClient, ['channelSecret' => $this->channel_secret]);
        $this->client = $httpClient;

        $bot       = $this->bot;
        $signature = $request->header(\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);
        $body      = $request->getContent();

        try {
            $events = $bot->parseEventRequest($body, $signature);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        $groupId = '';
        //        $allowGroupIds = [
        //            'Cbe3b58457b221fbab87a6ea4fc511e62' => '2028-04-25',
        //            'C1fee20af965f5cf24a9aa357fd06de6d' => '2099-12-31',
        //            'Ce3a03bd9ebad87b646d1528f0758cda4' => '2024-06-01',
        //        ];

        $now = Carbon::now();
        if (!isset($events)) {
            dd('if (!isset($events)) {');
        }
        foreach ($events as $event) {
            if ($event instanceof MessageEvent) {
                $replyToken = $event->getReplyToken();
                $message_type = $event->getMessageType();
                $groupId      = $event->getGroupId();
                // if (!isset($allowGroupIds[$groupId])){
                    // return [];
                // }

                // if ($groupId === 'Cbe3b58457b221fbab87a6ea4fc511e62') {
                //     //Log::info([$events]);
                //     try {
                //         $pon = $request->get('events')[0]['source']['groupId'];
                //         // $groupId = $pon['source']['groupId'];
                //         $this->bot->replyText($replyToken, json_encode([$pon]));
                //     } catch (\Exception $e) {
                //         // $this->bot->replyText($replyToken, json_encode([$e->getMessage()]));
                //         Log::error($e->getMessage());
                //     }
                //     return [];
                // }

                //@ 反向, 即可使用
                //                if (isset($allowGroupIds[$groupId]) && $now->lt(Carbon::createFromFormat('Y-m-d', $allowGroupIds[$groupId])->startOfDay())) {
                //                    $shopeeURl = '請蝦皮賣場購買序號後提供賴群編號開通使用'  . $this->breakLine;
                //                    $shopeeURl .= 'https://shopee.tw/product/2002016/23425009159/'  . $this->breakLine;
                //                    $shopeeURl .= '可在蝦皮聊聊提供編號設定'  . $this->breakLine;
                //                    $shopeeURl .= '您的群組編號：' . $groupId . $this->breakLine;
                //                    $shopeeURl .= '備註：開通服務時間10:00~24:00' . $groupId . $this->breakLine;
                //                    $this->bot->replyText($replyToken, $shopeeURl);
                //                } else {
                //
                //                }
                switch ($message_type) {
                    case 'text':
                        $text = $event->getText();
                        $this->getToken($text, $replyToken, $groupId);
                        $this->boss($text, $replyToken, $groupId);
                        break;
                }
            }
        }

    }


    private function getToken($text, $replyToken, $groupId)
    {
        switch (1) {
            case ($text === '群組編號') :
                $this->bot->replyText($replyToken, $groupId);

                break;

        }
    }
    private function boss($text, $replyToken, $groupId)
    {
        $allowGroupIds = [
            'Caf5d17946d4247442ae7c55c23d7c23c' => '2028-04-25',//heroku
            'Cbe3b58457b221fbab87a6ea4fc511e62' => '2028-04-25',
            //'C1fee20af965f5cf24a9aa357fd06de6d' => '2099-12-31',
            'C56f363b379a3ef6186315a91cff355b1' => '2099-12-31',
            'Ce3a03bd9ebad87b646d1528f0758cda4' => '2024-06-01',//蝦皮
            'C10a641bab5f805653e27a2b519d9af51' => '2024-06-01',//蝦皮
            'C292ba114e6161490516e4a03cca82e08' => '2024-06-01',//蝦皮
            'Cff69e4cfbc5e5d41d59daf2e8fa4861c' => '2024-06-01',//蝦皮
            'C3e03ac3e3416858aa99131b59547e02b' => '2024-06-01',//蝦皮
            'Cbff792456a5cb6d5edc2cc9849dc6fe0' => '2024-06-01',//鴨頭
            'C534eab6482631b4877ac3cb8f33cd6c8' => '2024-06-01',//蝦皮
            'C6059f16ea528f423131ca252ae7d0ce9' => '2024-06-01',//蝦皮
            'C01019f22ae445fce3840025a13eb0ae0' => '2024-06-01',//蝦皮
            'C5541a5e07a0233b13b8e14949c8677d1' => '2024-06-01',//重生
            'Cb727c56179e5f63beedded9e32e70bef' => '2024-06-01',//蝦皮
            'C87689c2a84276887e5aef332281813a7' => '2024-06-01',//蝦皮
            'C2d35e05a49974bb17ea2444587ca5649' => '2024-06-01',//蝦皮
            'Cbb08d25318d8fd7c1defeabd92028f96' => '2024-06-01',//蝦皮
            'C4a8f47b56b1f8f878abf56b22df23e25' => '2024-06-01',//蝦皮
            'Cc7b984307259c3f4e450a8e491fa37be' => '2025-07-01',//蝦皮
            'Cf17fd384a3eb13d056f254cc8c9fd233' => '2024-06-01',//蝦皮
            'Cdf353fd56d21f4459f5e049f474ce865' => '2024-06-01',//蝦皮
            'C887e26561840dbbdf1187f70dac3f254' => '2024-06-01',//蝦皮
            'C3cabdd732b0002d40f8fdef052be8981' => '2024-06-01',//蝦皮
            'C31af26e3e788390f7e4952f3a7418065' => '2024-06-01',//蝦皮
            'C03281a213813241724c684c99ba1eaea' => '2026-08-01',//蝦皮
            'C52b13554f3e886c625fd0b4be4a1051c' => '2026-07-01',//蝦皮
            'Ceddfe33df1a1117827a985f9e9558afd' => '2024-06-01',//蝦皮
            'C26a13fe72d172668ba88c8d633c1acc2' => '2024-06-01',//蝦皮
            'Ce3713bb584982781c0cd86b4b00657f2' => '2024-06-01',//蝦皮
            'C15ef73c66d51a14d6a3a9c26ccadc267' => '2024-06-01',//重生
            'C614965ecff5d1959f1a999042ace7e10' => '2024-06-01',//蝦皮
            'Cc27f0f72a6b0391d94ed4d506f7e73f9' => '2024-06-01',//蝦皮
            'C8c3a4c4657a790a2592ecf5ea5aacfb8' => '2024-06-01',//蝦皮
            'Ce5484db98a0d0abc36b845244a8731ca' => '2023-06-01',//蝦皮試用
            'C2f505367be5589295fbd14f11a8c30a8' => '2023-12-31',//蝦皮
            'C91ef86138dc05f4ea7b0aa65ff8473e9' => '2023-12-31',//蝦皮
            'C12ad4e1a13253a67cea424f10f68725f' => '2023-12-31',//蝦皮
            'C222ac62b8f8826f5d85dd86f25b03e7c' => '2023-09-30',//蝦皮
            'C61881bd8783b3e648bc0d76a0c1de09f' => '2023-10-03',//蝦皮
            'Ce0759f36fcf10a42c5619afefbdf9dad' => '2024-02-01',//蝦皮
            'Ca4973f2e121de82285600b225959e870' => '2024-03-10',//蝦皮
        ];
        $displayUrl = 'https://reurl.cc/WrMXZx';
        //        $displayUrl = url('/');
        switch (1) {
            case ($text === '重生' || $text === '重生時間') :
                if (isset($allowGroupIds[$groupId])){
                    $message    = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                     $message    = '5/1開始收費, 年卡1800元, 價格為每月150元' . $displayUrl . $this->breakLine;
                    $message    .= '沒付費群組即無法使用' . $this->breakLine;
                    $message    .= '歡迎加入賴群討論' . $this->breakLine;
                    $message    .= '請蝦皮賣場購買序號後提供賴群編號開通使用'  . $this->breakLine;
                    $message    .= '購買網址 https://shopee.tw/product/2002016/23425009159/'  . $this->breakLine;
                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;
                }
                $bossConfig = Config::get('boss');
                foreach ($bossConfig as $name => $min) {
                    $message .= $name . ' ' . ($min) . ' 分 ' . $this->breakLine;
                }
                $this->bot->replyText($replyToken, $message);

                break;
            case ($text === '王列表') :
                if (isset($allowGroupIds[$groupId])){
                    $message    = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                    $message    = '5/1開始收費, 年卡1800元, 價格為每月150元' . $displayUrl . $this->breakLine;
                    $message    .= '沒付費群組即無法使用' . $this->breakLine;
                    $message    .= '歡迎加入賴群討論' . $this->breakLine;
                    $message    .= '請蝦皮賣場購買序號後提供賴群編號開通使用'  . $this->breakLine;
                    $message    .= '購買網址 https://shopee.tw/product/2002016/23425009159/'  . $this->breakLine;
                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;
                }

                $message  .= '王 標籤ㄧ覽：' . $this->breakLine;
                $bossTags = Config::get('boss-tags');
                foreach ($bossTags as $name => $tags) {
                    $message .= $name . ' : ' . implode(', ', $tags) . $this->breakLine;
                }
                $this->bot->replyText($replyToken, $message);

                break;
            case ($text === '出' || $text === '出王') :
                Artisan::call('boss:list', [
                    '--groupId' => $groupId,
                ]);
                $bossList = \Illuminate\Support\Facades\Cache::get($groupId);
                if (isset($allowGroupIds[$groupId])){
                    $message    = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                     $message    = '5/1開始收費, 年卡1800元, 價格為每月150元' . $displayUrl . $this->breakLine;
                    $message    .= '沒付費群組即無法使用' . $this->breakLine;
                    $message    .= '歡迎加入賴群討論' . $this->breakLine;
                    $message    .= '請蝦皮賣場購買序號後提供賴群編號開通使用'  . $this->breakLine;
                    $message    .= '購買網址 https://shopee.tw/product/2002016/23425009159/'  . $this->breakLine;
                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;
                }
                $message  .= '出王時間表：' . $this->breakLine;
                if (! is_null($bossList)) {
                    foreach ($bossList as $name => $info) {
                        if ($info['pass'] === 0) {
                            if (isset($info['memo']) && $info['memo'] != '') {
                                $message .= Carbon::createFromFormat('Y-m-d H:i:s', $info['nextTime'])
                                        ->format('H:i:s') . ' ' . $name . ' ' . ' ' . '#' . $info['memo'] . $this->breakLine;
                            } else {
                                $message .= Carbon::createFromFormat('Y-m-d H:i:s', $info['nextTime'])
                                        ->format('H:i:s') . ' ' . $name . ' ' . $this->breakLine;
                            }
                        } else {
                            if (isset($info['memo']) && $info['memo'] != '') {
                                $message .= Carbon::createFromFormat('Y-m-d H:i:s', $info['nextTime'])
                                        ->format('H:i:s') . ' ' . $name . ' ' . '[過' . $info['pass'] . ']' . ' ' . '#' . $info['memo'] . $this->breakLine;
                            } else {
                                $message .= Carbon::createFromFormat('Y-m-d H:i:s', $info['nextTime'])
                                        ->format('H:i:s') . ' ' . $name . ' ' . '[過' . $info['pass'] . ']' . $this->breakLine;
                            }
                        }

                    }
                }
                $this->bot->replyText($replyToken, $message);
                //                Log::info($groupId);
                break;
            case (preg_match('/^([6]{4})\s(.+?)(?:\s(.+?))?$/', $text)) :
            case (preg_match('/^([K]{1})\s(.+?)(?:\s(.+?))?$/', $text)) :
            case (preg_match('/^([k]{1})\s(.+?)(?:\s(.+?))?$/', $text)) :
            case (preg_match('/^([0-1]?[0-9]|2[0-3])([0-5]?[0-9])([0-5]?[0-9])\s(.+?)(?:\s(.+?))?$/', $text)) :
            case (preg_match('/^([6][6][6][6])? .{1,18}$/', $text)) :
            case (preg_match('/^([Kk])? .{1,18}$/', $text)) :
            case (preg_match('/^([0-1][0-9]|[2][0-3])([0-5][0-9])([0-5][0-9])? .{1,18}$/', $text)) :
                $boss     = Config::get('boss');
                $bossTags = Config::get('boss-tags');
                $bossMaps = Config::get('boss-maps');
                $list     = [];
                foreach ($bossTags as $name => $tags) {
                    foreach ($tags as $tag) {
                        $list[$tag] = $name;
                    }
                }
                $text    = preg_replace('/\s(?=\s)/', '', $text);
                $info    = explode(' ', $text);
                $name    = $info[1];
                $memo    = isset($info[2]) ? $info[2] : '';
                $options = [
                    '--groupId' => $groupId,
                    '--name'    => $info[1],
                    '--memo'    => $memo,
                ];

//if ($info[0] == '6666' || $info[0] == 6666) {
                 if ($info[0] == '6666' || $info[0] == 6666 || $info[0] === 'K' || $info[0] === 'k') {
                    $tt                = Carbon::now()->format('His');
                } else {
                    $tt                = str_pad($info[0], 6, Carbon::now()->format('s'));
                }
                $options['--time'] = $tt;
                $time              = Carbon::createFromFormat('His', $tt);
                $now               = Carbon::now();
                if ($time->gt($now)) {
                    $time->subDay();
                }
                if (!isset($list[$name])) {
                    return false;
                }
                $killTime = $time->format('m/d H:i:s');
                $nextTime = $time->addMinutes($boss[$list[$name]])
                    ->format('m/d H:i:s');
                Artisan::call('boss:kill', $options);
                $maps    = implode(', ', $bossMaps[$list[$name]]);
                if (isset($allowGroupIds[$groupId])){
                    $message    = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                     $message    = '5/1開始收費, 年卡1800元, 價格為每月150元' . $displayUrl . $this->breakLine;
                    $message    .= '沒付費群組即無法使用' . $this->breakLine;
                    $message    .= '歡迎加入賴群討論' . $this->breakLine;
                    $message    .= '請蝦皮賣場購買序號後提供賴群編號開通使用'  . $this->breakLine;
                    $message    .= '購買網址 https://shopee.tw/product/2002016/23425009159/'  . $this->breakLine;
                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;
                }

                $message .= sprintf('已紀錄 %s (%s) %s 時間 %s', $list[$name], $info[1], $memo, $this->breakLine);
                $message .= sprintf('地圖：%s %s', $maps, $this->breakLine);
                $message .= sprintf('死亡時間 %s %s', $killTime, $this->breakLine);
                $message .= sprintf('下次出現 %s', $nextTime);

                $this->bot->replyText($replyToken, $message);

                break;
            case ($text === 'clear' || $text === '重置王表') :
                Artisan::call('boss:clear', [
                    '--groupId' => $groupId,
                ]);
                $this->bot->replyText($replyToken, '已清除王表');

                break;
            case ($text === '使用期限' || $text === '有效期限') :
                $message    = '';
                if (isset($allowGroupIds[$groupId])){
                        $message    = '';
                                           $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                                           $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                    }else{
                         $message    = '5/1開始收費, 年卡1800元, 價格為每月150元' . $displayUrl . $this->breakLine;
                    $message    .= '沒付費群組即無法使用' . $this->breakLine;
                    $message    .= '歡迎加入賴群討論' . $this->breakLine;
                    $message    .= '請蝦皮賣場購買序號後提供賴群編號開通使用'  . $this->breakLine;
                    $message    .= '購買網址 https://shopee.tw/product/2002016/23425009159/'  . $this->breakLine;
                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;

                    }
                $this->bot->replyText($replyToken, $message);

                break;

        }
    }

}
