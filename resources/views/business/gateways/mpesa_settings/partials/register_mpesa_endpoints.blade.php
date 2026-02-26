<!-- Modal --> 
<div class="row"> 
    <div class="col-sm-12"> 
        <h3 class="modal-title">@lang('Mpesa Daraja API Endpoints Settings'):</h3>
        <p>@lang('Configure Mpesa Endpoints')</p>
        <br/>
    </div>

    <div class="form-group">
        <div class="row"> 
            <div class="col-lg-12">
                <div class="form-group">
                    {!! Form::label('mpesa_confirmation_endpoint', __('Mpesa Daraja Confirmation URL API Endpoint') . ':') !!}
                    {!! Form::text('mpesa_confirmation_endpoint', isset($mpesa_setting->mpesa_confirmation_endpoint) ? $mpesa_setting->mpesa_confirmation_endpoint : null, ['class' => 'form-control mpesa_confirmation_endpoint', 'id' => 'mpesa_confirmation_endpoint']); !!}
                    <small style="color: red" class="small-note">This endpoint might be unregistered. make sure to register this URL with Safaricom PLC Company.</small>
                </div> 
            </div>
            
            <div class="col-lg-12">
                <div class="form-group">
                    {!! Form::label('mpesa_validation_endpoint', __('Mpesa Daraja Validation URL API Endpoint') . ':') !!}
                    {!! Form::text('mpesa_validation_endpoint', isset($mpesa_setting->mpesa_validation_endpoint) ? $mpesa_setting->mpesa_validation_endpoint : null, ['class' => 'form-control mpesa_validation_endpoint', 'id' => 'mpesa_validation_endpoint']); !!}
                    <small style="color: red" class="small-note">This endpoint might be unregistered. make sure to register this URL with Safaricom PLC Company.</small>
                </div> 
            </div>
        </div>
    </div>
    
</div>  