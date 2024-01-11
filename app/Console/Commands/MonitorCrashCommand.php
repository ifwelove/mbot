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
                $macAddresses = Redis::sMembers("token:$token:machines");
                foreach ($macAddresses as $mac) {
                    $key         = "token:$token:mac:$mac";
                    $machine     = Redis::hGetAll($key);
                    $lastUpdated = $machine['last_updated'] ?? 0;

                    //@todo 待測試穩定
//                    $status = isset($machine['status']) ? $machine['status'] : '';
//                    if (now()->timestamp - $lastUpdated > 1800 || $status == 'pc_not_open') {
//                        if ($machine['status'] != 'pc_not_open') {
//                            Redis::hSet($key, 'status', 'pc_not_open');
//                        }
                    if (isset($machine['crash_alert_total'])) {
                        $crash_alert_total = (int) $machine['crash_alert_total'] + 1;
                    } else {
                        $crash_alert_total = 1;
                    }
                    if (now()->timestamp - $lastUpdated > 1800 && $crash_alert_total <= 3) {
                        Redis::hSet($key, 'crash_alert_total', (string) $crash_alert_total);
                        $breakLine = "\n";
                        $message   = $breakLine;
                        $message   .= sprintf('自訂代號 : %s%s', isset($machine['pc_name']) ? $machine['pc_name'] : '', $breakLine);
                                            $message .= sprintf('電腦資訊 : %s%s', isset($machine['pc_info']) ? $machine['pc_info'] : '', $breakLine);
                        $message .= sprintf('大尾狀態 : %s%s', '當機, 半小時無訊號', $breakLine);
                        $message .= sprintf('模擬器數量 : %s/%s', $machine['dnplayer_running'], $machine['dnplayer']);
                        $message .= sprintf('已經處理點選清除通知 : https://mbot-3-ac8b63fd9692.herokuapp.com/delete-machine?token=%s&mac=%s', $token, $mac);

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
                    'message' => json_encode(['token'  => $token, 'message' => $exception->getMessage(), 'data' => $machine])
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);
        }
    }
}
