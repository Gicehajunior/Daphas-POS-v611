<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'mpesa.register_mpesa_endpoints' )</h4>
        </div>

        <div class="modal-body">
            <div class="col-lg-12">
                @include('business.gateways.mpesa_settings.partials.register_mpesa_endpoints')
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" id="{{$mpesa_setting->id}}" data-id="register_mpesa_endpoint_data_id_{{$mpesa_setting->id}}" <?= !isset($mpesa_setting->mpesa_consumer_secret) 
                    ? 'disabled' 
                    : null
                ?>
                class="btn btn-primary register_mpesa_endpoints">
                @lang( 'mpesa.register' )
            </button>
            <button type="button" class="btn btn-default" 
                data-dismiss="modal">@lang( 'messages.close' )</button>
        </div> 
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->  

<script> 
    var register_mpesa_endpoints = document.querySelectorAll('.register_mpesa_endpoints');
    register_mpesa_endpoint = undefined;

    function handle_register_mpesa_request_response(response) { 
        register_mpesa_endpoint.innerHTML = 'Register Endpoints';
        register_mpesa_endpoint.disabled = false; 
        
        try {    
            const message = response.msg ? JSON.parse(response.msg) : response.msg;

            if (message.errorMessage) {  
                toast('error', 9000, message.errorMessage); 
            } else if (message.ResponseDescription) {  
                toast('success', 9000, message.ResponseDescription);
            } 
            else {
                toast('error', 9000, message);
            } 
        } catch (error) { 
            toast('error', 9000, 'Unexpected error occured. Please try again later.');
        } 
    } 

    register_mpesa_endpoints.forEach(_register_mpesa_endpoint => {
        _register_mpesa_endpoint.addEventListener('click', (event) => {
            event.preventDefault();
            register_mpesa_endpoint = _register_mpesa_endpoint
            const clicked_id = event.target.id; 
            const mpesa_confirmation_endpoint = document.getElementById(`mpesa_confirmation_endpoint`);
            const mpesa_validation_endpoint = document.getElementById(`mpesa_validation_endpoint`);
            _register_mpesa_endpoint.innerHTML = 'Registering...';
            _register_mpesa_endpoint.disabled = true;

            $.ajax({
                type: "POST",
                url: "/business/settings/daraja_api/register_mpesa_endpoints",
                data: {
                    mpesa_confirmation_endpoint: mpesa_confirmation_endpoint.value,
                    mpesa_validation_endpoint: mpesa_validation_endpoint.value,
                    setting_id: clicked_id,
                    _token: "{{ csrf_token() }}",
                }, 
                dataType: "json",
                success: function (response) {
                    handle_register_mpesa_request_response(response);
                }
            });
            
        });
    });
    
</script>