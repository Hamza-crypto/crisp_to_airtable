<?php

namespace App\Console\Commands;

use App\Http\Controllers\AirTableController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchWebhooks extends Command
{
    protected $signature = 'airtable:fetch-webhooks';

    protected $description = 'It will read all the webhooks periodically';

    public function handle()
    {
        $air_table_controller = new AirTableController();

        // Check if API calls should be paused
        if (Cache::get('sale_api_pause_flag')) {
            dump('API calls are paused. Skipping fetch.');
        } else {
            $air_table_controller->store();
        }

        $air_table_controller->store_rak(); //For RAK
    }
}
