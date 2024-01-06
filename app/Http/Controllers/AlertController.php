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
            'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB1' => [
                'name' => 'very62',
                'date' => '2025-01-01',
                'amount' => '10',
            ],
            'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB' => [
                'name' => 'very6',
                'date' => '2025-01-01',
                'amount' => '10',
            ],
            'bWBWihKBoPyGbNN5Ht14TtBtfN0H9f7quS1fV7LCyU3' => [
                'name' => 'test5555',
                'date' => '2025-01-01',
                'amount' => '50',
            ],
            '1EW9dRJOANPRwZYvS0gZblhxGPZvJ9ZNEBdpLlvARUu' => [
                'name' => '井底之蛙',
                'date' => '2025-01-01',
                'amount' => '50',
            ],
            'u64MrAsdoyRHXZMN1wThRo9NVniGTwGop6czMVjyqUC' => [
                'name' => '真心不騙',
                'date' => '2025-01-01',
                'amount' => '2',
            ],
            'BwaD9GSKNCvUXanBptPoKe8vw09eqOawH0Pqdikcu6K' => [
                'name' => '什麼啊',
                'date' => '2025-01-01',
                'amount' => '2',
            ],
            'x46LmjVIU5CUUfTQfcqfiaBsihhue5wpITDMpM6WTV6' => [
                'name' => '桃聖潔',
                'date' => '2025-01-01',
                'amount' => '15',
            ],
            '6cPuC4LR52C78mI9OVGDdTce1dNmQIgTzn3iayAHpEo' => [
                'name' => '小白',
                'date' => '2025-01-01',
                'amount' => '15',
            ],
            'ZdxVsVdWDNJzHFKZTgSNaWg3BhfOCwEJOybvM2Q98R9' => [
                'name' => '??',
                'date' => '2025-01-01',
                'amount' => '100',
            ],
            'iMPSeJqEf7WJLgTDPSnSHE3UwMDNHjL02eV0p5S42fM' => [
                'name' => '??',
                'date' => '2025-01-01',
                'amount' => '100',
            ],
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

    private function getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer)
    {
        $breakLine        = "\n";
        $message          = $breakLine;
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

        return $message;
    }

    public function heroku(Request $request)
    {
//        '7173297118557c83de0dffed03fadddce186044ebecce65aa9e1d576e365'
        $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
        $client   = new Client();
        $headers  = [
            'Authorization' => sprintf('Bearer %s', $owen_token),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $options  = [
            'form_params' => [
                //                'message' => $message
                //                    'message' => $request->post('pc_name')
                'message' => json_encode($request->all())
            ]
        ];
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers'     => $headers,
            'form_params' => $options['form_params']
        ]);

        return response();
    }

    public function alert2(Request $request)
    {
        $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
        $token      = $request->post('token');
        $result     = $this->checkAllowToken($token);
        if ($result === false) {
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    //                'message' => $message
                    //                    'message' => $request->post('pc_name')
                    'message' => json_encode($request->all())
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);

            return response('token 未授權 無法進行推送到 line', 200)->header('Content-Type', 'text/plain');
        }
        $pc_message       = $request->post('message');
        $pc_name          = $request->post('pc_name');
        $pc_info          = $request->post('pc_info');
        $m_info          = $request->post('m_info');
        $alert_status     = $request->post('alert_status');
        $alert_type       = $request->post('alert_type');
        $mac              = $request->post('mac');
        $dnplayer         = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);

        $message = $this->getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer);


        try {
            $tokens = $this->getTokens();
            $maxMacs = $tokens[$token]['amount'];
            $macSetKey = "token:$token:machines";
            if (!Redis::sIsMember($macSetKey, $mac)) {
                $macCount = Redis::scard($macSetKey);
                if ($macCount >= $maxMacs) {
                    return response(sprintf('電腦台數限制 %s 已滿請聯繫作者', $maxMacs), 200)->header('Content-Type', 'text/plain');
                }
            }

            $currentDay = date('w'); // 獲取當前星期，其中 0（表示週日）到 6（表示週六）
            $currentTime = date('H:i'); // 獲取當前時間（24小時制）

            if (!($currentDay == 3 && $currentTime >= '04:30' && $currentTime <= '11:30')) {
                $client  = new Client();
                $headers = [
                    'Authorization' => sprintf('Bearer %s', $token),
                    'Content-Type'  => 'application/x-www-form-urlencoded'
                ];
                $options = [
                    'form_params' => [
                        'message' => $message
                    ]
                ];

                if ($alert_type === 'all') {
                    $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                        'headers'     => $headers,
                        'form_params' => $options['form_params']
                    ]);
                }

                if ($alert_type === 'error' && in_array($alert_status, ['failed', 'plugin_not_open'])) {
                    $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                        'headers'     => $headers,
                        'form_params' => $options['form_params']
                    ]);
                }
            }
