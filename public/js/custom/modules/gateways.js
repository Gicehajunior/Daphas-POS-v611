
class Gateway extends Master {
    constructor() { 
        super();
        this._token = null;
        this.transactions = []; 
    }

    initializeMpesa() {
        this.mpesa_transactions_preview();
        this.cancelXhrRequest();
        this.mpesa_settings_preview();
    }

    getToken() {
        this._token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        return this._token;
    }

    _mpesa_transactions_preview() {
        var data = {
            _token: this.getToken()
        };
        var route = '/business/settings/daraja_api/mpesa_transactions_preview';
        var request_method = 'GET';
        
        if (localStorage.getItem('mpesaTransactionsPreviewRouteBlocked') == 1) {
            return;
        }
        this._server(route, request_method, data);
    }

    mpesa_transactions_preview() {
        var mpesa_recent_transactions_btns = undefined;
        mpesa_recent_transactions_btns = document.querySelectorAll('#mpesa-recent-transactions');
        
        mpesa_recent_transactions_btns.forEach(mpesa_recent_transactions_btn => {
            if (document.body.contains(mpesa_recent_transactions_btn)) {
                mpesa_recent_transactions_btn.addEventListener('click', () => {
                    localStorage.setItem('mpesaTransactionsPreviewRouteBlocked', 0);
                    this._mpesa_transactions_preview();
                });
            }
        }); 
    }

    handle_mpesa_transactions_preview_response(response) { 
        if (localStorage.getItem('mpesaTransactionsPreviewRouteBlocked') == 1) {
            return;
        }
        
        try {
            var transactions = response.msg;

            if (response.success == 1) {
                if (transactions) {  
                    this.show_transactions(transactions); 
                    this._mpesa_transactions_preview();
                }  
            } else { 
                this.show_transactions(transactions);
                this._mpesa_transactions_preview(); 
            }
        } catch (error) { 
            this.toast('error', 9000, 'Unexpected error occured. Please try again later. Debug Trace: ' + error);
            this._mpesa_transactions_preview(); 
        }
    }

    show_transactions(transactions) {    
        transactions.forEach(res => {
            var transaction = [res.transaction];
            transaction = JSON.parse(transaction);  

            if (transaction.TransTime) {
                transaction.TransTime = this.convertTimestampToDatetime(transaction.TransTime);
            }

            const MiddleName = (transaction.MiddleName !== undefined) ? transaction.MiddleName : '';
            const LastName = (transaction.LastName !== undefined) ? transaction.LastName : '';

            this.transactions.push({
                Action: `<div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input all_mpesa_transactions_checkboxes" name="${transaction.InvoiceNumber}" id="${transaction.TransID}" type="checkbox" value="" aria-label="Text for screen reader">
                    </label>
                </div>`,
                TransID: transaction.TransID,
                FirstName: transaction.FirstName + ' ' + MiddleName + ' ' + LastName,
                // LastName: transaction.LastName,
                MSISDN: transaction.MSISDN,
                // InvoiceNumber: transaction.InvoiceNumber,
                // BillRefNumber: transaction.BillRefNumber,
                TransAmount: transaction.TransAmount,
                Shortcode: res.business_short_code,
                TransactionType: transaction.TransactionType,
                TransTime: res.created_at
            });  
        });  

        var mpesa_recent_transaction_payments = $('.mpesa_recent_transaction_payments').DataTable(); 
        var mpesa_recent_transaction_payments_TableRowCount = mpesa_recent_transaction_payments.rows().count(); 
        if (this.transactions.length < mpesa_recent_transaction_payments_TableRowCount) {
            mpesa_recent_transaction_payments.clear().draw();
        } 

        if (this.transactions.length > mpesa_recent_transaction_payments_TableRowCount || 
                mpesa_recent_transaction_payments_TableRowCount == 0) {
            $('.mpesa_recent_transaction_payments').DataTable({ 
                        pageLength: 10, 
                destroy: true,
                data: this.transactions,
                "order": [[ 7, 'desc' ]],
                columns: [
                    { data: 'Action', name: 'Action', orderable: false, "searchable": false},
                    { data: 'TransID', name: 'TransID' },
                    { data: 'FirstName', name: 'FirstName' },
                    // { data: 'LastName', name: 'LastName' },
                    { data: 'MSISDN', name: 'MSISDN' },
                    // { data: 'InvoiceNumber', name: 'InvoiceNumber' }, 
                    // { data: 'BillRefNumber', name: 'BillRefNumber'}
                    { data: 'TransAmount', name: 'TransAmount' },
                    { data: 'Shortcode', name: 'Shortcode' },
                    { data: 'TransactionType', name: 'TransactionType' },
                    { data: 'TransTime', name: 'TransTime' }
                ],
                fnInitComplete: function () {
                    const all_mpesa_transactions_checkboxes = document.querySelectorAll('.all_mpesa_transactions_checkboxes');
                    
                    all_mpesa_transactions_checkboxes.forEach(all_mpesa_transactions_checkbox => {
                        if (document.body.contains(all_mpesa_transactions_checkbox)) { 
                            all_mpesa_transactions_checkboxes.forEach(mpesa_transactions_checkboxe => { 
                                mpesa_transactions_checkboxe.checked = false;
                            });
                            all_mpesa_transactions_checkbox.addEventListener('click', (event) => {
                                if (event.target.checked) { 
                                    localStorage.setItem('mpesa_transaction_no', event.target.id); 
                                    const mpesa_recent_transaction_payments_modal_close = document.getElementById('mpesa_recent_transaction_payments_modal_close');
                                    mpesa_recent_transaction_payments_modal_close.click();
                                }
                            }); 
                        }
                    }); 
                }
            });
        }
        else {
            const all_mpesa_transactions_checkboxes = document.querySelectorAll('.all_mpesa_transactions_checkboxes');
            
            all_mpesa_transactions_checkboxes.forEach(mpesa_transactions_checkboxe => { 
                mpesa_transactions_checkboxe.checked = false;
            });
            console.log(`No new transactions found, Transaction Count stands at ${this.transactions.length}. Retrying...`);
        }  
    }

