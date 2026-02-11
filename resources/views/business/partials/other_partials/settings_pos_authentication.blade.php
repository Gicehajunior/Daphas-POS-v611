<div class="row"> 
    <div class="col-sm-12">
        <h4>@lang('POS Authentication Settings'):</h4>
        <p>@lang('Configure POS PIN settings')</p>
        <br/>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('pos_pin', __('Auth POS PIN') . ':') !!}
            {!! Form::text('pos_settings[pos_pin]', isset($pos_settings['pos_pin']) ? $pos_settings['pos_pin'] : null, ['class' => 'form-control', 'id' => 'pos_pin']); !!}
            <small>N/B: Should be pin of 5 total digits.</small>
        </div>
    </div> 

    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('pos_lock_after_duration', __('custom.pos_lock_after_duration') . ':') !!}
            {!! Form::select('pos_settings[pos_lock_after_duration]', [
                '1'  => __('custom.pos_lock_immediately'),
                '10' => __('custom.pos_lock_after_ten_seconds'),
                '15' => __('custom.pos_lock_after_fifteen_seconds'),
                '20' => __('custom.pos_lock_after_twenty_seconds'),
                '30' => __('custom.pos_lock_after_thirty_seconds'),
                '60' => __('custom.pos_lock_after_one_minute'),
                '300' => __('custom.pos_lock_after_five_minutes'),
                '900' => __('custom.pos_lock_after_fifteen_minutes'),
                '1800' => __('custom.pos_lock_after_thirty_minutes'),
                '2700' => __('custom.pos_lock_after_fortyfive_minutes'),
                '3600' => __('custom.pos_lock_after_one_hour')
            ], isset($pos_settings['pos_lock_after_duration']) ? $pos_settings['pos_lock_after_duration'] : 1, ['class' => 'form-control', 'style' => 'width: 100%;' ]); !!}
        </div>
    </div>
</div>  