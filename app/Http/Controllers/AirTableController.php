<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Http;

class AirTableController extends Controller
{
    public function call($endpoint, $method = 'GET', $body = [])
    {
        $url = sprintf("%s/%s",
            env('AIRTABLE_BASE_URL'),
            $endpoint);

        $response = Http::withToken(env('AIRTABLE_TOKEN'));

        if ($method === 'GET') {
            $response = $response->get($url);
        } elseif ($method === 'POST') {
            $response = $response->post($url, $body);
        }

        return $response->json();
    }
}