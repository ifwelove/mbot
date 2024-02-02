<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MonitorCardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:card';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //        $owen_token = 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB';
        //        $client   = new Client();
        //        $headers  = [
        //            'Authorization' => sprintf('Bearer %s', $owen_token),
        //            'Content-Type'  => 'application/x-www-form-urlencoded'
        //        ];
        //        $options  = [
        //            'form_params' => [
        //                'message' => 'test MonitorCrashCommand'
        //            ]
        //        ];
        //        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
        //            'headers'     => $headers,
        //            'form_params' => $options['form_params']
        //        ]);

        $tokens = config('monitor-token');
        try {
            $not_check_role_status = [
                '',
                '工具開始',
                '遊戲執行',
                '角色死亡',
            ];
            foreach ($tokens as $token => $name) {
//                if ($token != 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
//                    continue;
//                }
                $macAddresses = Redis::sMembers("token:$token:machines");
                foreach ($macAddresses as $mac) {
                    $key         = "token:$token:mac:$mac";
                    $machine     = Redis::hGetAll($key);
                    $rows   = [];
                    $role_gg   = 0;
                    if (isset($machine['m_info']) && $machine['m_info'] != '' && ! is_null($machine['m_info'])) {
                        $m_info = json_decode(base64_decode($machine['m_info']), true);
                        if (isset($m_info['card'])) {
                            $card = str_replace('?', '時', $m_info['card']);
                            if (preg_match('/(\d+)天\s*(\d+)小時/', $card, $matches)) {
                                // 使用當前時間，加上解析出來的天數和小時數
                                $days           = $matches[1];
                                $hours          = $matches[2];
                                $expirationTime = Carbon::now()
                                    ->addDays($days)
                                    ->addHours($hours);

                                if (isset($machine['card_alert_total'])) {
                                    $card_alert_total = (int) $machine['card_alert_total'] + 1;
                                } else {
                                    $card_alert_total = 1;
                                }
                                // 判斷是否需要發送通知

                                if ($expirationTime->lte(Carbon::now()
                                        ->addHours(1)) && $card_alert_total <= 3) {
//                                    dump($card_alert_total);
                                    //                                    echo "發送通知";
                                    Redis::hSet($key, 'card_alert_total', (string) $card_alert_total);
                                    $breakLine = "\n";
                                    $message   = $breakLine;
                                    $message   .= sprintf('自訂代號 : %s%s', isset($machine['pc_name']) ? $machine['pc_name'] : '', $breakLine);
                                    $message   .= sprintf('電腦資訊 : %s%s', isset($machine['pc_info']) ? $machine['pc_info'] : '', $breakLine);
                                    $message   .= sprintf('大尾狀態 : %s:%s%s', '卡號即將到期剩餘時間', $card, $breakLine);
                                    $message   .= sprintf('已經處理點選清除通知 : https://mbot-3-ac8b63fd9692.herokuapp.com/delete-machine?token=%s&mac=%s', $token, $mac);

                                    $client   = new Client();
                                    $headers  = [
                                        'Authorization' => sprintf('Bearer %s', $token),
                                        'Content-Type'  => 'application/x-www-form-urlencoded'
                                    ];
                                    $options  = [
                                        'form_params' => [
                                            'message' => $message
                                        ]
                                    ];
                                    $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                                        'headers'     => $headers,
                                        'form_params' => $options['form_params']
                                    ]);
                                } else {
                                    Redis::hSet($key, 'card_alert_total', '1');
                                    //                                    echo "不需要發送通知";
                                }
                            } else {
                                //@todo 卡號到期 ... etc
                                //                                echo "時間格式不正確";
                                continue;
                            }

                        }
                        if (isset($m_info['rows'])) {
                            $rows = $m_info['rows'];
                        }
                    }
                    foreach ($rows as $role) {
                        if(!in_array($role[2], $not_check_role_status)) {
                            $role_gg = 1;
                        }
                    }
                    if (isset($machine['role_gg_alert_total'])) {
                        $role_gg_alert_total = (int) $machine['role_gg_alert_total'] + 1;
                    } else {
                        $role_gg_alert_total = 1;
                    }
//                    角色死亡,
                    // 死亡結束工具, 結束工具
                    if ($role_gg === 1 && $card_alert_total <= 3) {
                        Redis::hSet($key, 'role_gg_alert_total', (string) $role_gg_alert_total);
                        $breakLine = "\n";
                        $message   = $breakLine;
                        $message   .= sprintf('自訂代號 : %s%s', isset($machine['pc_name']) ? $machine['pc_name'] : '', $breakLine);
                        $message   .= sprintf('電腦資訊 : %s%s', isset($machine['pc_info']) ? $machine['pc_info'] : '', $breakLine);
                        $message   .= sprintf('大尾狀態 : %s:%s', '發生工具結束, 死亡工具結束', $breakLine);
                        $message   .= sprintf('已經處理點選清除通知 : https://mbot-3-ac8b63fd9692.herokuapp.com/delete-machine?token=%s&mac=%s', $token, $mac);
                        dump($message);
                        $client   = new Client();
                        $headers  = [
                            'Authorization' => sprintf('Bearer %s', $token),
                            'Content-Type'  => 'application/x-www-form-urlencoded'
                        ];
                        $options  = [
                            'form_params' => [
                                'message' => $message
                            ]
                        ];
                        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                            'headers'     => $headers,
                            'form_params' => $options['form_params']
                        ]);
                    } else {
                        Redis::hSet($key, 'role_gg_alert_total', '1');
                    }
                }
            }
        } catch (\Exception $exception) {
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB'),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    'message' => json_encode([
                        'token'   => $token,
                        'message' => $exception->getMessage(),
                        'data'    => $machine
                    ])
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);
        }
    }
}
