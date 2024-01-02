<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AlertController extends Controller
{

    public function __construct()
    {
    }

    private function getTokens()
    {
        $tokens = [
            'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB' => 'very6', //本人
            'bWBWihKBoPyGbNN5Ht14TtBtfN0H9f7quS1fV7LCyU3' => 'test555',
            '1EW9dRJOANPRwZYvS0gZblhxGPZvJ9ZNEBdpLlvARUu' => '青蛙',
            'u64MrAsdoyRHXZMN1wThRo9NVniGTwGop6czMVjyqUC' => '真心不騙',
            'BwaD9GSKNCvUXanBptPoKe8vw09eqOawH0Pqdikcu6K' => '什麼啊',
            'x46LmjVIU5CUUfTQfcqfiaBsihhue5wpITDMpM6WTV6' => '桃聖潔',
            '6cPuC4LR52C78mI9OVGDdTce1dNmQIgTzn3iayAHpEo' => '小白',
        ];

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

    public function alert(Request $request)
    {
        $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
        $token = $request->post('token');
        $result = $this->checkAllowToken($token);
        if ($result === false) {
            $client = new Client();
            $headers = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $options = [
                'form_params' => [
                    //                'message' => $message
//                    'message' => $request->post('pc_name')
                    'message' => json_encode($request->all())
                ]];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers' => $headers,
                'form_params' => $options['form_params']
            ]);

            return response('token 未授權 無法進行推送到 line', 200)->header('Content-Type', 'text/plain');
        }
        $pc_message = $request->post('message');
        $pc_name = $request->post('pc_name');
        $pc_info = $request->post('pc_info');
        $alert_status = $request->post('alert_status');
        $alert_type = $request->post('alert_type');
        $mac = $request->post('mac');
        $dnplayer = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);
        $breakLine = "\n";
        $message = $breakLine;
        switch (1) {
            case ($alert_status === 'failed') :
                $message .= sprintf('自訂代號 : %s%s', $pc_name, $breakLine);
                $message .= sprintf('電腦資訊 : %s%s', $pc_info, $breakLine);
                $message .= sprintf('大尾狀態 : %s%s', '沒有回應', $breakLine);
                $message .= sprintf('模擬器數量 : %s/%s', $dnplayer_running, $dnplayer);
                break;
            case ($alert_status === 'plugin_not_open') :
                $message .= sprintf('自訂代號 : %s%s', $pc_name, $breakLine);
                $message .= sprintf('電腦資訊 : %s%s', $pc_info, $breakLine);
                $message .= sprintf('大尾狀態 : %s%s', '沒有執行', $breakLine);
                $message .= sprintf('模擬器數量 : %s/%s', $dnplayer_running, $dnplayer);
                break;
            case ($alert_status === 'success') :
                $message .= sprintf('自訂代號 : %s%s', $pc_name, $breakLine);
                $message .= sprintf('電腦資訊 : %s%s', $pc_info, $breakLine);
                $message .= sprintf('大尾狀態 : %s%s', '正常運作中', $breakLine);
                $message .= sprintf('模擬器數量 : %s/%s', $dnplayer_running, $dnplayer);
                break;
            default:
                $message .= $pc_message;
                break;
        }
        $client = new Client();
        $headers = [
            'Authorization' => sprintf('Bearer %s', $token),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $options = [
            'form_params' => [
                'message' => $message
            ]];
        try {
            if ($alert_type === 'all' && $alert_status === 'success') {
                $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                    'headers' => $headers,
                    'form_params' => $options['form_params']
                ]);
            }

            if ($alert_type === 'error' && in_array($alert_status, ['failed', 'plugin_not_open'])) {
                $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                    'headers' => $headers,
                    'form_params' => $options['form_params']
                ]);
            }
            $key = "token:$token:mac:$mac";
            $value = [
                'pc_name' => $pc_name,
                'status' => $alert_status,
                'dnplayer_running' => $dnplayer_running,
                'dnplayer' => $dnplayer,
                'last_updated' => now()->timestamp
            ];

            Redis::hMSet($key, $value);
            Redis::expire($key, 86400);
            Redis::sAdd("token:$token:machines", $mac);

        } catch (\Exception $e) {
            $client = new Client();
            $headers = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $options = [
                'form_params' => [
                    'message' => json_encode([$request->all(), $e->getMessage()])
                ]];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers' => $headers,
                'form_params' => $options['form_params']
            ]);
        }


        return response('呼叫 line notify 成功', 200)->header('Content-Type', 'text/plain');
    }

