<?php

namespace App\Http\Controllers;

use App\Http\FileService;
use Carbon\Carbon;
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

    private function getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer, $token)
    {
        //@todo $dnplayer_running 一直是0 可以 alert
        $breakLine = "\n";
        $message   = $breakLine;
        switch (1) {
            case ($alert_status === 'failed') :
                $message .= sprintf('自訂代號 : %s%s', $pc_name, $breakLine);
                $message .= sprintf('電腦資訊 : %s%s', $pc_info, $breakLine);
                $message .= sprintf('大尾狀態 : %s%s', '沒有回應', $breakLine);
                $message .= sprintf('模擬器數量 : %s/%s%s', $dnplayer_running, $dnplayer, $breakLine);
                $message .= sprintf('網頁版 : %s/%s', 'https://mbot-3-ac8b63fd9692.herokuapp.com/pro', $token);
                break;
            case ($alert_status === 'plugin_not_open') :
                $message .= sprintf('自訂代號 : %s%s', $pc_name, $breakLine);
                $message .= sprintf('電腦資訊 : %s%s', $pc_info, $breakLine);
                $message .= sprintf('大尾狀態 : %s%s', '沒有執行', $breakLine);
                $message .= sprintf('模擬器數量 : %s/%s%s', $dnplayer_running, $dnplayer, $breakLine);
                $message .= sprintf('網頁版 : %s/%s', 'https://mbot-3-ac8b63fd9692.herokuapp.com/pro', $token);
                break;
            case ($alert_status === 'success') :
                $message .= sprintf('自訂代號 : %s%s', $pc_name, $breakLine);
                $message .= sprintf('電腦資訊 : %s%s', $pc_info, $breakLine);
                $message .= sprintf('大尾狀態 : %s%s', '正常運作中', $breakLine);
                $message .= sprintf('模擬器數量 : %s/%s%s', $dnplayer_running, $dnplayer, $breakLine);
                $message .= sprintf('網頁版 : %s/%s', 'https://mbot-3-ac8b63fd9692.herokuapp.com/pro', $token);
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

    public function execOlinTap(Request $request)
    {
        $token      = $request->post('token');
        $result     = $this->checkAllowToken($token);
        $owen_token = '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75';
        $client   = new Client();
        $headers  = [
            'Authorization' => sprintf('Bearer %s', $owen_token),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $options  = [
            'form_params' => [
                'message' => 'checkOlinTap:' . $token
            ]
        ];
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers'     => $headers,
            'form_params' => $options['form_params']
        ]);

        return response('ok', 200)->header('Content-Type', 'text/plain');
    }
    public function checkOlinToken(Request $request)
    {
        $token      = $request->post('token');
        $result     = $this->checkAllowToken($token);
        if ($result === false) {
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

            return response('token 未授權 請聯繫作者開通Line ID: ifwelove', 200)->header('Content-Type', 'text/plain');
        } else {
            return response('ok', 200)->header('Content-Type', 'text/plain');
        }
    }

    //heroku
    public function checkToken(Request $request)
    {
        $token      = $request->post('token');
//        if ($token != 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
//            $client   = new Client();
//            $headers  = [
//                'Authorization' => sprintf('Bearer %s', '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75'),
//                'Content-Type'  => 'application/x-www-form-urlencoded'
//            ];
//            $options  = [
//                'form_params' => [
//                    'message' => $token . ' 使用自動更新'
//                ]
//            ];
//            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
//                'headers'     => $headers,
//                'form_params' => $options['form_params']
//            ]);
//        }



        $result     = $this->checkAllowToken($token);
        if ($result === false) {
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

            return response('token 未授權 請聯繫作者開通Line ID: ifwelove', 200)->header('Content-Type', 'text/plain');
        } else {
            $fileService = resolve(FileService::class);
            $name = $fileService->getLatestFileName();

            return response(sprintf('token 授權成功 開始檢查大尾更新流程 最新雲端空間檔案最新版本為:%s', $name), 200)->header('Content-Type', 'text/plain');
        }
    }

    public function apkCheckToken(Request $request)
    {
        $token      = $request->post('token');
        $result     = $this->checkAllowToken($token);
        if ($result === false) {
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

            return response('token 未授權 請聯繫作者開通Line ID: ifwelove', 200)->header('Content-Type', 'text/plain');
        } else {
            $fileService = resolve(FileService::class);
            $name = $fileService->getApkLatestFileName();

            return response(sprintf('token 授權成功 開始檢查APK更新流程 最新雲端空間檔案最新版本為:%s', $name), 200)->header('Content-Type', 'text/plain');
        }
    }

    public function alert2(Request $request)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        // Send the response to the client
        response()->json()->send();
        // If you're using FastCGI, this will end the request/response cycle
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

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
        $alert_status     = $request->post('alert_status', 'success');
        $alert_type       = $request->post('alert_type', 'error');
        $mac              = $request->post('mac');
        if ($mac == '00:F1:F3:D0:2B:AE') {
            return response('token 未授權 無法進行推送到 line', 200)->header('Content-Type', 'text/plain');
        }
        $version              = $request->post('version');
        $dnplayer         = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);
        $pro_version = $request->post('pro_version', '');
        $bag_alert = $request->post('bag_alert', 'yes');
        $role_gg_alert = $request->post('role_gg_alert', 'yes');
        $dead_gg_alert = $request->post('dead_gg_alert', 'yes');

        $message = $this->getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer, $token);


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
//            $value = [
//                'pc_name'          => $pc_name,
//                'status'           => $alert_status,
//                'dnplayer_running' => $dnplayer_running,
//                'dnplayer'         => $dnplayer,
////                'm_info'           => $m_info,
//                'last_updated'     => now()->timestamp
//            ];
//            $client   = new Client();
//            $headers  = [
//                'Authorization' => sprintf('Bearer %s', '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75'),
//                'Content-Type'  => 'application/x-www-form-urlencoded'
//            ];
//            $options  = [
//                'form_params' => [
//                    'message' => json_encode(['key' => $key, 'value' => json_encode($value)])
//                ]
//            ];
//            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
//                'headers'     => $headers,
//                'form_params' => $options['form_params']
//            ]);
            if (! is_null($m_info)) {
                $machine     = Redis::hGetAll($key);
                $rows_old = [];
                if (!empty($machine['m_info'])) {
                    $m_info_old = json_decode(base64_decode($machine['m_info']), true);
                    if (isset($m_info_old['rows'])) {
                        $rows_old = $m_info_old['rows'];
                    }
                }
                $m_info = json_decode(base64_decode($m_info), true);
                if (isset($m_info['rows'])) {
                    foreach ($m_info['rows'] as $index => &$row) {
                        if (isset($rows_old[$index][4]) && $row[4] === '') {
                            $row[4] = $rows_old[$index][4]; // 使用旧数据
                        }
                    }
                    $m_info = base64_encode(json_encode($m_info));
                }
            }
            Redis::hSet($key, 'm_info', $m_info);
            Redis::hSet($key, 'pc_name', $pc_name);
            Redis::hSet($key, 'mac', $mac);
            Redis::hSet($key, 'version', $version);
            Redis::hSet($key, 'pc_info', $pc_info);
            Redis::hSet($key, 'status', $alert_status);
            Redis::hSet($key, 'dnplayer_running', $dnplayer_running);
            Redis::hSet($key, 'dnplayer', $dnplayer);
            Redis::hSet($key, 'bag_alert', $bag_alert);
            Redis::hSet($key, 'role_gg_alert', $role_gg_alert);
            Redis::hSet($key, 'dead_gg_alert', $dead_gg_alert);
            Redis::hSet($key, 'pro_version', $pro_version);
            Redis::hSet($key, 'last_updated', now()->timestamp);

