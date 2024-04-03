<?php

use App\Http\Controllers\WebhookController;
use App\Models\AirTable;
use App\Notifications\AirTableNotification;
use Brick\Math\Exception\DivisionByZeroException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use NotificationChannels\Telegram\TelegramChannel;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('updates', function () {
    Artisan::call('crisp:update');
});

Route::get('db', function () {
    $record = AirTable::first();
    dump($record);
});

Route::get('/telegram', function () {
    $data_array['to'] = 'crisp_airtable';
    $data_array['msg'] = sprintf("Leadvertex order no. %s status updated to %s", 2, 6);

    dump($data_array['aa']);
    Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));

});

Route::get('/', function () {
    return view('welcome');
});

Route::controller(WebhookController::class)->group(function () {
    Route::get('webhook', 'webhook');
    Route::post('webhook', 'webhook');
});

Route::get('migrate', function () {
    Artisan::call('migrate:fresh --seed');
    dump('Migration Done');
});

Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    dump('Optimization Done');
});