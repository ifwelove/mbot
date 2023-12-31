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
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers' => $headers,
            'form_params' => $options['form_params']
        ]);

        return response('ok', 200)->header('Content-Type', 'text/plain');
    }
}
