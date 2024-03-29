<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class LineNotifyController extends Controller
{

    public function __construct()
    {
    }

    public function apply(Request $request){
        return redirect('https://notify-bot.line.me/oauth/authorize?response_type=code&scope=notify&response_mode=form_post&client_id=XW10Vs7FaqpDEnaqf4KUg8&redirect_uri=https://mbot-3-ac8b63fd9692.herokuapp.com/notify&state=f094a459-1d16-42d6-a709-c2b61ec53d60');
    }
    public function index(Request $request)
    {
        $code = $request->input('code');

        // 獲取 access_token
        $responseData = Http::asForm()
            ->post('https://notify-bot.line.me/oauth/token', [
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => 'https://mbot-3-ac8b63fd9692.herokuapp.com/notify',
                'client_id'     => 'XW10Vs7FaqpDEnaqf4KUg8',
                'client_secret' => 'ifWwCg8edsA3zZZZFd5SFdFwenHM5VwQfmKeOyKoUuQ',
            ])
            ->json();

        $accessToken = Arr::get($responseData, 'access_token');

        // 發送 notify 訊息
        $message_user = "你的Token為：" . $accessToken . "\n";
        $message_user .= '你的網址為：' . "https://mbot-3-ac8b63fd9692.herokuapp.com/pro/" . $accessToken;
        $responseData = Http::asForm()
            ->withHeaders([
                'Authorization' => "Bearer {$accessToken}"
            ])
            ->post('https://notify-api.line.me/api/notify', [
                'message' => $message_user
            ])
            ->json();

        $client   = new Client();
        $headers  = [
            'Authorization' => sprintf('Bearer %s', '5hcyGO935sKzRjF522X1UPPNnfL5QqYCMrLnB5M0KhE'),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $options  = [
            'form_params' => [
                'message' => $accessToken
            ]
        ];
        $response = $client->request('POST', 'https://notify-api.line.me/api/notify', [
            'headers'     => $headers,
            'form_params' => $options['form_params']
        ]);

        $status = Arr::get($responseData, 'status');

        if ($status !== 200) {
            response('連動失敗', $status);
        }

        dump('你的網址為：' . "https://mbot-3-ac8b63fd9692.herokuapp.com/pro/" . $accessToken);
        dump("你的Token為：" . $accessToken);

        return response('已經連動成功, 請保存好你的 token, 並等待管理者開通', 200);
    }
}
