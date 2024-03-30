<?php

namespace App\Http\Controllers;

use App\Models\AirTable;
use App\Models\Cursor;
use Exception;
use Illuminate\Support\Facades\Http;

class AirTableController extends Controller
{
    public function call($endpoint, $method = 'GET', $body = [])
    {
        $url = sprintf("%s/%s/%s/%s",
            env('AIRTABLE_BASE_URL'),
            env('BASE_ID'),
            env('TABLE_NAME'),
            $endpoint);

        $response = Http::withToken(env('AIRTABLE_TOKEN'));

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
        $url = sprintf("bases/%s/webhooks/%s/payloads?cursor=%s", env('BASE_ID'), env('WEBHOOK_ID'), $cursor->count);

        $data = $this->call($url);

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

        foreach ($data['payloads'] as $payload) {
            if (isset($payload['actionMetadata']['source']) && $payload['actionMetadata']['source'] === 'system') {
                continue; // Skip processing this item
            }

            $changedTables = $payload['changedTablesById'];

            try{
                foreach ($changedTables as $tableId => $changedRecords) {
                foreach ($changedRecords['changedRecordsById'] as $recordId => $record) {
                    $cellValues = $record['current']['cellValuesByFieldId'];

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
                            $airTable->table = $tableId;
                            $airTable->record = $recordId;
                            $airTable->field = $fieldId;
                            $airTable->value = $value;
                            $airTable->save();
                        }
                    }
                }
            }
            }
            catch(Exception $e){
                    dump($payload);
                dump($e->getMessage());
            }

        }
        Cursor::where('id', 1)->update(['count' => $data['cursor']]);
        return response()->json(['message' => 'Data stored/updated successfully'], 200);
    }
}