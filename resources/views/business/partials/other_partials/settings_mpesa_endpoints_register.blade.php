
<!-- Modal -->
<div class="modal fade" id="MpesaSettingsId" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div style="padding-top: 5%" class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Register Endpoints Via Mpesa Daraja API</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">  
                    <div class="col-lg-12">
                        <div class="form-group">
                            {!! Form::label('mpesa_validation_endpoint', __('Mpesa Daraja Validation URL API Endpoint') . ':') !!}
                            {!! Form::text('pos_settings[mpesa_validation_endpoint]', isset($pos_settings['mpesa_validation_endpoint']) ? $pos_settings['mpesa_validation_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_validation_endpoint']); !!}
                            <small style="color: red" class="small-note">This endpoint might be unregistered. make sure to register this URL with Safaricom PLC Company.</small>
                        </div> 
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            {!! Form::label('mpesa_confirmation_endpoint', __('Mpesa Daraja Confirmation URL API Endpoint') . ':') !!}
                            {!! Form::text('pos_settings[mpesa_confirmation_endpoint]', isset($pos_settings['mpesa_confirmation_endpoint']) ? $pos_settings['mpesa_confirmation_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_confirmation_endpoint']); !!}
                            <small style="color: red" class="small-note">This endpoint might be unregistered. make sure to register this URL with Safaricom PLC Company.</small>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> 
                <button type="button" id="register_mpesa_endpoints" class="btn btn-primary register_mpesa_endpoints" 
                    <?= !isset($pos_settings['mpesa_consumer_secret']) 
                        ? 'disabled' 
                        : null 
                    ?>>
                    Register Endpoints
                </button>
            </div>
        </div>
    </div>
</div>