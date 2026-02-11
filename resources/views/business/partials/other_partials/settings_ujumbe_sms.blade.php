<div class="row sms_service_settings @if($sms_service != 'daphasbulksms') hide @endif" data-service="daphasbulksms">
    <div class="col-xs-4">
        <div class="form-group">
            {!! Form::label('daphas_sms_sender_id', 'Sender ID' . '*:') !!}
            {!! Form::text('sms_settings[daphas_sms_sender_id]', !empty($sms_settings['daphas_sms_sender_id']) ? $sms_settings['daphas_sms_sender_id'] : null, ['class' => 'form-control','placeholder' => 'Please enter sender ID', 'id' => 'daphas_sms_sender_id']); !!}
        </div>
    </div>
    <div class="col-xs-6">
        <div class="form-group">
            {!! Form::label('daphas_sms_api_key', 'API Key' . '*:') !!}
            {!! Form::text('sms_settings[daphas_sms_api_key]', !empty($sms_settings['daphas_sms_api_key']) ? $sms_settings['daphas_sms_api_key'] : null, ['class' => 'form-control','placeholder' => 'Please enter api key', 'id' => 'daphas_sms_api_key']); !!}
        </div>
    </div>
    <div class="col-xs-4">
        <div class="form-group">
            {!! Form::label('daphas_sms_from', __('account.from') . ':') !!}
            {!! Form::text('sms_settings[daphas_sms_from]', !empty($sms_settings['daphas_sms_from']) ? $sms_settings['daphas_sms_from'] : null, ['class' => 'form-control','placeholder' => "Please enter sender's contact", 'id' => 'daphas_sms_from']); !!}
        </div>
    </div>
    <div class="col-xs-4">
        <div class="form-group">
            {!! Form::label('daphas_sms_email', 'Email' . '*:') !!}
            {!! Form::text('sms_settings[daphas_sms_email]', !empty($sms_settings['daphas_sms_email']) ? $sms_settings['daphas_sms_email'] : null, ['class' => 'form-control','placeholder' => "Please enter sender's email", 'id' => 'daphas_sms_email']); !!}
        </div>
    </div>
</div>
