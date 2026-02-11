@if( $contact->type == 'customer' )  
    <div class="d-inline pr-3">   
        @if((($contact->total_invoice - ($contact->invoice_received + $contact->opening_balance_paid)) + $contact->opening_balance) > 0)
            <a style="margin-left: 5px" href="{{action([\App\Http\Controllers\TransactionPaymentController::class, 'getPayContactDue'], [$contact->id])}}?type=sell" class="pay_purchase_due tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm pull-right tw-m-2"><i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang("contact.pay_due_amount")</a>
        @endif
    </div>
@endif