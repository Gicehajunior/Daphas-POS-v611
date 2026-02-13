<?php

namespace App\Http\Controllers\Custom;

use App\Business;
use App\Currency;
use App\Notifications\TestEmailNotification;
use App\System;
use App\TaxRate;
use App\Unit;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\Custom\CustomBusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class BusinessCustomController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BusinessCustomController
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new business/business as well as their
    | validation and creation.
    |
    */

    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $restaurantUtil;

    protected $customBusinessUtil;

    protected $moduleUtil;

    protected $mailDrivers;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, CustomBusinessUtil $customBusinessUtil, RestaurantUtil $restaurantUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->customBusinessUtil = $customBusinessUtil;
        $this->moduleUtil = $moduleUtil; 
    }

    public function downloadESDAClassApiBridgerScriptLibrary() {
        $file = public_path('api-lib/aclas_api.php');

        return response()->download($file, "Aclas-API-ESD-Bridger.php");
    }


    /**
     * registers the mpesa mpesa endpoints.
     *
     * @return \Illuminate\Http\Response
     */
    public function register_mpesa_endpoints(Request $request) {
        try {
            $endpoint_request = $request->input();

            if (!empty($endpoint_request['mpesa_confirmation_endpoint']) ||
                !empty($endpoint_request['mpesa_validation_endpoint'])) {
                $response = $this->customBusinessUtil->register_mpesa_endpoints($endpoint_request, $endpoint_request['setting_id']);
                
                if ($response == false) {
                    $output = [
                        'success' => 0,
                        'msg' => json_encode(['errorMessage' => __('Mpesa Daraja API disabled. Please enable it in the settings and try again.')]),
                    ];
                }
                else {
                    $output = [
                        'success' => 1,
                        'msg' => $response,
                    ];
                }
            } else {
                $response = json_encode(['errorMessage' => __('Mpesa confirmation url and validation required.')]); 
                
                $output = [
                    'success' => 0,
                    'msg' => $response,
                ];
            }   
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
            $this->_mpesa_error_logs("mpesa_error", $output['msg'], 'register_mpesa_endpoints');
        }

        return $output;
    }

    /**
     * gets all the transactions confirmation messages sent by mpesa 
     * via the confirmation and validation endpoints/routes.
     *
     * @return \Illuminate\Http\Response
     */
    public function mpesa_transactions_preview(Request $request) { 
        try { 
            $business_id = request()->session()->get('user.business_id');  
            $MpesaSettings = MpesaSetting::where('business_id', '=', $business_id)
                ->get();

            $mpesa_business_short_codes_array = [];
            foreach ($MpesaSettings as $key => $row) {
                if (!empty($row['mpesa_business_short_code'])) {
                    array_push($mpesa_business_short_codes_array, $row['mpesa_business_short_code']);
                }
            }
            
            $response = MpesaTransaction::whereIn('business_short_code', array_values($mpesa_business_short_codes_array))
                ->where('created_at', '>=', Carbon::now()->subMinutes(14400)->toDateTimeString())
                ->where('business_id', '=', $business_id)
                ->where('status', '=', 1) 
                ->where('linked', '=', 0)
                ->get(); 

            $output = [
                'success' => 1,
                'msg' => $response,
            ];  
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
            $this->_mpesa_error_logs("mpesa_error", $output['msg'], 'mpesa_transactions_preview');
        }

        return $output;
    }

    /**
     * writes off all the errors that are catched by the 
     * mpesa callback functions
     *
     * @return \Illuminate\Http\Response
     */
    public function _mpesa_error_logs($file_error_log_name, $output, $callbackfunc) {  
        $logEntry = date("Y-m-d H:i:s") . ': From ' . $callbackfunc . ' function: Debug Error, '. ($output ? $output : 'No response from ' . $callbackfunc . ' mpesa callback');
        
        $filePath = public_path() . '/logs//' . $file_error_log_name . '_logs.txt';
        
        $file = fopen($filePath, 'a');
        
        if ($file) { 
            fwrite($file, $logEntry . PHP_EOL); 
            fclose($file); 
        }
    }

    /**
     * validates any of the mpesa transaction request done 
     * via Daraja API.
     */
    public function validate_payment_request()  {
        try { 
            header("Content-Type: application/json");

            // Data from Mpesa
            $mpesaResponse = file_get_contents('php://input');

            // param by default
            $transaction_validation_param = "Accepted";
            $transaction_result_code = 0;

            $MpesaSettings = MpesaSetting::where('status', '=', 1)
                    ->get();

            foreach ($MpesaSettings as $key => $row) {
                if (!empty($row['mpesa_business_short_code'])) {
                    if ($row['mpesa_business_short_code'] == json_decode($mpesaResponse)->BusinessShortCode) {
                        $transaction_validation_param = $row['mpesa_transaction_validation_param'] ? $row['mpesa_transaction_validation_param'] : "Accepted";
                        $transaction_result_code = $row['mpesa_transaction_result_code'] ? $row['mpesa_transaction_result_code'] : 0;

                    }
                }
            }

            $response = $this->validate_mpesa_confirmation_callback($transaction_result_code, $transaction_validation_param); 

            $output = [
                'success' => 1,
                'msg' => $response,
            ];
            $this->_mpesa_error_logs("mpesa_error", $output['msg'], 'confirm_payment_request');
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
            $this->_mpesa_error_logs("mpesa_error", $output['msg'], 'validate_payment_request');
        } 
    }

    /**
     * confirms any of the Mpesa transaction request via Daraja API.
     */
    public function confirm_payment_request()  {
        try { 
            header("Content-Type: application/json");
            $response = [
                'ResultCode' => 0,
                'ResultDesc' => 'Confirmation Received Successfully'
            ];

            // Data from Mpesa
            $mpesaResponse = file_get_contents('php://input');

            if (!empty($mpesaResponse)) { 
                $content = json_encode($mpesaResponse, true);
                
                $MpesaSettings = MpesaSetting::where('status', '=', 1)
                    ->get();

                $mpesa_business_short_codes_array = [];
                foreach ($MpesaSettings as $key => $row) {
                    if (!empty($row['mpesa_business_short_code'])) {
                        array_push($mpesa_business_short_codes_array, ['business_id' => $row["business_id"], 'mpesa_business_short_code' => $row['mpesa_business_short_code']]);
                    }
                }
                
                $this->transactionUtil->save_mpesa_confirmation_callback_response($mpesaResponse, $mpesa_business_short_codes_array);
                
                $output = [
                    'success' => 1,
                    'msg' => json_decode($content),
                ];
            }
            else {
                $output = [
                    'success' => 0,
                    'msg' => null,
                ];
                
                $this->_mpesa_error_logs("mpesa_error", $output['msg'], 'confirm_payment_request');
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
            
            $this->_mpesa_error_logs("mpesa_error", $output['msg'], 'confirm_payment_request');
        }

        return $output;
    }
}