//            Redis::hMSet($key, $value);
            Redis::expire($key, 86400 * 7);
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
        if ($mac == '00:F1:F3:D0:2B:AE') {
            return response('token 未授權 無法進行推送到 line', 200)->header('Content-Type', 'text/plain');
        }
        $dnplayer         = $request->post('dnplayer', 0);
        $dnplayer_running = $request->post('dnplayer_running', 0);

        $message = $this->getMessage($alert_status, $pc_message, $pc_name, $pc_info, $dnplayer_running, $dnplayer, $token);


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
//
//            if ($token === 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
//                $client   = new Client();
//                $headers  = [
//                    'Authorization' => sprintf('Bearer %s', '3r5FV6kWXEyBvqHPSjzToZTRiSWe5MsLNn4ZGnvWX75'),
//                    'Content-Type'  => 'application/x-www-form-urlencoded'
//                ];
//                $options  = [
//                    'form_params' => [
//                        'message' => json_encode(['key' => $key, 'value' => ($value)])
//                    ]
//                ];
//                $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
//                    'headers'     => $headers,
//                    'form_params' => $options['form_params']
//                ]);
//            }


            Redis::hSet($key, 'pc_name', $pc_name);
            Redis::hSet($key, 'pc_info', $pc_info);
            Redis::hSet($key, 'mac', $mac);
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
            dd('https://mbot-3-ac8b63fd9692.herokuapp.com/pro/' . $token);
        }
        $client   = new Client();
        $headers  = [
            'Authorization' => sprintf('Bearer %s', '5hcyGO935sKzRjF522X1UPPNnfL5QqYCMrLnB5M0KhE'),
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
        dump('https://mbot-3-ac8b63fd9692.herokuapp.com/pro/' . $token);
    }

    public function shareToken()
    {
        return view('share');

    }
    public function monitor2()
    {
    }

    public function monitor()
    {
        $totalCount = 0;
        $tokens = $this->getTokens();
        $macCounts = [];

        foreach ($tokens as $token => $name) {
            $macAddresses = Redis::sMembers("token:$token:machines");
            $macCount = count($macAddresses);
            if ($macCount <= 0) {
                continue;
            }
            $totalCount += $macCount;

            // 將這個 token 的 macAddresses 數量儲存起來
            $macCounts[$token] = $macCount;
        }

        // 顯示每個 token 的 macAddresses 數量
        dd($totalCount, $macCounts);
    }

    public function sendMessage(Request $request, $token)
    {
        $text = $request->input('text');
        $client   = new Client();
        $headers  = [
            'Authorization' => sprintf('Bearer %s', $token),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $options  = [
            'form_params' => [
                'message' => $text
            ]
        ];
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers'     => $headers,
            'form_params' => $options['form_params']
        ]);
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
        dd(123);
