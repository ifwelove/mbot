<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AlertController extends Controller
{

    public function __construct()
    {
    }

    public function alert(Request $request)
    {
        $token = $request->post('token');
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
        $request = new Request('POST', 'https://notify-api.line.me/api/notify', $headers);
        $res = $client->sendAsync($request, $options)->wait();
        echo $res->getBody();
    }
}