//            $m_info = json_decode(base64_decode($m_info), true);
            $key   = "token:$token:mac:$mac";
            $value = [
                'pc_name'          => $pc_name,
                'status'           => $alert_status,
                'dnplayer_running' => $dnplayer_running,
                'dnplayer'         => $dnplayer,
                'm_info'           => $m_info,
                'last_updated'     => now()->timestamp
            ];

            Redis::hMSet($key, $value);
            Redis::expire($key, 300);
            Redis::sAdd("token:$token:machines", $mac);

        } catch (\Exception $e) {
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    'message' => json_encode([$e->getMessage(), $request->all()])
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);
        }


//        return response($value, 200)->header('Content-Type', 'application/json');
        return response($value, 200)->header('Content-Type', 'text/plain');
    }

    public function alert(Request $request)
    {
        $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
        $token      = $request->post('token');
        $result     = $this->checkAllowToken($token);
        if ($result === false) {
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    //                'message' => $message
                    //                    'message' => $request->post('pc_name')
                    'message' => json_encode($request->all())
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);

            return response('token 未授權 無法進行推送到 line', 200)->header('Content-Type', 'text/plain');
        }
        $pc_message       = $request->post('message');
        $pc_name          = $request->post('pc_name');
        $pc_info          = $request->post('pc_info');
