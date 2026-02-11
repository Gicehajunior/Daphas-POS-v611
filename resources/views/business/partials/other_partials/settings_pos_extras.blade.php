
<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
            <br>
            <label>
                {!! Form::checkbox('pos_settings[enable_pos_pin]', 1, !empty($pos_settings['enable_pos_pin']), ['class' => 'input-icheck']); !!}
                {{ __('custom.enable_pos_pin') }}
            </label>
        </div>
    </div>
</div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
            <br>
            <label>
                {!! Form::checkbox(
                    'pos_settings[enable_esd_usage]',
                    1,
                    !empty($pos_settings['enable_esd_usage']),
                    ['class' => 'input-icheck']
                ); !!}
                {{ __('custom.enable_esd_printer') }}
            </label>
        </div>
    </div>
</div>

<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
            <br>
            <label>
                {!! Form::checkbox('pos_settings[enable_whatsapp_link]', 1, !empty($pos_settings['enable_whatsapp_link']), ['class' => 'input-icheck']); !!}
                {{ __('custom.enable_whatsapp_link') }}
            </label>
        </div>
    </div>
</div> 
<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
            <br>
            <label>
                {!! Form::checkbox('pos_settings[enable_pos_counters]', 1, !empty($pos_settings['enable_pos_counters']), ['class' => 'input-icheck']); !!}
                {{ __('custom.enable_pos_counters') }}
            </label>
        </div>
    </div>
</div>
