<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MonitorCrashCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:crash';

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

            foreach ($tokens as $token => $name) {
//                if ($token != 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
//                    continue;
//                }
                $macAddresses = Redis::sMembers("token:$token:machines");
                foreach ($macAddresses as $mac) {
                    $key         = "token:$token:mac:$mac";
                    $machine     = Redis::hGetAll($key);
                    $lastUpdated = $machine['last_updated'] ?? 0;

                    //@todo 待測試穩定
//                    $status = isset($machine['status']) ? $machine['status'] : '';
//                    if (now()->timestamp - $lastUpdated > 3600 || $status == 'pc_not_open') {
//                        if ($machine['status'] != 'pc_not_open') {
//                            Redis::hSet($key, 'status', 'pc_not_open');
//                        }
//                    dump($machine['pc_name']);
//                    dump($machine['crash_alert_total']);
                    if (isset($machine['crash_alert_total'])) {
                        $crash_alert_total = (int) $machine['crash_alert_total'] + 1;
                    } else {
                        $crash_alert_total = 1;
                    }

//                    dump($crash_alert_total);
                    //crash_alert_total 三次了不會再通知, 但沒有點選清除, 但後續電腦正常後, 晚上又當機了但因為沒有清除所以不會通知, 所以要有一個機制當電腦正常後 crash_alert_total 要 reset
                    if (now()->timestamp - $lastUpdated > 3600 && $crash_alert_total <= 3) {
                        Redis::hSet($key, 'crash_alert_total', (string) $crash_alert_total);
                        $breakLine = "\n";
                        $message   = $breakLine;
                        $message   .= sprintf('自訂代號 : %s%s', isset($machine['pc_name']) ? $machine['pc_name'] : '', $breakLine);
                                            $message .= sprintf('電腦資訊 : %s%s', isset($machine['pc_info']) ? $machine['pc_info'] : '', $breakLine);
                        $message .= sprintf('大尾狀態 : %s%s', '當機, 半小時無訊號', $breakLine);
                        $message .= sprintf('模擬器數量 : %s/%s', $machine['dnplayer_running'], $machine['dnplayer']);
                        $message .= sprintf('如已經處理請至網頁點選重置訊號 : https://mbot-3-ac8b63fd9692.herokuapp.com/pro/%s', $token);
//                        $message .= sprintf('已經處理點選清除通知 : https://mbot-3-ac8b63fd9692.herokuapp.com/delete-machine?token=%s&mac=%s', $token, $mac);

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
                    } else if (now()->timestamp - $lastUpdated <= 3600) {
                        Redis::hSet($key, 'crash_alert_total', '1');
                    }
                }
            }
        } catch (\Exception $exception) {
//            dump($exception->getMessage());
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB'),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    'message' => json_encode(['token'  => $token, 'message' => $exception->getMessage()])
//                    'message' => json_encode(['token'  => $token, 'message' => $exception->getMessage(), 'data' => $machine])
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);
        }
    }
}
