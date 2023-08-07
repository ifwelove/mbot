<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping:heroku';

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
        $urls = [
            'https://sheltered-wildwood-81509-7319344d0e1b.herokuapp.com/ping',
            'https://mbot-2-6f5c0d4ec2bb.herokuapp.com/ping',
            'https://mbot-3-ac8b63fd9692.herokuapp.com/ping',
        ];
        foreach ($urls as $url) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: XSRF-TOKEN=eyJpdiI6ImR0ck9iNGEvZkg2YnhJcGpGbjd1V1E9PSIsInZhbHVlIjoiRjYzZFZJaU5QUlpaUGd2SmlIRENVa0pVUW5CbVlsRktzb3I4UWxZWjhQaGQ0ZGJOM244QVl5LzNLWDQrYlk1SHg2b0xyOEdQZkJmQVAvcC9GaThXNXZ0a242T0pCQ0lLQm1hbC9Sd0EvNFZjSXdxVVpVZGZta3g3QUc4R3JiT1YiLCJtYWMiOiI5OGVhMDNiZDg3M2E4YmFlMmU4NWI1ODBkYmZhZjZkZmY0NzRkZjAyNWUzMDIwMTA4OGEyNzMzNTliNTEzYWY4IiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6InFmOEJCNGxHYUlSOE85alRBMFZEZFE9PSIsInZhbHVlIjoic3k0QmhLcmQyNzYvM2Z2SnhoR1J3bmNNTWdmeVo0eFpjRDRmUzVCelNQR1BoM2FEQ3N5d3dyTDFZSm14Rnhja1JtSjA4amNSTEl5MXV1V3EwWkVlNHltVldLb1RIb0xBU1JWMjM2TzRNQWk1U0NNK082UlI0dCtPK0lSMjJpZVIiLCJtYWMiOiI3NjZlYjYxNzAzZGY0NGNjMTk2ZDI3N2Q1NzcyZmVkMmFiMjM5NjlhOGEzMWEzYTJhMmI5OGJmNTJmOGVmMzIzIiwidGFnIjoiIn0%3D'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
        }
    }
}

