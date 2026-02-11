<div class="row sms_service_settings @if($sms_service != 'gurutech_sms') hide @endif" data-service="gurutech_sms">
    <div class="col-xs-4">
        <div class="form-group">
            {!! Form::label('gurutech_sms_sender_id', 'Sender ID' . '*:') !!}
            {!! Form::text('sms_settings[gurutech_sms_sender_id]', !empty($sms_settings['gurutech_sms_sender_id']) ? $sms_settings['gurutech_sms_sender_id'] : null, ['class' => 'form-control','placeholder' => 'Please enter sender ID', 'id' => 'gurutech_sms_sender_id']); !!}
        </div>
    </div>
    <div class="col-xs-6">
        <div class="form-group col-xs-9"> 
            {!! Form::label('gurutech_sms_api_key', 'API Key' . ':') !!}
            {!! Form::text('sms_settings[gurutech_sms_api_key]', !empty($sms_settings['gurutech_sms_api_key']) ? $sms_settings['gurutech_sms_api_key'] : null, ['class' => 'form-control','placeholder' => 'Please enter api key', 'id' => 'gurutech_sms_api_key']); !!}
            <small>API key can be optional</small>  
        </div>
        <div style="margin-top: 2.9rem" class="form-group col-xs-3">
            <button type="button" id="update_gurutech_api_key" class="btn btn-primary btn-sm update_gurutech_api_key">Create/Update API Key</button>
        </div> 
    </div>
    <div class="col-xs-4">
        <div class="form-group">
            {!! Form::label('gurutech_sms_userid', __('account.from') . '(User ID):') !!}
            {!! Form::text('sms_settings[gurutech_sms_userid]', !empty($sms_settings['gurutech_sms_userid']) ? $sms_settings['gurutech_sms_userid'] : null, ['class' => 'form-control','placeholder' => "Please enter user ID", 'id' => 'gurutech_sms_userid']); !!}
        </div>
    </div>
    <div class="col-xs-6">
        <div class="form-group">
            {!! Form::label('gurutech_sms_password', 'Password' . '*:') !!}
            {!! Form::text('sms_settings[gurutech_sms_password]', !empty($sms_settings['gurutech_sms_password']) ? $sms_settings['gurutech_sms_password'] : null, ['class' => 'form-control','placeholder' => "Please enter sms account password", 'id' => 'gurutech_sms_password']); !!}
        </div>
    </div>
</div>
