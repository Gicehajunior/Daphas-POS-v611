<?php

namespace App\Utils\Custom;

use App\Barcode;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\NotificationTemplate;
use App\Printer;
use App\Unit;
use App\User;
use App\MpesaSetting;
use App\MpesaTransaction;
use App\Utils\Util;
use \MpesaPay;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\VariationLocationDetails; 


class CustomBusinessUtil extends Util
{
    /**
     * writes off all the errors that are catched by the 
     * mpesa callback functions
     *
     * @return \Illuminate\Http\Response
     */
    public function _mpesa_error_logs($file_error_log_name, $output, $callbackfunc) {  
        $logEntry = date("Y-m-d H:i:s") . ': From ' . $callbackfunc . ' function: Debug Error, '. ($output ? $output : 'No response from ' . $callbackfunc . ' mpesa callback');
        
        $filePath = public_path() . '/logs//' . $file_error_log_name . '_logs.txt';
        if (file_exists($filePath)) { 
            $file = fopen($filePath, 'a');
            
            if ($file) { 
                fwrite($file, $logEntry . PHP_EOL); 
                fclose($file); 
            }
        }
    }

    /**
     * Returns pos settings
     */
    public function mpesa_settings($id) { 
        $mpesa_settings = MpesaSetting::where('id', $id)->get();
        
        return $mpesa_settings;
    }

    /**
     * Returns the html decoded string
     *
     * @param int $business_id
     * @return array
     */
    public function decode_string_component($string) {
        $output = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($string)); 
        return html_entity_decode($output, null, 'UTF-8');
    }

    /**
     * Registers confirmation endpoint for mpesa daraja api. 
     * Registers validation endpoint for mpesa daraja api.
     * Return the mpesa confirmation, & validation endpoint registration 
     * request response;
     * 
     * @return array
     */
    public function register_mpesa_endpoints($CallBackEndpoints, $id) {
        $this->_mpesa_error_logs('mpesa_error', $id, 'register_mpesa_endpoints');
        $mpesa_settings = $this->mpesa_settings($id); 
        $callbacks = [
            'validationUrl' => $this->decode_string_component($CallBackEndpoints['mpesa_validation_endpoint']),
            'confirmationUrl' => $this->decode_string_component($CallBackEndpoints['mpesa_confirmation_endpoint']),
        ];

        $_mpesa_settings = [];
        foreach ($mpesa_settings as $key => $setting) {
            if ($setting['id'] == $id) { 
                $_mpesa_settings['mpesa_business_short_code'] = $setting['mpesa_business_short_code'];
                $_mpesa_settings['mpesa_consumer_key'] = $setting['mpesa_consumer_key'];
                $_mpesa_settings['mpesa_consumer_secret'] = $setting['mpesa_consumer_secret'];
                $_mpesa_settings['mpesa_pass_key'] = $setting['mpesa_pass_key'];
                $_mpesa_settings['mpesa_api_environment'] = isset($setting['mpesa_api_environment']) 
                    ? (($setting['mpesa_api_environment'] == 1) 
                        ? 'live' 
                        : 'sandbox') 
                    : 'live';
                $_mpesa_settings['status'] = $setting['status'];
            } 
        }  

        if ($_mpesa_settings['status'] == 1) 
        {
            $mpesa_pay = new MpesaPay(
                $_mpesa_settings['mpesa_api_environment'],
                $_mpesa_settings['mpesa_consumer_key'],
                $_mpesa_settings['mpesa_consumer_secret'],
                $_mpesa_settings['mpesa_pass_key']
            );
    
            $response = $mpesa_pay->register_callbacks_url(
                $_mpesa_settings['mpesa_business_short_code'], 
                $callbacks, 
                'completed'
            );
        }
        else {
            $response = false;
        }

        return $response;
    } 
}