//        $m_info          = $request->post('m_info', []);
        $alert_status     = $request->post('alert_status');
        $alert_type       = $request->post('alert_type');
        $mac              = $request->post('mac');
        $dnplayer         = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);

        $message = $this->getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer);


        try {
            $tokens = $this->getTokens();
            $maxMacs = $tokens[$token]['amount'];
            $macSetKey = "token:$token:machines";
            if (!Redis::sIsMember($macSetKey, $mac)) {
                $macCount = Redis::scard($macSetKey);
                if ($macCount >= $maxMacs) {
                    return response(sprintf('電腦台數限制 %s 已滿請聯繫作者', $maxMacs), 200)->header('Content-Type', 'text/plain');
                }
            }

            $currentDay = date('w'); // 獲取當前星期，其中 0（表示週日）到 6（表示週六）
            $currentTime = date('H:i'); // 獲取當前時間（24小時制）

            if (!($currentDay == 3 && $currentTime >= '04:30' && $currentTime <= '11:30')) {
                $client  = new Client();
                $headers = [
                    'Authorization' => sprintf('Bearer %s', $token),
                    'Content-Type'  => 'application/x-www-form-urlencoded'
                ];
                $options = [
                    'form_params' => [
                        'message' => $message
                    ]
                ];

                if ($alert_type === 'all') {
                    $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                        'headers'     => $headers,
                        'form_params' => $options['form_params']
                    ]);
                }

                if ($alert_type === 'error' && in_array($alert_status, ['failed', 'plugin_not_open'])) {
                    $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                        'headers'     => $headers,
                        'form_params' => $options['form_params']
                    ]);
                }
            }

            $key   = "token:$token:mac:$mac";
            $value = [
                'pc_name'          => $pc_name,
                'status'           => $alert_status,
                'dnplayer_running' => $dnplayer_running,
                'dnplayer'         => $dnplayer,
//                'm_info'           => $m_info,
                'last_updated'     => now()->timestamp
            ];

            Redis::hMSet($key, $value);
            Redis::expire($key, 86400 * 2);
            Redis::sAdd("token:$token:machines", $mac);

        } catch (\Exception $e) {
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', $owen_token),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    'message' => json_encode([$e->getMessage(), $request->all()])
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
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
        $count  = 0;
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
        $tokens = $this->getTokens();
        if (!isset($tokens[$token])) {
            $user = [
                'name' => '',
                'date' => '未申請使用',
                'amount' => '0',
            ];
        } else {
            $user = $tokens[$token];
        }

//        $macCount = Redis::scard("token:$token:machines");

        $dnplayer_running_total = 0;
        $dnplayer_total         = 0;
        $macAddresses           = Redis::sMembers("token:$token:machines");
        $machines               = [];
        foreach ($macAddresses as $mac) {
            $key         = "token:$token:mac:$mac";
            $machine     = Redis::hGetAll($key);
            $lastUpdated = $machine['last_updated'] ?? 0;

            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

            $pc_name               = isset($machine['pc_name']) ? $machine['pc_name'] : '';
            $dnplayer               = isset($machine['dnplayer']) ? $machine['dnplayer'] : 0;
            $dnplayer_running       = isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0;
//            $m_info       = !empty($machine['m_info']) ? ($machine['m_info']) : [];
//            dump($machine);
//            $m_info       = !empty($machine['m_info']) ? json_decode($machine['m_info']) : [];
//            $groupedData = [];
//            foreach ($m_info as $item) {
//                $key = $item[1];
//                $value = (int) $item[0];
//                if (!isset($groupedData[$key])) {
//                    $groupedData[$key] = 0;
//                }
//                $groupedData[$key] += $value;
//            }


            $machines[]             = [
                'mac'              => $mac,
                'pc_name'          => $pc_name,
                'dnplayer'         => $dnplayer,
                'dnplayer_running' => $dnplayer_running,
//                'm_info'           => $groupedData,
                'data'             => $machine
            ];
            $dnplayer_running_total = $dnplayer_running_total + $dnplayer_running;
            $dnplayer_total         = $dnplayer_total + (int) $dnplayer;
        }

        usort($machines, function ($a, $b) {
            return strcmp($a['pc_name'], $b['pc_name']);
        });

        $machines_total = 0;
        foreach ($machines as $index => $machine) {
            if (!isset($machine['data']['last_updated'])) {
                $machines[$index]['data']['last_updated'] = '';
            } else {
                $machines[$index]['data']['last_updated'] = date('Y-m-d H:i:s', $machine['data']['last_updated']);
            }
            $machines_total++;
        }

        return view('machines',
            [
//                'macCount' => $macCount,
                'user' => $user,
                'machines' => $machines,
                'token' => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total' => $dnplayer_total,
                'machines_total' => $machines_total
            ]);
        //        return response()->json(['machines' => $machines]);
    }

    public function showMachines2($token)
    {
        $tokens = $this->getTokens();
        if (!isset($tokens[$token])) {
            $user = [
                'name' => '',
                'date' => '未申請使用',
                'amount' => '0',
            ];
        } else {
            $user = $tokens[$token];
        }

        //        $macCount = Redis::scard("token:$token:machines");

        $dnplayer_running_total = 0;
        $dnplayer_total         = 0;
        $macAddresses           = Redis::sMembers("token:$token:machines");
        $machines               = [];
        $m_info = [
            'rows' => [],
            'card' => '',
            'merge' => [],
        ];
        foreach ($macAddresses as $mac) {
            $key         = "token:$token:mac:$mac";
            $machine     = Redis::hGetAll($key);
            $lastUpdated = $machine['last_updated'] ?? 0;
//            dump((($machine)));
            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

            $pc_name               = isset($machine['pc_name']) ? $machine['pc_name'] : '';
            if ($machine['m_info'] != '' && !is_null($machine['m_info'])) {
                $m_info = json_decode(base64_decode($machine['m_info']), true);

                if(isset($m_info['merge'])){
                    $merge = $m_info['merge'];
                }
                if(isset($m_info['card'])){
                    $card = $m_info['card'];
                }
                dump($merge);
            } else {
                $merge = [];
                $card = '';
            }
            $dnplayer               = isset($machine['dnplayer']) ? $machine['dnplayer'] : 0;
            $dnplayer_running       = isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0;
            //            $m_info       = !empty($machine['m_info']) ? ($machine['m_info']) : [];
            //            dump($machine);
            //            $m_info       = !empty($machine['m_info']) ? json_decode($machine['m_info']) : [];
            //            $groupedData = [];
            //            foreach ($m_info as $item) {
            //                $key = $item[1];
            //                $value = (int) $item[0];
            //                if (!isset($groupedData[$key])) {
            //                    $groupedData[$key] = 0;
            //                }
            //                $groupedData[$key] += $value;
            //            }


            $machines[]             = [
                'mac'              => $mac,
                'pc_name'          => $pc_name,
                'merge'          => json_encode($merge),
                'card'          => $card,
                'dnplayer'         => $dnplayer,
                'dnplayer_running' => $dnplayer_running,
                //                'm_info'           => $groupedData,
                'data'             => $machine
            ];
            $dnplayer_running_total = $dnplayer_running_total + $dnplayer_running;
            $dnplayer_total         = $dnplayer_total + (int) $dnplayer;
        }

        usort($machines, function ($a, $b) {
            return strcmp($a['pc_name'], $b['pc_name']);
        });

        $machines_total = 0;
        foreach ($machines as $index => $machine) {
            if (!isset($machine['data']['last_updated'])) {
                $machines[$index]['data']['last_updated'] = '';
            } else {
                $machines[$index]['data']['last_updated'] = date('Y-m-d H:i:s', $machine['data']['last_updated']);
            }
            $machines_total++;
        }

        return view('machines2',
            [
                //                'macCount' => $macCount,
                'user' => $user,
                'machines' => $machines,
                'token' => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total' => $dnplayer_total,
                'machines_total' => $machines_total
            ]);
        //        return response()->json(['machines' => $machines]);
    }

    public function deleteMachine(Request $request)
    {
        $token = $request->input('token');
        $mac   = $request->input('mac');
        $key   = "token:$token:mac:$mac";

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
