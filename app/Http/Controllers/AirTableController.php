<?php

namespace App\Http\Controllers;

use App\Models\AirTable;
use App\Models\Cursor;
use App\Notifications\AirTableNotification;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class AirTableController extends Controller
{
    public function call($endpoint, $method = 'GET', $body = [])
    {
        $url = sprintf(
            '%s/%s/%s/%s',
            env('AIRTABLE_BASE_URL'),
            env('BASE_ID'),
            env('TABLE_NAME'),
            $endpoint
        );

        $response = Http::withToken(env('AIRTABLE_TOKEN'));

        if ($method === 'GET') {
            $response = $response->get($url);
        } elseif ($method === 'POST') {
            $response = $response->post($url, $body);
        }

        return $response->json();
    }

    public function call_rak($endpoint, $method = 'GET', $body = [])
    {
        $url = sprintf(
            '%s/%s/%s/%s',
            env('AIRTABLE_BASE_URL'),
            env('RAK_BASE_ID'),
            env('RAK_TABLE_NAME'),
            $endpoint
        );

        $response = Http::withToken(env('RAK_AIRTABLE_TOKEN'));

        if ($method === 'GET') {
            $response = $response->get($url);
        } elseif ($method === 'POST') {
            $response = $response->post($url, $body);
        }

        return $response->json();
    }


    public function store()
    {
        $cursor = Cursor::where('id', 1)->first();
        $url = sprintf('%s/bases/%s/webhooks/%s/payloads?cursor=%s', env('AIRTABLE_BASE_URL'), env('BASE_ID'), env('WEBHOOK_ID'), $cursor->count);
        //$url = "http://localhost:8787/";
        $response = Http::withToken(env('AIRTABLE_TOKEN'))->get($url);

        $data = $response->json();

        // Check if mightHaveMore is false
        if (!$data['mightHaveMore']) {
            // Pause API calls for next 5 minutes
            Cache::put('sale_api_pause_flag', true, 300);
            dump('Making cache for sale');
        }


        $sourceValues = $this->store_data($data, 'sales');

        Cursor::where('id', 1)->update(['count' => $data['cursor']]);

        if($sourceValues) {
            $uniqueSourceValues = array_unique($sourceValues);
            $sourceString = implode(', ', $uniqueSourceValues);
            $data_array['msg'] = sprintf("Webhook cursor: %s \n %s", $cursor->count, $sourceString);
            Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));
        }

        return response()->json(['message' => 'Data stored/updated successfully'], 200);

    }


    public function store_rak()
    {
        $cursor = Cursor::where('id', 2)->first(); //2 = RAK CRM
        $url = sprintf('%s/bases/%s/webhooks/%s/payloads?cursor=%s', env('AIRTABLE_BASE_URL'), env('RAK_BASE_ID'), env('RAK_WEBHOOK_ID'), $cursor->count);
        //$url = "http://localhost:8787/";
        $response = Http::withToken(env('RAK_AIRTABLE_TOKEN'))->get($url);

        $data = $response->json();

        // Check if mightHaveMore is false
        if (!$data['mightHaveMore']) {
            // Pause API calls for next 5 minutes
            Cache::put('rak_api_pause_flag', true, 300);
            dump('Making cache for rak');
        }

        $sourceValues = [];

        $sourceValues = $this->store_data($data, 'rak');

        Cursor::where('id', 2)->update(['count' => $data['cursor']]);

        if($sourceValues) {
            $uniqueSourceValues = array_unique($sourceValues);
            $sourceString = implode(', ', $uniqueSourceValues);
            $data_array['msg'] = sprintf("Webhook cursor RAK: %s \n %s", $cursor->count, $sourceString);
            Notification::route(TelegramChannel::class, '')->notify(new AirTableNotification($data_array));
        }

        return response()->json(['message' => 'Data stored/updated successfully'], 200);

    }

    public function store_data($data, $base)
    {
        $allowedFields = [
            'fldyZIIKL2rGWc6J1', //first name
            'fldyZq9vgdzdSApC3', //last name
            'fldIpyhdmc00XmzAq', //email
            'fldI2mks558xsUhOs', //phone

            'fldxze6wDjeopL6o8', //glcid
            'fldEHTUWmPzH18HmM', //crisp profile
            'fldY2hh2yiHR7u42U', //whatsapp
            'fldY0BzN3lzWRTr2V', //course_interest
            'flddlmiODQr38Rm1e', //utm source

            'fldpIgUProUv4SvrA', //status
        ];
        $sourceValues = [];

        foreach ($data['payloads'] as $payload) {
            if (isset($payload['actionMetadata']['source']) && $payload['actionMetadata']['source'] === 'system') {
                continue; // Skip processing this item
            }

            $sourceValues[] = $payload['actionMetadata']['source'];

            $changedTables = $payload['changedTablesById'];

            try {
                foreach ($changedTables as $tableId => $changedRecords) {

                    // Check if the payload has 'createdRecordsById' key
                    if (isset($changedRecords['createdRecordsById'])) {
                        $records = $changedRecords['createdRecordsById'];
                    } elseif (isset($changedRecords['changedRecordsById'])) {
                        // Otherwise, check if it has 'changedRecordsById' key
                        $records = $changedRecords['changedRecordsById'];
                    } else {
                        // If neither key is present, skip this table
                        continue;
                    }
                    foreach ($records as $recordId => $record) {
                        $cellValues = isset($record['current']) ? $record['current']['cellValuesByFieldId'] : $record['cellValuesByFieldId'];

                        foreach ($cellValues as $fieldId => $value) {

                            // Check if the field is allowed
                            // if ( !in_array($fieldId, $allowedFields)) {
                            //     continue;
                            // }

                            // Initialize an empty array to hold the values
                            $values = [];

                            // Check if the value is an array
                            if (is_array($value)) {
                                // If it's an array, iterate through each item and get the 'name' field
                                foreach ($value as $item) {
                                    $values[] = $item['name'] ?? null;
                                }
                            } else {
                                // If it's a string, simply add it to the values array
                                $values[] = $value;
                            }

                            // Convert the values array to a comma-separated string
                            $value = implode(', ', $values);

                            // Check if the record already exists
                            $existingRecord = AirTable::where('table', $tableId)
                                ->where('record', $recordId)
                                ->where('field', $fieldId)
                                ->first();

                            if ($existingRecord) {
                                // Update the existing record
                                $existingRecord->value = $value;
                                $existingRecord->save();
                            } else {
                                // Create a new record
                                $airTable = new AirTable();
                                $airTable->base = $base;
                                $airTable->table = $tableId;
                                $airTable->record = $recordId;
                                $airTable->field = $fieldId;
                                $airTable->value = $value;
                                $airTable->save();
                            }
                        }
                    }
                }
            } catch (Exception $e) {

                dump($e->getMessage());
            }

        }

        return $sourceValues;
    }

}
