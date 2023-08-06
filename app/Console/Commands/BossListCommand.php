<?php

namespace App\Console\Commands;

use App\Games;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BossListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boss:list {--groupId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $groupId = $this->option('groupId', null);
        if (is_null($groupId)) {
            return false;
        }
        $bossList = \Illuminate\Support\Facades\Cache::get($groupId);
        if (is_null($bossList)) {
            $bossList = [];
        }
        foreach ($bossList as $boss => &$info) {
            $this->nextTime($boss, $info);
        }
        $bossList = collect($bossList)->sort(function($a, $b) {
            $aNextTime = Carbon::createFromFormat('Y-m-d H:i:s', $a['nextTime']);
            $bNextTime = Carbon::createFromFormat('Y-m-d H:i:s', $b['nextTime']);
            if ($aNextTime->eq($bNextTime)) {
                return 0;
            }

            return ($aNextTime->gt($bNextTime)) ? 1 : -1;
        })->toArray();
        \Illuminate\Support\Facades\Cache::put($groupId, $bossList);
    }

    private function nextTime($boss, &$info)
    {
        $bossConfig = Config::get('boss');
        $now        = Carbon::now();
        if ($now->gt($info['nextTime'])) {
            $nextTime         = Carbon::createFromFormat('Y-m-d H:i:s', $info['nextTime'])
                ->addMinutes($bossConfig[$boss]);
            $info['nextTime'] = $nextTime->format('Y-m-d H:i:s');
            $info['pass']++;//換算次數不是只有++
            $this->nextTime($boss, $info);
        }
    }
}

