<?php

namespace App\Console\Commands;

use App\Http\Controllers\AirTableController;
use Illuminate\Console\Command;

class FetchWebhooks extends Command
{
    protected $signature = 'airtable:fetch-webhooks';

    protected $description = 'It will read all the webhooks periodically';

    public function handle()
    {
        $air_table_controller = new AirTableController;
        $air_table_controller->store();
    }
}
