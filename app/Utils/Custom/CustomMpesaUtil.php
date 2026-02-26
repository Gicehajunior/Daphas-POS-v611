<?php

namespace App\Utils\Custom;

use App\MpesaSetting;
use App\MpesaTransaction;
use App\Utils\Util;
use \MpesaPay;

class CustomMpesaUtil extends Util
{
    /**
     * Returns the mpesa settings for a business
     *
     * @param int $business_id
     * @return array
     */
    public function getMpesaSettings($business_id){
        $mpesa_settings = MpesaSetting::where('business_id', $business_id)->get(); 
    
        return $mpesa_settings;
    }
    
    /**
     * Returns the mpesa settings for a business
     *
     * @param int $business_id
     * @return array
     */
    public function getMpesaSettingsByShortcode($business_id, $mpesa_business_short_code)
    {
        $mpesa_settings = MpesaSetting::where('business_id', $business_id)
                        ->where('mpesa_business_short_code', $mpesa_business_short_code)
                        ->get(); 
        
        return $mpesa_settings;
    }

    /**
     * Returns the mpesa payments for a business
     *
     * @param int $business_id
     * @return array
     */
    public function _getMpesaPaymentsByLimitedTime($business_id, $time) {
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
            ->where('created_at', '>=', \Carbon::now()->subMinutes($time)->toDateTimeString())
            ->where('business_id', '=', $business_id)
            ->where('status', '=', 1) 
            ->get(); 

        return $response;
    }

    /**
     * Returns the mpesa payments for a business
     *
     * @param int $business_id
     * @return array
     */
    public function getMpesaPaymentsByLimitedTime($business_id, $time=null)
    {
        $mpesa_payments = $this->_getMpesaPaymentsByLimitedTime($business_id, $time == null ? $time : 14400); 

        $finalTransactionObject = [];
        $count = 0;
        foreach($mpesa_payments as $key => $mpesa_payment) {
            $count += 1; 
            $transaction = json_decode($mpesa_payment['transaction']);
            foreach($transaction as $transaction_key => $transaction_value) { 
                if ($transaction_key == "TransTime") {   
                    $finalTransactionObject[$count][$transaction_key] = $this->convertMPTTimeToReadableTime($transaction_value);
                }
                else {
                    $finalTransactionObject[$count][$transaction_key] = $transaction_value;
                }
            }

            $finalTransactionObject[$count]['id'] = $mpesa_payment['id'];
            $finalTransactionObject[$count]['business_short_code'] = $mpesa_payment['business_short_code'];
            $finalTransactionObject[$count]['transaction_type'] = $mpesa_payment['transaction_type'];
            $finalTransactionObject[$count]['transaction_id'] = $mpesa_payment['transaction_id'];
            $finalTransactionObject[$count]['transaction_amount'] = $mpesa_payment['transaction_amount'];
            $finalTransactionObject[$count]['business_id'] = $mpesa_payment['business_id'];
            $finalTransactionObject[$count]['status'] = $mpesa_payment['status'];
            $finalTransactionObject[$count]['created_at'] = \Carbon::parse($mpesa_payment['created_at'])->format('Y-m-d H:i:s');
            $finalTransactionObject[$count]['updated_at'] = \Carbon::parse($mpesa_payment['updated_at'])->format('Y-m-d H:i:s');
        }
        
        return $finalTransactionObject;
    }

    public function linkMpesaTransactionsRecursively($mpesa_transaction_nums, $link_value = null) {
        foreach($mpesa_transaction_nums as $key => $mpesa_transaction_num) {
            $mpesa_transaction = MpesaTransaction::find($mpesa_transaction_num);
            $mpesa_transaction->linked = $link_value;
            $mpesa_transaction->update();
        }
    }

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
     * Returns the mpesa payment transaction time formated to readable time 
     *
     * @param int $business_id
     * @return array
     */
    public function convertMPTTimeToReadableTime($timestamp)
    {
        $readableTime = date('Y-m-d H:i:s', $timestamp);
        
        return $readableTime;
    }
}
