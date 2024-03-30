<?php

namespace App\Console\Commands;

use App\Http\Controllers\CrispController;
use App\Http\Controllers\AirTableController;
use App\Models\AirTable;
use Exception;
use Illuminate\Console\Command;

class UpdateDataOnCrisp extends Command
{
    protected $signature = 'crisp:update';

    protected $description = 'Command description';

    public function handle()
    {
        $air_table_controller = new AirTableController;
        $crisp_controller = new CrispController();

        $webhook = AirTable::firstOrFail();

        try{
            $url = sprintf("%s", $webhook->record);
            $data = $air_table_controller->call($url);

            $data = $data['fields'];

            if (!isset($data['Email'])) {
                return 0;
            }



            $this->createNewContact($crisp_controller, $data);
            $this->updateContactInfo($crisp_controller, $data);
            $this->updateProfileInfo($crisp_controller, $data);

            AirTable::where('record', $webhook->record)->delete();
        }
        catch(Exception $e){
            AirTable::where('record', $webhook->record)->delete();
            dump($e->getMessage());
        }

    }

    public function createNewContact($crisp_controller, $data)
    {
        $email = $data['Email'];
        $url = sprintf("people/profile/%s", $email);
        $response = $crisp_controller->call($url);

        //Create new contact if not found on Crisp
        if( $response['error']) {

            $body['email'] = $email;

            if (isset($data['Full Name'])) {
                $body['person']['nickname'] = $data['Full Name'];
            }

            if (isset($data['Phone'])) {
                $body['person']['phone'] = $data['Phone'];
            }

            if (isset($data['Gender'])) {
                $body['person']['gender'] = strtolower($data['Gender']);
            }

        $url = sprintf("people/profile");
        $response = $crisp_controller->call($url, 'POST', $body);
        dump($response);
        }

    }

    public function updateContactInfo($crisp_controller, $data)
    {
        $email = $data['Email'];

        if (isset($data['Full Name'])) {
            $body['person']['nickname'] = $data['Full Name'];
        }

        if (isset($data['Phone'])) {
            $body['person']['phone'] = $data['Phone'];
        }

        if (isset($data['Gender'])) {
            $body['person']['gender'] = strtolower($data['Gender']);
        }

        $url = sprintf("people/profile/%s", $email);
        $response = $crisp_controller->call($url, 'PATCH', $body);
        dump($response);
    }

    public function updateProfileInfo($crisp_controller, $data)
    {
        $email = $data['Email'];

        if (isset($data['whatsapp'])) {
            $body['data']['whatsapp_business_number'] = $data['whatsapp'];
        }

        if (isset($data['Course interest'])) {
            if (is_array($data['Course interest'])) {
                $body['data']['course_interest'] = implode(', ', $data['Course interest']);
            }
            else {
                $body['data']['course_interest'] = $data['Course interest'];
            }
        }
        else {
            $body['data']['course_interest'] = "";
        }

        $body['data']['nationality'] = isset($data['Nationality']) ? $data['Nationality'] : '';
        $body['data']['registered'] = isset($data['Registered']) ? $data['Registered'] : '';
        $body['data']['preferred_timing'] = isset($data['Preferred timing']) ? $data['Preferred timing'] : '';
        $body['data']['total_spend'] = isset($data['Amount Rollup (from Deal)']) ? $data['Amount Rollup (from Deal)'] : '';
        $body['data']['branch'] = "Dubai"; // isset($data['Branch']) ? $data['Branch'] : '';


        $body['data']['utm_campaign'] = isset($data['utm_campaign']) ? $data['utm_campaign'] : '';
        $body['data']['utm_source'] = isset($data['utm_source']) ? $data['utm_source'] : '';
        $body['data']['utm_term'] = isset($data['utm_term']) ? $data['utm_term'] : '';

        $body['data']['GCLID'] = isset($data['GCLID']) ? $data['GCLID'] : '';

        $body['data']['status'] = isset($data['Status']) ? $data['Status'] : '';

        $url = sprintf("people/data/%s", $email);
        $response = $crisp_controller->call($url, 'PATCH', $body);

        dump($response);
    }
}