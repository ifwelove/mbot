<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Telegram;

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

        $tokens = config('monitor-token');
        try {
            $fieldsToHSet = [];
            foreach ($tokens as $token => $name) {
                // 获取所有 mac 地址
                $macAddresses = Redis::sMembers("token:$token:machines");
                if (empty($macAddresses)) {
                    continue; // 如果没有 MAC 地址，跳过
                }

                // 生成所有需要获取的键
                $keys = array_map(function ($mac) use ($token) {
                    return "token:$token:mac:$mac";
                }, $macAddresses);

                // 使用 Pipeline 批量获取所有机器数据
                $machines = Redis::pipeline(function ($pipe) use ($keys) {
                    foreach ($keys as $key) {
                        $pipe->hGetAll($key);
                    }
                });

                // 将结果与 MAC 地址对应
                $result = [];
                foreach ($macAddresses as $index => $mac) {
                    $result[$mac] = $machines[$index]; // 每个 MAC 对应的机器数据
                }

                // 此时 $result 包含所有 MAC 地址及其数据
                // 你可以根据需要进一步处理 $result
                foreach ($result as $mac => $machine) {
                    // 在这里对每台机器的数据进行处理
//                    dd($machine);
//                    Log::info("MAC: $mac, Data: " . json_encode($machine));
//                }
//                $macAddresses = Redis::sMembers("token:$token:machines");
//                foreach ($macAddresses as $mac) {
                    $key         = "token:$token:mac:$mac";
//                    $fieldsToHSet = [];
//                    $machine     = Redis::hGetAll($key);
//                    dump($machine);
                    $lastUpdated = $machine['last_updated'] ?? 0;

                    //@todo 待測試穩定
//                    $status = isset($machine['status']) ? $machine['status'] : '';
//                    if (now()->timestamp - $lastUpdated > 3600 || $status == 'pc_not_open') {
//                        if ($machine['status'] != 'pc_not_open') {
//                            Redis::hSet($key, 'status', 'pc_not_open');
//                        }
                    if (isset($machine['crash_alert_total'])) {
                        $crash_alert_total = (int) $machine['crash_alert_total'] + 1;
                    } else {
                        $crash_alert_total = 1;
                    }

//                    dump($crash_alert_total);
                    //crash_alert_total 三次了不會再通知, 但沒有點選清除, 但後續電腦正常後, 晚上又當機了但因為沒有清除所以不會通知, 所以要有一個機制當電腦正常後 crash_alert_total 要 reset
                    if (now()->timestamp - $lastUpdated > 3600 && $crash_alert_total <= 3) {
//                        Redis::hSet($key, 'crash_alert_total', (string) $crash_alert_total);
                        $fieldsToHSet[] = [
                            'key'   => $key,
                            'field' => 'crash_alert_total',
                            'value' => (string) $crash_alert_total
                        ];
                        $breakLine = "\n";
                        $message   = $breakLine;
                        $message   .= sprintf('自訂代號 : %s%s', isset($machine['pc_name']) ? $machine['pc_name'] : '', $breakLine);
                                            $message .= sprintf('電腦資訊 : %s%s', isset($machine['pc_info']) ? $machine['pc_info'] : '', $breakLine);
                        $message .= sprintf('大尾狀態 : %s%s', '當機, 半小時無訊號', $breakLine);
                        $message .= sprintf('模擬器數量 : %s/%s', $machine['dnplayer_running'], $machine['dnplayer']);
                        $message .= sprintf('如已經處理請至網頁點選重置訊號 : https://lbs.a5963745.workers.dev/pro/%s', $token);
//                        $message .= sprintf('已經處理點選清除通知 : https://lbs.a5963745.workers.dev/delete-machine?token=%s&mac=%s', $token, $mac);
                        Telegram::sendAlertMessage($token, $message);

                    } else if (now()->timestamp - $lastUpdated <= 3600) {
//                        Redis::hSet($key, 'crash_alert_total', '1');
                        $fieldsToHSet[] = [
                            'key'   => $key,
                            'field' => 'crash_alert_total',
                            'value' => '1'
                        ];
                    }

//                    Redis::pipeline(function ($pipe) use ($fieldsToHSet) {
//                        // 針對所有要 hSet 的欄位
//                        foreach ($fieldsToHSet as $item) {
//                            $pipe->hSet($item['key'], $item['field'], $item['value']);
//                        }
//                    });
                }
            }
            Redis::pipeline(function ($pipe) use ($fieldsToHSet) {
                // 針對所有要 hSet 的欄位
                foreach ($fieldsToHSet as $item) {
                    $pipe->hSet($item['key'], $item['field'], $item['value']);
                }
            });
        } catch (\Exception $exception) {
            Telegram::sendToLineOwner(json_encode(['token'  => $token, 'message' => $exception->getMessage()]));
        }
    }
}
