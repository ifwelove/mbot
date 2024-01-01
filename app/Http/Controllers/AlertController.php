<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AlertController extends Controller
{

    public function __construct()
    {
    }

    private function checkAllowToken($token)
    {
        // todo 幾台 和 日期
        $tokens = [
            'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB' => 'very6', //本人
            'bWBWihKBoPyGbNN5Ht14TtBtfN0H9f7quS1fV7LCyU3' => 'test555',
            '1EW9dRJOANPRwZYvS0gZblhxGPZvJ9ZNEBdpLlvARUu' => '青蛙'
        ];
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
                    'message' => $request->post('pc_name')
//                    'message' => json_encode($request->all())
                ]];
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers' => $headers,
                'form_params' => $options['form_params']
            ]);

            return response('token 未授權 無法進行推送到 line', 200)->header('Content-Type', 'text/plain');
        }
        $message = $request->post('message');
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
            $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
                'headers' => $headers,
                'form_params' => $options['form_params']
            ]);
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
}
