<?php 
use App\Http\Controllers\Custom\StockIssuanceCustomController;
use App\Http\Controllers\Custom\BusinessCustomController;
use App\Http\Controllers\Custom\MpesaCustomController; 
use App\Http\Controllers\Custom\EmailCustomController; 
use App\Http\Controllers\Custom\StripeCustomController; 
use App\Http\Controllers\Custom\SellPosCustomController;
use App\Http\Controllers\SellPosController;

Route::get('mpesa-settings/_register_enpoints/{id}', [MpesaCustomController::class, '_register_enpoints']);
Route::post('mpesa-settings/register_enpoints/{id}', [MpesaCustomController::class, 'register_enpoints']);

//Stock Issuances
Route::get('stock-issuances/print/{id}', [StockIssuanceCustomController::class, 'printInvoice']);
Route::post('stock-issuances/update-status/{id}', [StockIssuanceCustomController::class, 'updateStatus']);
Route::resource('stock-issuances', StockIssuanceCustomController::class);

// Stripe Resource
Route::get('/stripe-settings', [StripeCustomController::class, 'index']);

// Email Resource
Route::get('/email-settings', [EmailCustomController::class, 'index']);

Route::get('/reports/product-sell-summary-report', 'ReportController@product_sell_summary_report');
Route::get('/reports/product-expense-summary-report', 'ReportController@product_expense_summary_report');

Route::post('/pos/update_kra_transaction_invoice_token', 'SellPosController@update_kra_transaction_invoice_token');
Route::post('/pos/trash_bin_transaction/{id}', 'SellPosController@destroy'); 

// Business settings 
Route::get('/business/settings/mpesa', [MpesaCustomController::class, 'index']);
Route::get('/business/settings/mpesa', [MpesaCustomController::class, 'index']); 
Route::resource('/mpesa-settings', MpesaCustomController::class);
Route::post('/pos/getRawMpesaTransactions', [SellPosController::class, 'getRawMpesaTransactions']);
Route::get('mpesa-settings/edit/{id}', [MpesaCustomController::class, 'edit']);
Route::post('mpesa-settings/update/{id}', [MpesaCustomController::class, 'update']);
Route::get('mpesa-settings/_destroy/{id}', [MpesaCustomController::class, '_destroy']);
Route::post('mpesa-settings/destroy/{id}', [MpesaCustomController::class, 'destroy']);
Route::post('/business/settings/daraja_api/register_mpesa_endpoints', [BusinessCustomController::class, 'register_mpesa_endpoints']); 
Route::get('/business/settings/daraja_api/mpesa_transactions_preview', [BusinessCustomController::class, 'mpesa_transactions_preview']);

// POS
Route::post('/pos/auth/checkPinIfEnabled', [SellPosCustomController::class, 'checkPinIfEnabled']);
Route::post('/pos/auth/pin', [SellPosCustomController::class, 'posPinAuthentication']);

// Download Resource
Route::get('/download/', [BusinessCustomController::class, 'downloadESDAClassApiBridgerScriptLibrary']);