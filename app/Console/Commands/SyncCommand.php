<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $line = config('line');
        $tableName = '';
        $count     = 1;
        DB::connection('sync')
            ->table('ItemSell')
            ->chunkById(1000, function ($items) use (&$count, &$tableName, $line) {
                $insert = [];
                foreach ($items as $item) {
                    $row      = [
                        'ItemVolume' => $item->ItemVolume,
                        'ItemCount'  => $item->ItemCount,
                        'ItemName'   => $item->ItemName,
                        'ServerID'   => $item->ServerID,
                        'TradeType'  => $item->TradeType,
                        'Update_at'  => $item->Update_at,
                        'ItemColor'  => $item->ItemColor,
                    ];
                    $insert[] = $row;
                }
                $client    = new Client();
                $response  = $client->post(sprintf('%s/items/test', $line['sync_url']), [
                    'json' => [
                        'items' => $insert
                    ]
                ]);
                $tableName = json_decode($response->getBody()
                    ->getContents(), true);
                $count++;
            }, 'index');
        $client             = new Client();
        $response           = $client->post(sprintf('%s/items/test', $line['sync_url']), [
            'json' => [
                'table' => $tableName['table']
            ]
        ]);
    }
}
