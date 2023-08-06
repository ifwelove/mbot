<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BossKillCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boss:kill {--groupId=} {--name=} {--memo=} {--time=}';

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
        $boss     = Config::get('boss');
        $bossTags = Config::get('boss-tags');
        $list     = [];
        foreach ($bossTags as $name => $tags) {
            foreach ($tags as $tag) {
                $list[$tag] = $name;
            }
        }
        $name = $this->option('name') ? $list[$this->option('name')] : null;

        if (is_null($name)) {
            return false;
        }
        $memo = $this->option('memo') ? $this->option('memo') : '';
        $time = (! is_null($this->option('time'))) ? Carbon::createFromFormat('His', $this->option('time')) : Carbon::now();
        $now  = Carbon::now();
        if ($time->gt($now)) {
            $time->subDay();
        }
//        $pass     = 0;
//        $killTime = $time->format('Y-m-d H:i:s');
//        $nextTime = $time->addMinutes($boss[$name])
//            ->format('Y-m-d H:i:s');
//        while (1) {
//            if (!$now->gt($nextTime)) {
//                break;
//            } else {
//                $pass++;
//                $nextTime = $time->addMinutes($boss[$name])
//                    ->format('Y-m-d H:i:s');
//            }
//        }
        $bossList                    = [];
        $bossList[$name]['pass']     = 0;
        $bossList[$name]['memo']     = $memo;
        $bossList[$name]['killTime'] = $time->copy()->format('Y-m-d H:i:s');
        $bossList[$name]['nextTime'] = $time->copy()->addMinutes($boss[$name])
            ->format('Y-m-d H:i:s');
        $bossListOld                 = \Illuminate\Support\Facades\Cache::get($groupId);
        if (is_null($bossListOld)) {
            $bossListOld = [];
        }
//        Log::info($bossList);
//        Log::info($bossListOld);
        \Illuminate\Support\Facades\Cache::put($groupId, $bossList + $bossListOld);
    }
}