//    public function updateMachineStatus()
//    {
//        $keys = Redis::keys("token:*:mac:*");
//        foreach ($keys as $key) {
//            $machine = Redis::hGetAll($key);
//            $lastUpdated = $machine['last_updated'];
//
//            // 檢查是否超過一小時未更新
//            if (now()->timestamp - $lastUpdated > 3600) {
//                // 更新狀態為 'notopen'
//                Redis::hSet($key, 'status', 'notopen');
//            }
//        }
//    }

//    public function showMachines($token)
//    {
//        $macAddresses = Redis::sMembers("token:$token:machines");
//        $machines = [];
//
//        foreach ($macAddresses as $mac) {
//            $key = "token:$token:mac:$mac";
//            $machines[] = [
//                'mac' => $mac,
//                'data' => Redis::hGetAll($key)
//            ];
//        }
//
//        return response()->json(['machines' => $machines]);
//    }

    public function monitor()
    {
        $count = 0;
        $tokens = $this->getTokens();
        foreach ($tokens as $token => $name) {
            $macAddresses = Redis::sMembers("token:$token:machines");
            foreach ($macAddresses as $mac) {
                $count++;
            }
        }
        dd($count);
    }

    public function showMachines($token)
    {
        $macAddresses = Redis::sMembers("token:$token:machines");
        $machines = [];
        foreach ($macAddresses as $mac) {
            $key = "token:$token:mac:$mac";
            $machine = Redis::hGetAll($key);
            $lastUpdated = $machine['last_updated'] ?? 0;

            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

            $machines[] = [
                'mac' => $mac,
                'pc_name' => $machine['pc_name'],
                'dnplayer' => isset($machine['dnplayer']) ? $machine['dnplayer'] : 0,
                'dnplayer_running' => isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0,
                'data' => $machine
            ];
        }

        usort($machines, function ($a, $b) {
            return strcmp($a['pc_name'], $b['pc_name']);
        });

        foreach ($machines as $index => $machine) {
            $machines[$index]['data']['last_updated'] = date('Y-m-d H:i:s', $machine['data']['last_updated']);
        }

        return view('machines', ['machines' => $machines, 'token' => $token]);

//        return response()->json(['machines' => $machines]);
    }

    public function deleteMachine(Request $request)
    {
        $token = $request->input('token');
        $mac = $request->input('mac');
        $key = "token:$token:mac:$mac";

        Redis::del($key);
        Redis::sRem("token:$token:machines", $mac);

        return response()->json(['message' => 'Machine deleted successfully']);
    }


//    public function deleteSpecificTokenKeys(array $tokens)
//    {
//        foreach ($tokens as $token) {
//            $keysForToken = Redis::keys("token:$token:*");
//            foreach ($keysForToken as $key) {
//                Redis::del($key);
//            }
//        }
//
//        return response()->json(['message' => 'Specified token keys deleted successfully']);
//    }

    public function deleteTokens(array $tokens)
    {
        foreach ($tokens as $token) {
            // 获取该 token 下所有的 MAC 地址
            $macAddresses = Redis::sMembers("token:$token:machines");

            foreach ($macAddresses as $mac) {
                // 删除每个 MAC 地址的具体数据
                Redis::del("token:$token:mac:$mac");
            }

            // 删除跟踪该 token 下所有 MAC 地址的集合
            Redis::del("token:$token:machines");
        }

        return response()->json(['message' => 'Tokens deleted successfully']);
    }
}
