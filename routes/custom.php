<?php 
use App\Http\Controllers\Custom\StockIssuanceCustomController;

//Stock Issuances
Route::get('stock-issuances/print/{id}', [StockIssuanceCustomController::class, 'printInvoice']);
Route::post('stock-issuances/update-status/{id}', [StockIssuanceCustomController::class, 'updateStatus']);
Route::resource('stock-issuances', StockIssuanceCustomController::class);

// Download Resource
Route::get('/download/', [App\Http\Controllers\Custom\BusinessCustomController::class, 'downloadESDAClassApiBridgerScriptLibrary']);