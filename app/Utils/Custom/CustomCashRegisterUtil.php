<?php

namespace App\Utils\Custom;

use App\Utils\TransactionUtil;
use App\Transaction;

use DB;

/**
 * CashRegisterController
 * 
 * @author Giceha(https://github.com/Gicehajunior)
 */
trait CustomCashRegisterUtil {

    public $transactionRegisterUtil;

    public function __construct(TransactionUtil $transactionRegisterUtil)
    {
        $this->transactionRegisterUtil = $transactionRegisterUtil;
    }

    /** 
     * @return array
     */
    public function compute_commission_totals($business_id, $filters = [], $type) { 
        $start_date = $filters['start_date'];
        $end_date = $filters['end_date'];

        $transactions = Transaction::where('transactions.business_id', $business_id); 

        if (!empty($start_date) && !empty($end_date)) {
            $transactions->whereBetween(DB::raw('date(transactions.transaction_date)'), [$start_date, $end_date]);
        }

        //Filter by the location
        if (!empty($location_id)) {
            $transactions->where('transactions.location_id', $location_id);
        }

        if (!empty($commission_agent)) {
            $transactions->where('transactions.commission_agent', $commission_agent);
        }

        $transactions = $transactions->get();

        $commission_total_amount = 0; 
        foreach ($transactions as $transactionKey => $transactionValue) {
            $commission_total_amount += $transactionValue['commission_amount']; 
        } 

        return $commission_total_amount;
    } 

    /** 
     * @return array
     */
    public function compute_expenses($business_id, $filters = [], $type) {  
        $all_expenses = $this->transactionRegisterUtil->getExpenseReport(
            $business_id,
            [
                'start_date' => isset($filters['start_date'])
                    ?   $filters['start_date']
                    :   null,
                'end_date' => isset($filters['end_date'])
                    ?   $filters['end_date']
                    :   null,
                'register_details' => isset($filters['register_details']) 
                    ?   $filters['register_details'] 
                    :   null
            ],
            $type = 'by_category' 
        ); 
 
        $total_expenses = 0; 
        foreach ($all_expenses as $key => $value) { 
            $total_expenses += $value['total_expense'];
        }     
        
        return ['expenses' => $all_expenses, 'total_expenses' => $total_expenses];
    } 
}

