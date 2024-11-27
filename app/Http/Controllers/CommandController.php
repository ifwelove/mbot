<?php

namespace App\Http\Controllers;

use App\Http\FileService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CommandController extends Controller
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

    public function storeAllMacCommand(Request $request)
    {
        $validated = $request->validate([
            'token'   => 'required|string',
            'command' => 'required|string',
        ]);

        $token   = $validated['token'];
        $command = $validated['command'];
        $commands = ['close_mpro', 'reopen_mpro', 'open_mpro', 'update_mpro', 'reboot_pc', 'sort_player', 'copy_to_local', 'open_update_mpro', 'close_update_mpro', 'reopen_monitor', 'apk_install'];
        if (!in_array($command, $commands)) {
            return response()->json(['message' => 'Command stored failed']);
        }
        //loop
        $macAddresses = Redis::sMembers("token:$token:machines");
        foreach ($macAddresses as $mac) {
            $redisKey = "token:{$token}:mac:{$mac}:command";

            // 使用 SET 命令存储命令，并设置过期时间
            Redis::set($redisKey, $command, 'EX', 86400 / 24 / 5); // 这里我们设置了 24 小时的过期时间
        }


        return response()->json(['message' => '發送命令成功, 等待命令執行, 如120秒內未執行會放棄該命令, 請再重新點選命令']);
    }

    public function storeCommand(Request $request)
    {
        $validated = $request->validate([
            'token'   => 'required|string',
            'mac'     => 'required|string',
            'command' => 'required|string',
        ]);

        $token   = $validated['token'];
        $mac     = $validated['mac'];
        $command = $validated['command'];
        $commands = ['close_mpro', 'reopen_mpro', 'open_mpro', 'update_mpro', 'reboot_pc', 'sort_player', 'copy_to_local', 'open_update_mpro', 'close_update_mpro', 'reopen_monitor', 'apk_install'];
        if (!in_array($command, $commands)) {
            return response()->json(['message' => 'Command stored failed']);
        }
        $redisKey = "token:{$token}:mac:{$mac}:command";

        // 使用 SET 命令存储命令，并设置过期时间
        Redis::set($redisKey, $command, 'EX', 86400 / 24 / 5); // 这里我们设置了 24 小时的过期时间

        return response()->json(['message' => '發送命令成功, 等待命令執行, 如120秒內未執行會放棄該命令, 請再重新點選命令']);
    }

    public function getAndClearCommand(Request $request)
    {
        $host = $request->getHost(); // 取得主機名稱
        $currentMinute = now()->format('Y-m-d H:i'); // 以分鐘為單位統計
        $redisKey = "api_calls:{$host}:{$currentMinute}";
        // 記錄次數，並設置 TTL 為 1 天（86400 秒）
        Redis::incr($redisKey);
        Redis::expire($redisKey, 86400 / 12);

        if (rand(1, 100) <= 30) { // 30% 機率成立
            return response()->json(['message' => 'no_command']);
        }
//        $validated['token'] = 'M7PMOK6orqUHedUCqMVwJSTUALCnMr8FQyyEQS6gyrB';
//        $validated['mac'] = '22:35:4D:08:03:29';

        $validated = $request->validate([
            'token' => 'required|string',
            'mac'   => 'required|string',
        ]);


        $token = $validated['token'];
        $mac   = $validated['mac'];

        $redisKey = "token:{$token}:mac:{$mac}:command";

        // 获取命令
        $command = Redis::get($redisKey);

        if ($command) {
            // 命令存在，删除 key
            Redis::del($redisKey);

            return response()->json(['command' => $command]);
        } else {
            // 命令不存在
            return response()->json(['message' => 'no_command']);
        }
    }

    public function clearCommands(Request $request)
    {
        // 驗證請求參數
        $validated = $request->validate([
            'token' => 'required|string',
            'mac'   => 'required|string',
        ]);

        $token = $validated['token'];
        $mac   = $validated['mac'];

        // 定義 Redis key
        $redisKey = "token:{$token}:mac:{$mac}:commands";

        // 刪除與這個 key 關聯的所有指令
        Redis::del($redisKey);

        // 返回一個成功的響應
        return response()->json([
            'message' => 'Commands cleared successfully',
        ]);
    }

}
