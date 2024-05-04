<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

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

Route::get('webhooks', function () {
    Artisan::call('airtable:fetch-webhooks');
});


Route::get('updates', function () {
    Artisan::call('crisp:update');
});


Route::get('webhooks', function () {
    Artisan::call('airtable:fetch-webhooks');
});



Route::get('/', function () {
    return view('welcome');
});

Route::controller(WebhookController::class)->group(function () {
    Route::get('webhook', 'webhook');
    Route::post('webhook', 'webhook');
    Route::post('webhook/rak', 'webhook_rak');
});

Route::get('migrate', function () {
    Artisan::call('migrate:fresh --seed');
    dump('Migration Done');
});

Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    dump('Optimization Done');
});
