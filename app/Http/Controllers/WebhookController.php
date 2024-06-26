<?php

namespace App\Http\Controllers;

use App\Notifications\AirTableNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class WebhookController extends Controller
{
    public $air_table_controller;

    public $crisp_controller;

    public function __construct()
    {
        $this->air_table_controller = new AirTableController();
        $this->crisp_controller = new CrispController();
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        // Check if both email and phone are missing
        if (empty($data['email']) && empty($data['phone'])) {
            return 0; // Return 0 and exit further execution
        }

        // Splitting the name into first and last name
        $fullName = explode(' ', $data['nickname']);
        $firstName = $fullName[0];
        $lastName = isset($fullName[1]) ? $fullName[1] : '';

        // Extracting course interests
        $courseInterests = $data['course_interest'] ?? ''; // Set to empty string if course interest is undefined or null
        if (is_array($courseInterests)) {
            $courseInterests = implode(', ', $courseInterests);
        }

        $body = [
            'fields' => [
                'First name' => $firstName,
                'Last name' => $lastName,
                'Status' => 'NEW',
            ],
        ];

        // Conditionally add fields based on whether their values are defined and not equal to "undefined" or an empty string
        if (! empty($data['email']) && $data['email'] !== 'undefined') {
            $body['fields']['Email'] = trim($data['email']);
        }
        if (! empty($data['phone']) && $data['phone'] !== 'undefined') {
            $body['fields']['Phone'] = $data['phone'];
        }
        if (isset($data['GCLID']) && $data['GCLID'] !== 'undefined') {
            $body['fields']['GCLID'] = $data['GCLID'];
        }
        if (isset($data['crisp_profile']) && $data['crisp_profile'] !== 'undefined') {
            // Extract the URL from the "crisp_profile" field using regex
            preg_match('/\[Open Profile\]\((.*?)\)/', $data['crisp_profile'], $matches);

            // Check if the regex found a match
            if (! empty($matches[1])) {
                // Extracted URL found, assign it to the 'crisp_profile' field
                $body['fields']['crisp_profile'] = $matches[1];
            }
        }
        if (isset($data['whatsapp_business_number']) && $data['whatsapp_business_number'] !== 'undefined') {
            $body['fields']['whatsapp'] = $data['whatsapp_business_number'];
        }

        if (! empty($courseInterests) && $courseInterests !== 'undefined') {
            $body['fields']['Course interest'] = [$courseInterests];
        }
        if (isset($data['utm_source']) && $data['utm_source'] !== 'undefined') {
            $body['fields']['utm_source'] = $data['utm_source'];
        }

        $url = ''; //sprintf("%s/%s", env('BASE_ID'), env('TABLE_NAME'));

        $response = $this->air_table_controller->call($url, 'POST', $body);

        // Check if response status is 200 and it contains the "id" field
        if (isset($response['id'])) {

            $data_array['msg'] = sprintf('Webhook successfuly sent for user: %s', $response['fields']['Email']);
            Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));

            return 'Record successfully created in AirTable';
        } else {
            return $response; // Return appropriate message if creation failed
        }

    }

    public function webhook_rak(Request $request)
    {
        $data = $request->all();

        // Check if both email and phone are missing
        if (empty($data['email']) && empty($data['phone'])) {
            return 0; // Return 0 and exit further execution
        }

        // Splitting the name into first and last name
        $fullName = explode(' ', $data['nickname']);
        $firstName = $fullName[0];
        $lastName = isset($fullName[1]) ? $fullName[1] : '';

        // Extracting course interests
        $courseInterests = $data['course_interest'] ?? ''; // Set to empty string if course interest is undefined or null
        if (is_array($courseInterests)) {
            $courseInterests = implode(', ', $courseInterests);
        }

        $body = [
            'fields' => [
                'First name' => $firstName,
                'Last name' => $lastName,
                'Status' => 'NEW',
                'Crisp' => true
            ],
        ];

        // Conditionally add fields based on whether their values are defined and not equal to "undefined" or an empty string
        if (! empty($data['email']) && $data['email'] !== 'undefined') {
            $body['fields']['Email'] = trim($data['email']);
        }
        if (! empty($data['phone']) && $data['phone'] !== 'undefined') {
            $body['fields']['Phone'] = $data['phone'];
        }

        if (! empty($courseInterests) && $courseInterests !== 'undefined') {
            $body['fields']['Course interest'] = [$courseInterests];
        }

        $url = ''; //sprintf("%s/%s", env('BASE_ID'), env('TABLE_NAME'));

        $response = $this->air_table_controller->call_rak($url, 'POST', $body);

        // Check if response status is 200 and it contains the "id" field
        if (isset($response['id'])) {

            $data_array['msg'] = sprintf('Webhook successfuly sent for user: %s', $response['fields']['Email']);
            //Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));

            return 'Record successfully created in AirTable';
        } else {
            return $response; // Return appropriate message if creation failed
        }

    }

    public function getMessages($session_id, $body)
    {
        try {
            $crisp_url = sprintf('conversation/%s/messages', $session_id);
            $responseData = $this->crisp_controller->call($crisp_url);

            if (! isset($responseData['data']) || empty($responseData['data'])) {
                return response()->json(['error' => true, 'message' => 'No messages found in the response.']);
            }

            $messages = $responseData['data'];

            // Concatenate all messages into a single string
            $conversation = '';
            foreach ($messages as $message) {
                $personName = $message['from'] === 'user' ? 'Visitor' : 'Operator'; // Determine person's name
                $conversation .= "{$personName}: {$message['content']}\n"; // Add person's name with message content and a newline separator
            }

            $body['fields']['crisp_conversations'] = $conversation;

            return $body;
        } catch (\Exception $e) {
            return $body;
        }

    }

    public function estimates(Request $request)
    {
        Http::post(env('ESTIMATES_WEB_URL'), $request->all());
    }
}
