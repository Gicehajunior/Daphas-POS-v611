
<!-- Modal -->
<div style = "overflow-y : scroll; z-index: 2000" class="modal fade" id="mpesa_recent_transactions_modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Mpesa Live Transactions Preview</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">  
                <table class="table table-bordered table-striped mpesa_recent_transaction_payments" 
                    id="mpesa_recent_transaction_payments">
                    <thead>
                        <tr> 
                            <th>@lang('Check')</th>
                            <th>@lang('T.ID')</th>
                            <th>@lang('First Name')</th> 
                            <th>@lang('MSISDN')</th> 
                            <th>@lang('Amount')</th>
                            <th>@lang('Business ShortCode')</th>
                            <th>@lang('Transaction Type')</th>
                            <th>@lang('Time')</th> 
                        </tr>
                    </thead>
                </table>  
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm mpesa_recent_transaction_payments_modal_close" 
                    id="mpesa_recent_transaction_payments_modal_close" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
