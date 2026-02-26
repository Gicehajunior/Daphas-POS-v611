<!-- Modal --> 
<div class="row"> 
    <div class="col-sm-12"> 
        <h3 class="modal-title">@lang('Mpesa Daraja API Settings'):</h3>
        <p>@lang('Configure Mpesa Endpoints settings')</p>
        <p style="color: red; font-weight: bold;">@lang('Please use below Mpesa Developer Settings only if you know what it does...')</p>
        <br/>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            @php
                $shortcode_type = isset($mpesa_setting->shortcode_type) ? (($mpesa_setting->shortcode_type == 1) ? 'Paybill Number' : 'Till Number') : 'live';
            @endphp
            {!! Form::label('shortcode_type', __('Mpesa Shortcode Type') . ':') !!}
            {!! Form::select('shortcode_type', ['1' => 'Paybill', '2' => 'Till Number'], isset($shortcode_type) ? $shortcode_type : null, ['class' => 'form-control', 'id' => 'shortcode_type']); !!}
        </div>
    </div>

    <div style="display: none;" class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_c2b_endpoint', __('Mpesa Daraja C2B API Endpoint') . ':') !!}
            {!! Form::text('mpesa_c2b_endpoint', isset($mpesa_setting->mpesa_c2b_endpoint) ? $mpesa_setting->mpesa_c2b_endpoint : null, ['class' => 'form-control', 'id' => 'mpesa_c2b_endpoint']); !!}
        </div>
    </div>

    <div style="display: none;" class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_b2c_endpoint', __('Mpesa Daraja B2C API Endpoint') . ':') !!}
            {!! Form::text('mpesa_b2c_endpoint', isset($mpesa_setting->mpesa_b2c_endpoint) ? $mpesa_setting->mpesa_b2c_endpoint : null, ['class' => 'form-control', 'id' => 'mpesa_b2c_endpoint']); !!}
        </div>
    </div>

    <div style="display: none;" class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_transaction_status_endpoint', __('Mpesa Daraja Transaction Status API Endpoint') . ':') !!}
            {!! Form::text('mpesa_transaction_status_endpoint', isset($mpesa_setting->mpesa_transaction_status_endpoint) ? $mpesa_setting->mpesa_transaction_status_endpoint : null, ['class' => 'form-control', 'id' => 'mpesa_transaction_status_endpoint']); !!}
        </div>
    </div> 

    <div class="col-lg-6">
        <div class="form-group">
            @php
                $mpesa_api_environment = isset($mpesa_setting->mpesa_api_environment) ? (($mpesa_setting->mpesa_api_environment == 1) ? 'live' : 'sandbox') : 'live';
            @endphp
            {!! Form::label('mpesa_api_environment', __('Mpesa Daraja API Environment') . ':') !!}
            {!! Form::select('mpesa_api_environment', ['1' => 'live', '2' => 'sandbox'], isset($mpesa_api_environment) ? $mpesa_api_environment : null, ['class' => 'form-control', 'id' => 'mpesa_api_environment']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_consumer_key', __('Mpesa Daraja API Consumer Key') . ':') !!}
            {!! Form::text('mpesa_consumer_key', isset($mpesa_setting->mpesa_consumer_key) ? $mpesa_setting->mpesa_consumer_key : null, ['class' => 'form-control', 'id' => 'mpesa_consumer_key']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_consumer_secret', __('Mpesa Daraja API Consumer Secret') . ':') !!}
            {!! Form::text('mpesa_consumer_secret', isset($mpesa_setting->mpesa_consumer_secret) ? $mpesa_setting->mpesa_consumer_secret : null, ['class' => 'form-control', 'id' => 'mpesa_consumer_secret']); !!}
        </div>
    </div> 
    
    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_pass_key', __('Mpesa Daraja API Pass Key') . ':') !!}
            {!! Form::text('mpesa_pass_key', isset($mpesa_setting->mpesa_pass_key) ? $mpesa_setting->mpesa_pass_key : null, ['class' => 'form-control', 'id' => 'mpesa_pass_key']); !!}
            <small style="color: red">IT administrator should request the Pass Key from Safaricom Daraja API.</small>
        </div>
    </div>


    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_business_short_code', __('Mpesa Daraja API Business Shortcode') . ':') !!}
            {!! Form::text('mpesa_business_short_code', isset($mpesa_setting->mpesa_business_short_code) ? $mpesa_setting->mpesa_business_short_code : null, ['class' => 'form-control', 'id' => 'mpesa_business_short_code']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_business_msisdn', __('Mpesa Daraja API Business MSISDN') . ':') !!}
            {!! Form::text('mpesa_business_msisdn', isset($mpesa_setting->mpesa_business_msisdn) ? $mpesa_setting->mpesa_business_msisdn : null, ['class' => 'form-control', 'id' => 'mpesa_business_msisdn']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_party_A', __('Mpesa Daraja API Party A') . ':') !!}
            {!! Form::text('mpesa_party_A', isset($mpesa_setting->mpesa_party_A) ? $mpesa_setting->mpesa_party_A : null, ['class' => 'form-control', 'id' => 'mpesa_party_A']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_party_B', __('Mpesa Daraja API Party B') . ':') !!}
            {!! Form::text('mpesa_party_B', isset($mpesa_setting->mpesa_party_B) ? $mpesa_setting->mpesa_party_B : null, ['class' => 'form-control', 'id' => 'mpesa_party_B']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_initiator_name', __('Mpesa Daraja API Initiator Name') . ':') !!}
            {!! Form::text('mpesa_initiator_name', isset($mpesa_setting->mpesa_initiator_name) ? $mpesa_setting->mpesa_initiator_name : null, ['class' => 'form-control', 'id' => 'mpesa_initiator_name']); !!}
        </div>
    </div> 
    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_confirmation_endpoint', __('Mpesa Daraja API Confirmation URL Endpoint') . ':') !!}
            {!! Form::text('mpesa_confirmation_endpoint', isset($mpesa_setting->mpesa_confirmation_endpoint) ? $mpesa_setting->mpesa_confirmation_endpoint : null, ['class' => 'form-control', 'id' => 'mpesa_confirmation_endpoint']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_validation_endpoint', __('Mpesa Daraja API Validation URL Endpoint') . ':') !!}
            {!! Form::text('mpesa_validation_endpoint', isset($mpesa_setting->mpesa_validation_endpoint) ? $mpesa_setting->mpesa_validation_endpoint : null, ['class' => 'form-control', 'id' => 'mpesa_validation_endpoint']); !!}
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_transaction_validation_param', __('Mpesa Daraja API Validation Parameter') . ':') !!}
            {!! Form::select('mpesa_transaction_validation_param', ['1' => 'Accepted', '2' => 'Rejected'], isset($mpesa_transaction_validation_param) ? $mpesa_transaction_validation_param : null, ['class' => 'form-control', 'id' => 'mpesa_transaction_validation_param']); !!}
            <small style="color: red">Accepted prompts the API to accept the transactions payment with no validation, while Rejected prompts the API to reject the transaction payments if no validation done. </small>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_transaction_result_code', __('Mpesa Daraja API Validation Result Code Parameter') . ':') !!}
            {!! Form::select('mpesa_transaction_result_code', ['0' => 'Success Code', '1' => 'Rejected Code'], isset($mpesa_transaction_result_code) ? $mpesa_transaction_result_code : null, ['class' => 'form-control', 'id' => 'mpesa_transaction_result_code']); !!}
            <small style="color: red"> Success Code shows transaction success status, while Rejected shows transaction rejection status.</small>
        </div>
    </div>

    <div class="col-lg-6"> 
        <div class="form-group">
            <div class="checkbox">
            <br>
            <label>
                {!! Form::checkbox('status', 1,  
                    isset($mpesa_setting->status) ? $mpesa_setting->status : null, 
                [ 'class' => 'input-icheck']); !!} 
                {{ isset($mpesa_setting) 
                    ?  (($mpesa_setting == 1) 
                            ? __('Active') 
                            : __('Inactive')) 
                    : __('Activate') }}
            </label>
            </div>
        </div>
    </div>
</div>  