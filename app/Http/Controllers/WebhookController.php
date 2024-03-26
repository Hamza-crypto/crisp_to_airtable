<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Exception;
use Google\Service\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;
use Illuminate\Support\Facades\File;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use Revolution\Google\Sheets\Facades\Sheets;

class WebhookController extends Controller
{
    public $at_controller;

    public function __construct()
    {
        $this->at_controller = new AirTableController();
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        // Splitting the name into first and last name
        $fullName = explode(' ', $data['nickname']);
        $firstName = $fullName[0];
        $lastName = isset($fullName[1]) ? $fullName[1] : '';

        // Extracting course interests
        $courseInterests = implode(', ', $data['fields']['Course interest']);

        $body = [
            'fields' => [
                'First name' => $firstName,
                'Last name' => $lastName,
                'Email' => $data['email'] ?? '',
                'Phone' => $data['phone'] ?? '',
                'GCLID' => $data['GCLID'] ?? '',
                'crisp_profile' => $data['crisp_profile'] ?? '',
                'whatsapp' => $data['whatsapp_business_number'] ?? '',
                'Course interest' => $courseInterests,
                'utm_source' => $data['utm_source'] ?? '',
                'Status' => 'NEW',
            ]
        ];

        $url = sprintf("%s/%s", env('BASE_ID'), env('TABLE_NAME'));

        $response = $this->at_controller->call($url, 'POST', $body);
        dump($response);
    }
}