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
            'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB' => '123' //本人
        ];
        if (isset($tokens[$token])) {
            return true;
        } else {
            return false;
        }
    }

    public function alert(Request $request)
    {
        $token = $request->post('token');
//        dd($request->all());
        $result = $this->checkAllowToken($token);
        if ($result === false) {
            return response('token 未授權', 200)->header('Content-Type', 'text/plain');
        }
        $message = $request->post('message');
        $client = new Client();
        $headers = [
            'Authorization' => sprintf('Bearer %s', $token),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $options = [
            'form_params' => [
//                'message' => $message
                'message' => json_encode($request->all())
            ]];
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers' => $headers,
            'form_params' => $options['form_params']
        ]);

        return response('ok', 200)->header('Content-Type', 'text/plain');
    }
}