//dd(123);
        //        $macCount = Redis::scard("token:$token:machines");

        $dnplayer_running_total = 0;
        $dnplayer_total         = 0;
        $macAddresses           = Redis::sMembers("token:$token:machines");
        $machines               = [];
        foreach ($macAddresses as $mac) {
            dump($mac);
            $key              = "token:$token:mac:$mac";
            $machine          = Redis::hGetAll($key);
            dump($machine);
            //@todo 可刪除 搭配 command
            $lastUpdated = $machine['last_updated'] ?? 0;
//            if (now()->timestamp - $lastUpdated > 1800) {
//                Redis::hSet($key, 'status', 'pc_not_open');
//                Redis::hSet($key, 'dnplayer', 10000);
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
//            }

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

    public function showDemo(Request $request, $token)
    {
        $admin = $request->get('admin');
        //        if ($token !== 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
        //            dd('功能尚未開放, 僅供展示');
        //        }
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
        //        dump($user);

        $dnplayer_running_total = 0;
        $dnplayer_total         = 0;
        $macAddresses           = Redis::sMembers("token:$token:machines");
        //        dump($macAddresses);
        $machines               = [];
        $m_info                 = [
            'rows'  => [],
            'card'  => '',
            'merge' => [],
        ];
        $merges = [];
        $money_total = 0;
        $not_check_role_status = [
            '',
            '工具開始',
            '遊戲執行',
            '角色死亡',
        ];
        $merges   = [];
        foreach ($macAddresses as $mac) {
            $key         = "token:$token:mac:$mac";
            $machine     = Redis::hGetAll($key);
            $lastUpdated = $machine['last_updated'] ?? 0;
            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

//            $merge   = [];
            $rows   = [];
            $rows_status = [];
            $money_rows = [];
            $card    = '';
            $pc_name = isset($machine['pc_name']) ? $machine['pc_name'] : '';
            $machine['pro_version'] = isset($machine['pro_version']) ? $machine['pro_version'] : '';
            if (isset($machine['m_info']) && $machine['m_info'] != '' && ! is_null($machine['m_info'])) {
                $m_info = json_decode(base64_decode($machine['m_info']), true);
//                if (isset($m_info['merge'])) {
//                    $merge = $m_info['merge'];
//                }
                if (isset($m_info['rows'])) {
                    $rows = $m_info['rows'];
                }
                if (isset($m_info['card'])) {
                    $card = str_replace('?', '時', $m_info['card']);
                }
            }
            $dnplayer         = isset($machine['dnplayer']) ? $machine['dnplayer'] : 0;
            $dnplayer_running = isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0;

            foreach ($rows as $role) {
                if(!in_array($role[2], $not_check_role_status)) {
                    if (!isset($rows_status[$role[2]])) {
                        $rows_status[$role[2]] = 1;
                    } else {
                        $rows_status[$role[2]]++;
                    }
                }
                $temp_name = str_replace('(', '', $role[4]);
                $temp_name = str_replace(')', '-', $temp_name);
                if ($temp_name !== '') {
                    if (!isset($money_rows[$temp_name])) {
                        $money_rows[$temp_name]['total'] =  (int) $role[3];
                        $money_rows[$temp_name]['rows'] = $role[3]. '<br>';
                    } else {
                        $money_rows[$temp_name]['total'] = (int) $money_rows[$temp_name]['total'] +  (int) $role[3];
                        $money_rows[$temp_name]['rows'] .= $role[3] . '<br>';
                    }
                    if (!isset($merges[$temp_name])) {
                        $merges[$temp_name] =  (int) $role[3];
                    } else {
                        $merges[$temp_name] = (int) $merges[$temp_name] +  (int) $role[3];
                    }
                }
                if (strpos($role[12], '?') !== false) {
                    $card = str_replace('?', '時', $role[12]);
                }
            }

//            foreach ($merge as $merge_sub => $merge_sub_total) {
//                $money_total = $money_total + $merge_sub_total;
//                if (!isset($merges[$merge_sub])) {
//                    $merges[$merge_sub] = $merge_sub_total;
//                } else {
//                    $merges[$merge_sub] = $merges[$merge_sub] + $merge_sub_total;
//                }
//            }
            $machines[]             = [
                'mac'              => $mac,
                'pc_name'          => $pc_name,
//                'merge'            => $merge,
                'card'             => $card,
                'dnplayer'         => $dnplayer,
                'dnplayer_running' => $dnplayer_running,
                //                'm_info'           => $groupedData,
                'data'             => $machine,
                'role_list'        => $rows,
                'money_rows'       => $money_rows,
                'rows'             => $rows_status
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

        $originalArray = config('gods');
        $currentData = $merges;
        // 建立名稱和WorldNo的映射
        $nameToWorldNo = [];
        foreach ($originalArray as $item) {
            $nameToWorldNo[$item['Name']] = $item['WorldNo'];
        }

        // 將當前資料按照WorldNo排序
        uksort($currentData, function($a, $b) use ($nameToWorldNo) {
            // 處理名稱中的特殊前綴
            //            $aName = str_replace(["(重生)", "(經典)"], "", $a);
            //            $bName = str_replace(["(重生)", "(經典)"], "", $b);

            $aWorldNo = $nameToWorldNo[$a] ?? PHP_INT_MAX; // 如果找不到，則放到最後
            $bWorldNo = $nameToWorldNo[$b] ?? PHP_INT_MAX;

            return $aWorldNo <=> $bWorldNo;
        });
        $money_total = array_sum($currentData);
        if ($token === 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB' and is_null($admin)) {
            return view('demo', [
                //                'macCount' => $macCount,
                'user'                   => $user,
                'machines'               => $machines,
                'token'                  => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total'         => $dnplayer_total,
                'machines_total'         => $machines_total,
                'merges'         => $currentData,
                'money_total'         => $money_total,
            ]);
        } else {
            return view('machines4', [
                //                'macCount' => $macCount,
                'user'                   => $user,
                'machines'               => $machines,
                'token'                  => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total'         => $dnplayer_total,
                'machines_total'         => $machines_total,
                'merges'         => $currentData,
                'money_total'         => $money_total,
            ]);
        }

        //        return response()->json(['machines' => $machines]);
    }

    public function showBill(Request $request, $token)
    {
        $name = $request->input('name', '賴*德');
        $email = $request->input('email', 'irenewillie927@gmail.com');
        $dateline = $request->input('dateline', '2024/12/31');
        return view('bill', [
            'mobile' => $token,
            'mobileMark' => substr($token, 0, 5) . '***' . substr($token, -2),
            'mobileAdd' =>  substr($token, 0, 4) . '-' . substr($token, 4, 3) . '-' . substr($token, 7),
            'name' => $name,
            'email' => $email,
            'dateline' => $dateline,
            'total' => rand(500, 2000),
        ]);
    }

    public function showTest($token)
    {
        //        if ($token !== 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
        //            dd('功能尚未開放, 僅供展示');
        //        }
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
//        dump($user);

        $dnplayer_running_total = 0;
        $dnplayer_total         = 0;
        $macAddresses           = Redis::sMembers("token:$token:machines");
//        dump($macAddresses);
        $machines               = [];
        $m_info                 = [
            'rows'  => [],
            'card'  => '',
            'merge' => [],
        ];
        $merges = [];
        $money_total = 0;
        $not_check_role_status = [
            '',
            '工具開始',
            '遊戲執行',
            '角色死亡',
        ];
        foreach ($macAddresses as $mac) {
            $key         = "token:$token:mac:$mac";
            $machine     = Redis::hGetAll($key);
            $lastUpdated = $machine['last_updated'] ?? 0;
            if (now()->timestamp - $lastUpdated > 1800) {
                Redis::hSet($key, 'status', 'pc_not_open');
                $machine['status'] = 'pc_not_open'; // 更新本地变量以反映新状态
            }

            $merge   = [];
            $rows   = [];
            $rows_status = [];
            $money_rows = [];
            $card    = '';
            $pc_name = isset($machine['pc_name']) ? $machine['pc_name'] : '';
            if (isset($machine['m_info']) && $machine['m_info'] != '' && ! is_null($machine['m_info'])) {
                $m_info = json_decode(base64_decode($machine['m_info']), true);
                if (isset($m_info['merge'])) {
                    $merge = $m_info['merge'];
                }
                if (isset($m_info['rows'])) {
                    $rows = $m_info['rows'];
                }
                if (isset($m_info['card'])) {
                    $card = str_replace('?', '時', $m_info['card']);
                }
            }
            $dnplayer         = isset($machine['dnplayer']) ? $machine['dnplayer'] : 0;
            $dnplayer_running = isset($machine['dnplayer_running']) ? $machine['dnplayer_running'] : 0;

            foreach ($rows as $role) {
                if(!in_array($role[2], $not_check_role_status)) {
                    if (!isset($rows_status[$role[2]])) {
                        $rows_status[$role[2]] = 1;
                    } else {
                        $rows_status[$role[2]]++;
                    }
                }
                // 鑽石 csv 複製用
                $temp_name = str_replace('(', '', $role[4]);
                $temp_name = str_replace(')', '-', $temp_name);
                if ($temp_name !== '') {
                    if (!isset($money_rows[$temp_name])) {
                        $money_rows[$temp_name]['total'] =  (int) $role[3];
                        $money_rows[$temp_name]['rows'] = $role[3]. '<br>';
                    } else {
                        $money_rows[$temp_name]['total'] = (int) $money_rows[$temp_name]['total'] +  (int) $role[3];
                        $money_rows[$temp_name]['rows'] .= $role[3] . '<br>';
                    }
                }
            }

            foreach ($merge as $merge_sub => $merge_sub_total) {
                $money_total = $money_total + $merge_sub_total;
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
                'data'             => $machine,
                'role_list'        => $rows,
                'money_rows'       => $money_rows,
                'rows'             => $rows_status
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

        $originalArray = config('gods');
        $currentData = $merges;
        // 建立名稱和WorldNo的映射
        $nameToWorldNo = [];
        foreach ($originalArray as $item) {
            $nameToWorldNo[$item['Name']] = $item['WorldNo'];
        }

        // 將當前資料按照WorldNo排序
        uksort($currentData, function($a, $b) use ($nameToWorldNo) {
            // 處理名稱中的特殊前綴
//            $aName = str_replace(["(重生)", "(經典)"], "", $a);
//            $bName = str_replace(["(重生)", "(經典)"], "", $b);

            $aWorldNo = $nameToWorldNo[$a] ?? PHP_INT_MAX; // 如果找不到，則放到最後
            $bWorldNo = $nameToWorldNo[$b] ?? PHP_INT_MAX;

            return $aWorldNo <=> $bWorldNo;
        });

        if ($token === 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB') {
            return view('demo', [
                //                'macCount' => $macCount,
                'user'                   => $user,
                'machines'               => $machines,
                'token'                  => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total'         => $dnplayer_total,
                'machines_total'         => $machines_total,
                'merges'         => $currentData,
                'money_total'         => $money_total,
            ]);
        } else {
            return view('machines4', [
                //                'macCount' => $macCount,
                'user'                   => $user,
                'machines'               => $machines,
                'token'                  => $token,
                'dnplayer_running_total' => $dnplayer_running_total,
                'dnplayer_total'         => $dnplayer_total,
                'machines_total'         => $machines_total,
                'merges'         => $currentData,
                'money_total'         => $money_total,
            ]);
        }

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

    public function deleteMachineFromLine(Request $request)
    {
        $token = $request->get('token');
        $mac   = $request->get('mac');
        $key   = "token:$token:mac:$mac";

        Redis::del($key);
        Redis::sRem("token:$token:machines", $mac);

        return redirect(sprintf('https://mbot-3-ac8b63fd9692.herokuapp.com/pro/%s', $token));
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
