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
            {!! Form::label('mpesa_c2b_endpoint', __('Mpesa Daraja C2B API Endpoint') . ':') !!}
            {!! Form::text('pos_settings[mpesa_c2b_endpoint]', isset($pos_settings['mpesa_c2b_endpoint']) ? $pos_settings['mpesa_c2b_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_c2b_endpoint']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_b2c_endpoint', __('Mpesa Daraja B2C API Endpoint') . ':') !!}
            {!! Form::text('pos_settings[mpesa_b2c_endpoint]', isset($pos_settings['mpesa_b2c_endpoint']) ? $pos_settings['mpesa_b2c_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_b2c_endpoint']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_transaction_status_endpoint', __('Mpesa Daraja Transaction Status API Endpoint') . ':') !!}
            {!! Form::text('pos_settings[mpesa_transaction_status_endpoint]', isset($pos_settings['mpesa_transaction_status_endpoint']) ? $pos_settings['mpesa_transaction_status_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_transaction_status_endpoint']); !!}
        </div>
    </div> 
    <div class="col-lg-6">
        <div class="form-group">
            @php
                $mpesa_api_environment = isset($pos_settings['mpesa_api_environment']) ? (($pos_settings['mpesa_api_environment'] == 1) ? 'live' : 'sandbox') : 'live';
            @endphp
            {!! Form::label('mpesa_api_environment', __('Mpesa Daraja API Environment') . ':') !!}
            {!! Form::select('pos_settings[mpesa_api_environment]', ['1' => 'live', '2' => 'sandbox'], isset($mpesa_api_environment) ? $mpesa_api_environment : null, ['class' => 'form-control', 'id' => 'mpesa_api_environment']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_consumer_key', __('Mpesa Daraja API Consumer Key') . ':') !!}
            {!! Form::text('pos_settings[mpesa_consumer_key]', isset($pos_settings['mpesa_consumer_key']) ? $pos_settings['mpesa_consumer_key'] : '', ['class' => 'form-control', 'id' => 'mpesa_consumer_key']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_consumer_secret', __('Mpesa Daraja API Consumer Secret') . ':') !!}
            {!! Form::text('pos_settings[mpesa_consumer_secret]', isset($pos_settings['mpesa_consumer_secret']) ? $pos_settings['mpesa_consumer_secret'] : null, ['class' => 'form-control', 'id' => 'mpesa_consumer_secret']); !!}
        </div>
    </div> 
    
    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_pass_key', __('Mpesa Daraja API Pass Key') . ':') !!}
            {!! Form::text('pos_settings[mpesa_pass_key]', isset($pos_settings['mpesa_pass_key']) ? $pos_settings['mpesa_pass_key'] : null, ['class' => 'form-control', 'id' => 'mpesa_pass_key']); !!}
        </div>
    </div>


    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_business_short_code', __('Mpesa Daraja API Business Shortcode') . ':') !!}
            {!! Form::text('pos_settings[mpesa_business_short_code]', isset($pos_settings['mpesa_business_short_code']) ? $pos_settings['mpesa_business_short_code'] : null, ['class' => 'form-control', 'id' => 'mpesa_business_short_code']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_business_msisdn', __('Mpesa Daraja API Business MSISDN') . ':') !!}
            {!! Form::text('pos_settings[mpesa_business_msisdn]', isset($pos_settings['mpesa_business_msisdn']) ? $pos_settings['mpesa_business_msisdn'] : null, ['class' => 'form-control', 'id' => 'mpesa_business_msisdn']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_party_A', __('Mpesa Daraja API Party A') . ':') !!}
            {!! Form::text('pos_settings[mpesa_party_A]', isset($pos_settings['mpesa_party_A']) ? $pos_settings['mpesa_party_A'] : null, ['class' => 'form-control', 'id' => 'mpesa_party_A']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_party_B', __('Mpesa Daraja API Party B') . ':') !!}
            {!! Form::text('pos_settings[mpesa_party_B]', isset($pos_settings['mpesa_party_B']) ? $pos_settings['mpesa_party_B'] : null, ['class' => 'form-control', 'id' => 'mpesa_party_B']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_initiator_name', __('Mpesa Daraja API Initiator Name') . ':') !!}
            {!! Form::text('pos_settings[mpesa_initiator_name]', isset($pos_settings['mpesa_initiator_name']) ? $pos_settings['mpesa_initiator_name'] : null, ['class' => 'form-control', 'id' => 'mpesa_initiator_name']); !!}
        </div>
    </div> 
    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_confirmation_endpoint', __('Mpesa Daraja API Confirmation URL Endpoint') . ':') !!}
            {!! Form::text('pos_settings[mpesa_confirmation_endpoint]', isset($pos_settings['mpesa_confirmation_endpoint']) ? $pos_settings['mpesa_confirmation_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_confirmation_endpoint']); !!}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            {!! Form::label('mpesa_validation_endpoint', __('Mpesa Daraja API Validation URL Endpoint') . ':') !!}
            {!! Form::text('pos_settings[mpesa_validation_endpoint]', isset($pos_settings['mpesa_validation_endpoint']) ? $pos_settings['mpesa_validation_endpoint'] : null, ['class' => 'form-control', 'id' => 'mpesa_validation_endpoint']); !!}
        </div>
    </div>

    <div class="col-lg-6"> 
        <div class="form-group">
            <div class="checkbox">
            <br>
            <label>
                {!! Form::checkbox('pos_settings[disable_mpesa_api]', 1,  
                    !empty($pos_settings['disable_mpesa_api']) , 
                [ 'class' => 'input-icheck']); !!} {{ __( 'Disable Mpesa Daraja API' ) }}
            </label>
            </div>
        </div>
    </div>
</div>  