    mpesa_settings_preview() {
        const mpesa_settings_table = document.getElementById('mpesa_settings_table'); 
        if (document.body.contains(mpesa_settings_table)) {  
            if ($('.mpesa_settings_table').length > 0) { 
                const _mpesa_settings_table = $('.mpesa_settings_table').DataTable({ 
                            pageLength: 10,
                    processing: true,
                        pageLength: 10,
                    serverSide: true,
                    aaSorting: [[14, 'desc']],
                    autoWidth: false,
                    ajax: {
                        url: '/business/settings/mpesa',
                        data: function (d) {
                            d._token = $('meta[name="csrf-token"]').attr('content');
                        }
                    },
                    columns: [
                        { data: 'action', name: 'action', orderable: false, "searchable": false},
                        { data: 'mpesa_api_environment', name: 'mpesa_api_environment' },
                        { data: 'mpesa_consumer_key', name: 'mpesa_consumer_key' },
                        { data: 'mpesa_consumer_secret', name: 'mpesa_consumer_secret' },
                        { data: 'mpesa_pass_key', name: 'mpesa_pass_key'},
                        { data: 'mpesa_business_short_code', name: 'mpesa_business_short_code' },
                        { data: 'mpesa_business_msisdn', name: 'mpesa_business_msisdn' },
                        { data: 'mpesa_party_A', name: 'mpesa_party_A' },
                        { data: 'mpesa_party_B', name: 'mpesa_party_B' },
                        { data: 'mpesa_initiator_name', name: 'mpesa_initiator_name' },
                        { data: 'mpesa_confirmation_endpoint', name: 'mpesa_confirmation_endpoint' },
                        { data: 'mpesa_validation_endpoint', name: 'mpesa_validation_endpoint' },
                        { data: 'shortcode_type', name: 'shortcode_type' },
                        { data: 'status', name: 'status' },
                        { data: 'created_at', name: 'created_at', orderable: true, searchable: true },
                    ], 
                    select: true,
                    fnInitComplete: function () {
                        
                    }
                }); 
            }
        }
    }

    handle_mpesa_settings_preview_response(response) {
        // console.log(response);
    }

    convertTimestampToDatetime(timestamp) {
        const inputTimestamp = "20191122063845";

        const year = inputTimestamp.slice(0, 4);
        const month = inputTimestamp.slice(4, 6);
        const day = inputTimestamp.slice(6, 8);
        const hours = inputTimestamp.slice(8, 10);
        const minutes = inputTimestamp.slice(10, 12);
        const seconds = inputTimestamp.slice(12, 14);

        const parsedDate = new Date(`${year}-${month}-${day}T${hours}:${minutes}:${seconds}`);

        const readableTime = `${parsedDate.getFullYear()}-${(parsedDate.getMonth() + 1).toString().padStart(2, '0')}-${parsedDate.getDate().toString().padStart(2, '0')} ${parsedDate.getHours().toString().padStart(2, '0')}:${parsedDate.getMinutes().toString().padStart(2, '0')}:${parsedDate.getSeconds().toString().padStart(2, '0')}`;

        return readableTime;
    }

    toast(status, time, message) {
        if (message != undefined || message != null) {
            toastr.options.newestOnTop = true;
            toastr.options.timeOut = time;
            toastr.options.extendedTimeOut = 0; 
            toastr.options.progressBar = true;
            toastr.options.rtl = false;
            toastr.options.closeButton = true;
            toastr.options.closeMethod = 'fadeOut';
            toastr.options.closeDuration = 300;
            toastr.options.closeEasing = 'swing';
            toastr.options.preventDuplicates = true;
        
            if (status == 'success'){
                toastr.success(message);
            }
            else if(status == 'warning') {
                toastr.warning(message);
            }
            else if(status == 'error'){
                toastr.error(message);
            }
        }
    } 

    _server(route, request_method, postdata) {   
        $.ajax({
            url: route,
            type: request_method,
            data: postdata, 
            success: function (data, textStatus, jqXHR) {    
                if (textStatus == 'success') {  
                    if (this.url.includes('register_mpesa_endpoints')) {
                        (new Gateway()).handle_register_mpesa_request_response(data); 
                    } 
                    else if (this.url.includes('mpesa_transactions_preview')) {  
                        (new Gateway()).handle_mpesa_transactions_preview_response(data); 
                    } 
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {  
                toast('error', 9000, `Unexpected error occured. Please try again later: ${errorThrown}`); 
            }
        }); 
    }

    cancelXhrRequest() {
        const mpesa_recent_transaction_payments_modal_closers = document.querySelectorAll('#mpesa_recent_transaction_payments_modal_close');
        mpesa_recent_transaction_payments_modal_closers.forEach(mpesa_recent_transaction_payments_modal_closer => {
            if (document.body.contains(mpesa_recent_transaction_payments_modal_closer)) {
                mpesa_recent_transaction_payments_modal_closer.addEventListener('click', () => {
                    localStorage.setItem('mpesaTransactionsPreviewRouteBlocked', 1);
                    console.log('Aborted mpesa_transactions_preview XHR Request');
                });
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const mpesa = new Gateway();   
    mpesa.initializeMpesa();
});


