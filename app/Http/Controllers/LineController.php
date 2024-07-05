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

    private function getTokens()
    {
        $tokens = config('boss-token');

        return $tokens;
    }

    public function ping(Request $request)
    {
        $line      = config('line');
        $tableName = '';
        $count     = 1;
        DB::connection('sync')
            ->table('ItemSell')
            ->chunkById(1000, function ($items) use (&$count, &$tableName, $line) {
                $insert = [];
                foreach ($items as $item) {
                    $row      = [
                        'ItemVolume' => $item->ItemVolume,
                        'ItemCount'  => $item->ItemCount,
                        'ItemName'   => $item->ItemName,
                        'ServerID'   => $item->ServerID,
                        'TradeType'  => $item->TradeType,
                        'Update_at'  => $item->Update_at,
                        'ItemColor'  => $item->ItemColor,
                    ];
                    $insert[] = $row;
                }
                \Illuminate\Support\Facades\Cache::put(sprintf('test_%s', $count), json_encode($insert), 600);
                //                $client    = new Client();
                //                $response  = $client->post(sprintf('%s/items/test', $line['sync_url']), [
                //                    'json' => [
                //                        'items' => $insert
                //                    ]
                //                ]);
                //                $tableName = json_decode($response->getBody()
                //                    ->getContents(), true);
                $count++;
            }, 'index');
        //        $client             = new Client();
        //        $response           = $client->post(sprintf('%s/items/test', $line['sync_url']), [
        //            'json' => [
        //                'table' => $tableName['table']
        //            ]
        //        ]);

        return response('', 200);
    }

    public function webhook(Request $request)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        // Send the response to the client
        response()
            ->json()
            ->send();
        // If you're using FastCGI, this will end the request/response cycle
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $allowGroupIds              = $this->getTokens();
        $config                     = config('line');
        $this->channel_access_token = $config['token'];
        $this->channel_secret       = $config['secret'];
        $httpClient                 = new CurlHTTPClient($this->channel_access_token);
        $this->bot                  = new LINEBot($httpClient, ['channelSecret' => $this->channel_secret]);
        $this->client               = $httpClient;

        $bot       = $this->bot;
        $signature = $request->header(\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);
        $body      = $request->getContent();

        try {
            $events = $bot->parseEventRequest($body, $signature);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        $now = Carbon::now();
        if (! isset($events)) {
            dd('if (!isset($events)) {');
        }
        foreach ($events as $event) {
            if ($event instanceof MessageEvent) {
                $replyToken   = $event->getReplyToken();
                $message_type = $event->getMessageType();
                $groupId      = $event->getGroupId();

                //@ 反向, 即可使用
                if (isset($allowGroupIds[$groupId]) && $now->gt(Carbon::createFromFormat('Y-m-d', $allowGroupIds[$groupId])
                        ->startOfDay())) {
                    $shopeeURl = '序號已經到期:' . $allowGroupIds[$groupId] . $this->breakLine;
                    $shopeeURl .= '請私訊作者購買 line id: ifwelove' . $this->breakLine;
                    $shopeeURl .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $shopeeURl);
                    break;
                } else {
                    if (! isset($allowGroupIds[$groupId])) {
                        $shopeeURl = '群組尚未註冊:' . $this->breakLine;
                        $shopeeURl .= '請私訊作者購買 line id: ifwelove' . $this->breakLine;
                        $shopeeURl .= '您的群組編號：' . $groupId . $this->breakLine;
                        $this->bot->replyText($replyToken, $shopeeURl);
                        break;
                    } else {
                        switch ($message_type) {
                            case 'text':
                                $text = $event->getText();
                                //                        $this->getToken($text, $replyToken, $groupId);
                                $this->getToken($text, $replyToken, $groupId, $request);
                                $this->boss($text, $replyToken, $groupId);
                                break;
                        }
                    }
                }
            }
        }

    }


    private function getToken($text, $replyToken, $groupId, $request)
    {
        switch (1) {
            case ($text === '群組編號') :
                //                $this->bot->replyText($replyToken, json_encode($request->all()));
                $this->bot->replyText($replyToken, $groupId);

                break;

        }
    }

    private function boss($text, $replyToken, $groupId)
    {
        $allowGroupIds = $this->getTokens();
        $displayUrl    = 'https://reurl.cc/WrMXZx';
        //        $displayUrl = url('/');
        switch (1) {
            case ($text === '重生' || $text === '重生時間') :
                if (isset($allowGroupIds[$groupId])) {
                    $message = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                    $message = '' . $displayUrl . $this->breakLine;
                    $message .= '沒付費群組即無法使用' . $this->breakLine;
                    $message .= '歡迎加入賴群討論' . $this->breakLine;
                    $message .= '請蝦皮賣場購買序號後提供賴群編號開通使用' . $this->breakLine;
                    $message .= '或者私訊作者購買 line id: ifwelove' . $this->breakLine;
                    $message .= '您的群組編號：' . $groupId . $this->breakLine;
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
                if (isset($allowGroupIds[$groupId])) {
                    $message = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                    $message = '' . $displayUrl . $this->breakLine;
                    $message .= '沒付費群組即無法使用' . $this->breakLine;
                    $message .= '歡迎加入賴群討論' . $this->breakLine;
                    $message .= '請蝦皮賣場購買序號後提供賴群編號開通使用' . $this->breakLine;
                    $message .= '或者私訊作者購買 line id: ifwelove' . $this->breakLine;
                    $message .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;
                }

                $message  .= '王 標籤ㄧ覽：' . $this->breakLine;
                $bossTags = Config::get('boss-list');
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
                if (isset($allowGroupIds[$groupId])) {
                    $message = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                    $message = '' . $displayUrl . $this->breakLine;
                    $message .= '沒付費群組即無法使用' . $this->breakLine;
                    $message .= '歡迎加入賴群討論' . $this->breakLine;
                    $message .= '請蝦皮賣場購買序號後提供賴群編號開通使用' . $this->breakLine;
                    $message .= '或者私訊作者購買 line id: ifwelove' . $this->breakLine;
                    $message .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;
                }
                $message .= '出王時間表：' . $this->breakLine;
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
                if ($info[0] === '6666' || $info[0] === 6666 || $info[0] === '666' || $info[0] === 666 || $info[0] === 'K' || $info[0] === 'k') {
                    $tt = Carbon::now()
                        ->format('His');
                } else {
                    $tt = str_pad($info[0], 6, Carbon::now()
                        ->format('s'));
                }
                $options['--time'] = $tt;
                $time              = Carbon::createFromFormat('His', $tt);
                $now               = Carbon::now();
                if ($time->gt($now)) {
                    $time->subDay();
                }
                if (! isset($list[$name])) {
                    return false;
                }
                $killTime = $time->format('m/d H:i:s');
                $nextTime = $time->addMinutes($boss[$list[$name]])
                    ->format('m/d H:i:s');
                Artisan::call('boss:kill', $options);
                $maps = implode(', ', $bossMaps[$list[$name]]);
                if (isset($allowGroupIds[$groupId])) {
                    $message = '';
                    //                    $message    = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    //                    $message    .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                    $message = '' . $displayUrl . $this->breakLine;
                    $message .= '沒付費群組即無法使用' . $this->breakLine;
                    $message .= '歡迎加入賴群討論' . $this->breakLine;
                    $message .= '請蝦皮賣場購買序號後提供賴群編號開通使用' . $this->breakLine;
                    $message .= '或者私訊作者購買 line id: ifwelove' . $this->breakLine;
                    $message .= '您的群組編號：' . $groupId . $this->breakLine;
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
                if (isset($allowGroupIds[$groupId])) {
                    $message = '感謝付費 使用期限：' . $allowGroupIds[$groupId] . $this->breakLine;
                    $message .= '您的群組編號：' . $groupId . $this->breakLine;
                } else {
                    $message = '' . $displayUrl . $this->breakLine;
                    $message .= '沒付費群組即無法使用' . $this->breakLine;
                    $message .= '歡迎加入賴群討論' . $this->breakLine;
                    $message .= '請蝦皮賣場購買序號後提供賴群編號開通使用' . $this->breakLine;
                    $message .= '或者私訊作者購買 line id: ifwelove' . $this->breakLine;
                    $message .= '您的群組編號：' . $groupId . $this->breakLine;
                    $this->bot->replyText($replyToken, $message);
                    exit;

                }
                $this->bot->replyText($replyToken, $message);

                break;

        }
    }

}
