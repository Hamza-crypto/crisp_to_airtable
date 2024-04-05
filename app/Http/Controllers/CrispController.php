<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class CrispController extends Controller
{
    public function call($endpoint, $method = 'GET', $body = [])
    {
        $url = sprintf('%s/%s',
            env('CRISP_BASE_URL'),
            $endpoint);

        $username = env('CRISP_TOKEN_IDENTIFIER');
        $token = env('CRISP_TOKEN_KEY');

        $headers = [
            'Content-Type' => 'application/json',
            'X-Crisp-Tier' => 'plugin',
            'Authorization' => 'Basic '.base64_encode($username.':'.$token),
        ];

        $response = null;

        if ($method === 'GET') {
            $response = Http::withHeaders($headers)->get($url);
        } elseif ($method === 'POST') {
            $response = Http::withHeaders($headers)->post($url, $body);
        } elseif ($method === 'PATCH') {
            $response = Http::withHeaders($headers)->patch($url, $body);
        }

        return $response->json();
    }
}
