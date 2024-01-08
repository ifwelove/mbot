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

    private function getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer)
    {
        $breakLine = "\n";
        $message   = $breakLine;
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
        $client     = new Client();
        $headers    = [
            'Authorization' => sprintf('Bearer %s', $owen_token),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $options    = [
            'form_params' => [
                //                'message' => $message
                //                    'message' => $request->post('pc_name')
                'message' => json_encode($request->all())
            ]
        ];
        $response   = $client->request('POST', 'https://notify-api.line.me/api/notify', [
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
        $m_info           = $request->post('m_info');
        $alert_status     = $request->post('alert_status');
        $alert_type       = $request->post('alert_type');
        $mac              = $request->post('mac');
        $dnplayer         = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);

        $message = $this->getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer);


        try {
            $tokens    = $this->getTokens();
            $maxMacs   = $tokens[$token]['amount'];
            $macSetKey = "token:$token:machines";
            if (! Redis::sIsMember($macSetKey, $mac)) {
                $macCount = Redis::scard($macSetKey);
                if ($macCount >= $maxMacs) {
                    return response(sprintf('電腦台數限制 %s 已滿請聯繫作者', $maxMacs), 200)->header('Content-Type', 'text/plain');
                }
            }

            $currentDay  = date('w'); // 獲取當前星期，其中 0（表示週日）到 6（表示週六）
            $currentTime = date('H:i'); // 獲取當前時間（24小時制）

            if (! ($currentDay == 3 && $currentTime >= '04:30' && $currentTime <= '11:30')) {
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
//                'm_info'           => $m_info,
                'last_updated'     => now()->timestamp
            ];
            $client   = new Client();
            $headers  = [
                'Authorization' => sprintf('Bearer %s', '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75'),
                'Content-Type'  => 'application/x-www-form-urlencoded'
            ];
            $options  = [
                'form_params' => [
                    'message' => ['key' => $key, 'value' => json_encode($value)]
                ]
            ];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers'     => $headers,
                'form_params' => $options['form_params']
            ]);

            Redis::hSet($key, 'pc_name', $pc_name);
            Redis::hSet($key, 'pc_info', $pc_info);
            Redis::hSet($key, 'status', $alert_status);
            Redis::hSet($key, 'm_info', $m_info);
            Redis::hSet($key, 'dnplayer_running', $dnplayer_running);
            Redis::hSet($key, 'dnplayer', $dnplayer);
            Redis::hSet($key, 'last_updated', now()->timestamp);

//            Redis::hMSet($key, $value);
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


        //        return response($value, 200)->header('Content-Type', 'application/json');
        return response('', 200)->header('Content-Type', 'text/plain');
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
        $pc_message = $request->post('message');
        $pc_name    = $request->post('pc_name');
        $pc_info    = $request->post('pc_info');
        //        $m_info          = $request->post('m_info', []);
        $alert_status     = $request->post('alert_status');
        $alert_type       = $request->post('alert_type');
        $mac              = $request->post('mac');
        $dnplayer         = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);

        $message = $this->getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer);


        try {
            $tokens    = $this->getTokens();
            $maxMacs   = $tokens[$token]['amount'];
            $macSetKey = "token:$token:machines";
            if (! Redis::sIsMember($macSetKey, $mac)) {
                $macCount = Redis::scard($macSetKey);
                if ($macCount >= $maxMacs) {
                    return response(sprintf('電腦台數限制 %s 已滿請聯繫作者', $maxMacs), 200)->header('Content-Type', 'text/plain');
                }
            }

            $currentDay  = date('w'); // 獲取當前星期，其中 0（表示週日）到 6（表示週六）
            $currentTime = date('H:i'); // 獲取當前時間（24小時制）

            if (! ($currentDay == 3 && $currentTime >= '04:30' && $currentTime <= '11:30')) {
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

            $key = "token:$token:mac:$mac";
            //            $value = [
            //                'pc_name'          => $pc_name,
            //                'pc_info'          => $pc_info,
            //                'status'           => $alert_status,
            //                'dnplayer_running' => $dnplayer_running,
            //                'dnplayer'         => $dnplayer,
            ////                'm_info'           => $m_info,
            //                'last_updated'     => now()->timestamp
            //            ];
            Redis::hSet($key, 'pc_name', $pc_name);
            Redis::hSet($key, 'pc_info', $pc_info);
            Redis::hSet($key, 'status', $alert_status);
            Redis::hSet($key, 'dnplayer_running', $dnplayer_running);
            Redis::hSet($key, 'dnplayer', $dnplayer);
            Redis::hSet($key, 'last_updated', now()->timestamp);
            //            Redis::hMSet($key, $value);
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

    public function shareApply(Request $request)
    {
        $tokens = $this->getTokens();
        $token = $request->post('token');
        if (isset($tokens[$token])) {
            dump('申請已通過, 通過後可在下方連結看到專屬網頁');
            dd('https://mbot-3-ac8b63fd9692.herokuapp.com/machines/' . $token);
        }
        $client   = new Client();
        $headers  = [
            'Authorization' => sprintf('Bearer %s', '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75'),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $options  = [
            'form_params' => [
                'message' => $token
            ]
        ];
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers'     => $headers,
            'form_params' => $options['form_params']
        ]);
        dump($token);
        dump('審核申請中, 通過後可在下方連結看到專屬網頁');
        dump('https://mbot-3-ac8b63fd9692.herokuapp.com/machines/' . $token);
    }

    public function shareToken()
    {
        return view('share');

    }
    public function monitor2()
    {
        $a = '{"version": "2024-01-01 23:00:00", "token": "M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB", "api_token": "very60001", "alert_type": "all", "cpu": "BFEBFBFF000306F2", "mac": "22:35:4D:08:03:29", "pc_name": "\u53f0\u5317168", "pc_info": "DESKTOP-1S40PNN\\taipei666", "alert_status": "success", "dnplayer": "30", "dnplayer_running": 30, "message": "Process is running PC Info DESKTOP-1S40PNN\\taipei666", "m_info": "eyJtZXJnZSI6IHsiXHU1OTI3XHU1NzMwXHU0ZTRiXHU3OTVlIjogMTQ3NTEsICJcdTY3MmFcdTk4NmZcdTc5M2FcdTRmM2FcdTY3MGRcdTU2NjgiOiAwfSwgImNhcmQiOiAiIiwgInJvd3MiOiBbWyIiLCAiMSIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiMzA0IiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICIxNSIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDg2KSIsICIwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjExNC4zNy4xNDQuMTMwIl0sIFsiIiwgIjIiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjIwNyIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiOCIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDg2KSIsICIwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjYxLjIyOC4xNzMuMTI2Il0sIFsiIiwgIjMiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjY4OSIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiMTEiLCAiMjAwIiwgIjAiLCAiMC4wMDAwJSg4NikiLCAiMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICIzNi4yMjYuMTQ2LjUzIl0sIFsiIiwgIjQiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjcxOSIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiOCIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDg2KSIsICItMTAsMDAwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjM2LjIyNi4xMzUuMjA2Il0sIFsiIiwgIjUiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjIxMCIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiMTIiLCAiMjAwIiwgIjAiLCAiMC4wMDAwJSg4NSkiLCAiMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICI2MS4yMjguMTY3LjI0NCJdLCBbIiIsICI2IiwgIlx1NWRlNVx1NTE3N1x1OTU4Ylx1NTljYiIsICI1MSIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiOCIsICIxOTQiLCAiMCIsICIwLjAwMDIlKDg2KSIsICItNDAsMDAwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjYxLjIyOC4xNjAuMjUwIl0sIFsiIiwgIjciLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjczIiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICIxMyIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDgyKSIsICIwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjExNC4zNy4xMDMuMjIzIl0sIFsiIiwgIjgiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjgzNiIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiOCIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDg2KSIsICIwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjEyNS4yMjkuMjEwLjI0MCJdLCBbIiIsICI5IiwgIlx1NWRlNVx1NTE3N1x1OTU4Ylx1NTljYiIsICI0ODciLCAiXHU1OTI3XHU1NzMwXHU0ZTRiXHU3OTVlIiwgIjExIiwgIjk3ODA2IiwgIjAiLCAiMC4wMDAwJSg4NykiLCAiMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICIxMTQuMzcuMTQ0LjEzMCJdLCBbIiIsICIxMCIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiNjExIiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICIxMCIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDg1KSIsICIwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjYxLjIyOC4xNzMuMTI2Il0sIFsiIiwgIjExIiwgIlx1NWRlNVx1NTE3N1x1OTU4Ylx1NTljYiIsICIyOTEiLCAiXHU1OTI3XHU1NzMwXHU0ZTRiXHU3OTVlIiwgIjEzIiwgIjIwMCIsICIwIiwgIjAuMDAwMCUoODYpIiwgIi00MCwwMDAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiMzYuMjI2LjE0Ni41MyJdLCBbIiIsICIxMiIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiNjg2OSIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiMzAiLCAiMjAwIiwgIjAiLCAiMC4wMDAwJSg4NikiLCAiMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICIzNi4yMjYuMTM1LjIwNiJdLCBbIiIsICIxMyIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiODciLCAiXHU1OTI3XHU1NzMwXHU0ZTRiXHU3OTVlIiwgIjEzIiwgIjI2ODAiLCAiMCIsICIwLjAwMDAlKDg2KSIsICIwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjYxLjIyOC4xNjcuMjQ0Il0sIFsiIiwgIjE0IiwgIlx1NWRlNVx1NTE3N1x1OTU4Ylx1NTljYiIsICIzNTEiLCAiXHU1OTI3XHU1NzMwXHU0ZTRiXHU3OTVlIiwgIjEzIiwgIjIwMCIsICIwIiwgIjAuMDAwMCUoODYpIiwgIjAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiNjEuMjI4LjE2MC4yNTAiXSwgWyIiLCAiMTUiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjU2OSIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiMTAiLCAiMTgyIiwgIjAiLCAiMC4wMDA3JSg4NikiLCAiLTQwLDAwMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICIxMTQuMzcuMTAzLjIyMyJdLCBbIiIsICIxNiIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiMjE1IiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICI5IiwgIjE5NiIsICIwIiwgIjAuMDAwNiUoODEpIiwgIi00MCwwMDAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiMTI1LjIyOS4yMTAuMjQwIl0sIFsiIiwgIjE3IiwgIlx1NWRlNVx1NTE3N1x1OTU4Ylx1NTljYiIsICI2NSIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiOCIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDgyKSIsICItMjAsMDAwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjExNC4zNy4xNDQuMTMwIl0sIFsiIiwgIjE4IiwgIlx1NWRlNVx1NTE3N1x1OTU4Ylx1NTljYiIsICIyMDgiLCAiXHU1OTI3XHU1NzMwXHU0ZTRiXHU3OTVlIiwgIjgiLCAiMTkxIiwgIjAiLCAiMC4wMDEyJSg4MykiLCAiLTQwLDAwMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICI2MS4yMjguMTczLjEyNiJdLCBbIiIsICIxOSIsICJcdTkwNGFcdTYyMzJcdTU3ZjdcdTg4NGMiLCAiMTQzIiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICIyMSIsICI4MzQzNSIsICIwIiwgIjAuMDAwMCUoODcpIiwgIjAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiMzYuMjI2LjE0Ni41MyJdLCBbIiIsICIyMCIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiMjA4IiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICIxMiIsICIyMDAiLCAiMCIsICIwLjAwMDAlKDApIiwgIjAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiMzYuMjI2LjEzNS4yMDYiXSwgWyIiLCAiMjEiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjIwNyIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiMTYiLCAiMjAwIiwgIjAiLCAiMC4wMDAwJSg4MykiLCAiLTIwLDAwMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICI2MS4yMjguMTY3LjI0NCJdLCBbIiIsICIyMiIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiMjMxIiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICI5IiwgIjIwMCIsICIwIiwgIjAuMDAwMCUoODIpIiwgIi0yMCwwMDAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiNjEuMjI4LjE2MC4yNTAiXSwgWyIiLCAiMjMiLCAiXHU1ZGU1XHU1MTc3XHU5NThiXHU1OWNiIiwgIjc2MyIsICJcdTU5MjdcdTU3MzBcdTRlNGJcdTc5NWUiLCAiMTYiLCAiMTkyIiwgIjAiLCAiMC4wMDEzJSg4MikiLCAiLTQwLDAwMCIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICIxMTQuMzcuMTAzLjIyMyJdLCBbIiIsICIyNCIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiMTY2IiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICIxOSIsICIxODYiLCAiMCIsICIwLjAwMTklKDgzKSIsICItNDAsMDAwIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIjI2XHU1OTI5IDJcdTVjMGY/IiwgIjEyNS4yMjkuMjEwLjI0MCJdLCBbIiIsICIyNSIsICJcdTVkZTVcdTUxNzdcdTk1OGJcdTU5Y2IiLCAiMTkxIiwgIlx1NTkyN1x1NTczMFx1NGU0Ylx1Nzk1ZSIsICI3IiwgIjIwMCIsICIwIiwgIjAuMDAwMCUoODMpIiwgIjAiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiMjZcdTU5MjkgMlx1NWMwZj8iLCAiMTE0LjM3LjE0NC4xMzAiXSwgWyIiLCAiMjYiLCAiIiwgIiIsICIiLCAiIiwgIiIsICIiLCAiIiwgIiIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIyNlx1NTkyOSAyXHU1YzBmPyIsICIiXSwgWyIiLCAiMjciLCAiIiwgIiIsICIiLCAiIiwgIiIsICIiLCAiIiwgIiIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIiLCAiIl0sIFsiIiwgIjI4IiwgIiIsICIiLCAiIiwgIiIsICIiLCAiIiwgIiIsICIiLCAiRzEyMzExWUs3STlPUDA2Q01NTyIsICIyMDIxMTRPRUs3MUNETzZaMDYiLCAiIiwgIiJdLCBbIiIsICIyOSIsICIiLCAiIiwgIiIsICIiLCAiIiwgIiIsICIiLCAiIiwgIkcxMjMxMVlLN0k5T1AwNkNNTU8iLCAiMjAyMTE0T0VLNzFDRE82WjA2IiwgIiIsICIiXSwgWyIiLCAiMzAiLCAiIiwgIiIsICIiLCAiIiwgIiIsICIiLCAiIiwgIiIsICJHMTIzMTFZSzdJOU9QMDZDTU1PIiwgIjIwMjExNE9FSzcxQ0RPNlowNiIsICIiLCAiIl1dfQ=="}';
        dump($a);
        $bb = json_decode(($a), true);
        //        $bb['info'];
        $bb = json_decode(base64_decode($bb['m_info']), true);
        dump(time());
        dd($bb['merge']);
    }

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

    public function showToken($token)
    {
        $tokens = $this->getTokens();
        if (! isset($tokens[$token])) {
            dd('not found token');
        }

        $macAddresses           = Redis::sMembers("token:$token:machines");
        foreach ($macAddresses as $mac) {
            $key     = "token:$token:mac:$mac";
            $machine = Redis::hGetAll($key);
            dump($machine);
        }
    }

    public function showMachines($token)
    {
        $tokens = $this->getTokens();
        if (! isset($tokens[$token])) {
            $user = [
                'name'   => '',
                'date'   => '未申請使用',
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
            $key              = "token:$token:mac:$mac";
            $machine          = Redis::hGetAll($key);

            //@todo 可刪除 搭配 command
            $lastUpdated = $machine['last_updated'] ?? 0;
            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

            $pc_name          = isset($machine['pc_name']) ? $machine['pc_name'] : '';
            $dnplayer         = isset($machine['dnplayer']) ? $machine['dnplayer'] : 0;
            $dnplayer_running = isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0;


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
            if (! isset($machine['data']['last_updated'])) {
                $machines[$index]['data']['last_updated'] = '';
            } else {
                $machines[$index]['data']['last_updated'] = date('Y-m-d H:i:s', $machine['data']['last_updated']);
            }
            $machines_total++;
        }

        return view('machines', [
                //                'macCount' => $macCount,
                'user'                   => $user,
                'machines'               => $machines,
                'token'                  => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total'         => $dnplayer_total,
                'machines_total'         => $machines_total
            ]);
        //        return response()->json(['machines' => $machines]);
    }

    public function showDemo($token)
    {
        if ($token !== 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
            dd('功能尚未開放, 僅供展示');
        }
        $tokens = $this->getTokens();
        if (! isset($tokens[$token])) {
            $user = [
                'name'   => '',
                'date'   => '未申請使用',
                'amount' => '0',
            ];
        } else {
            $user = $tokens[$token];
        }


        $dnplayer_running_total = 0;
        $dnplayer_total         = 0;
        $macAddresses           = Redis::sMembers("token:$token:machines");
        $machines               = [];
        $m_info                 = [
            'rows'  => [],
            'card'  => '',
            'merge' => [],
        ];
        $merges = [];
        foreach ($macAddresses as $mac) {
            $key         = "token:$token:mac:$mac";
            $machine     = Redis::hGetAll($key);
            $lastUpdated = $machine['last_updated'] ?? 0;
            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

            $merge   = [];
            $card    = '';
            $pc_name = isset($machine['pc_name']) ? $machine['pc_name'] : '';
            if (isset($machine['m_info']) && $machine['m_info'] != '' && ! is_null($machine['m_info'])) {
                $m_info = json_decode(base64_decode($machine['m_info']), true);
                if (isset($m_info['merge'])) {
                    $merge = $m_info['merge'];
                }
                if (isset($m_info['card'])) {
                    $card = $m_info['card'];
                }
            }
            $dnplayer         = isset($machine['dnplayer']) ? $machine['dnplayer'] : 0;
            $dnplayer_running = isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0;

            foreach ($merge as $merge_sub => $merge_sub_total) {
                if (!isset($merges[$merge_sub])) {
                    $merges[$merge_sub] = $merge_sub_total;
                } else {
                    $merges[$merge_sub] = $merges[$merge_sub] + $merge_sub_total;
                }
            }
            $machines[]             = [
                'mac'              => $mac,
                'pc_name'          => $pc_name,
                'merge'            => $merge,
                'card'             => $card,
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
            if (! isset($machine['data']['last_updated'])) {
                $machines[$index]['data']['last_updated'] = '';
            } else {
                $machines[$index]['data']['last_updated'] = date('Y-m-d H:i:s', $machine['data']['last_updated']);
            }
            $machines_total++;
        }

        return view('machines2', [
                //                'macCount' => $macCount,
                'user'                   => $user,
                'machines'               => $machines,
                'token'                  => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total'         => $dnplayer_total,
                'machines_total'         => $machines_total,
                'merges'         => $merges,
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
