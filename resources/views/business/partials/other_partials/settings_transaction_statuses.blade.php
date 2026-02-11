<div class="row"> 
        <div class="col-sm-12">
            <h4>@lang('Transaction Statuses'):</h4>
            <p>@lang('Customize Transaction statuses.')</p>
            <br/>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('ordered', __('Ordered Status') . ':') !!}
                {!! Form::text('pos_settings[ordered]', isset($pos_settings['ordered']) ? $pos_settings['ordered'] : null, ['class' => 'form-control', 'id' => 'ordered']); !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('packed', __('Packed Status') . ':') !!}
                {!! Form::text('pos_settings[packed]', isset($pos_settings['packed']) ? $pos_settings['packed'] : null, ['class' => 'form-control', 'id' => 'packed']); !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('shipped', __('Shipped Status') . ':') !!}
                {!! Form::text('pos_settings[shipped]', isset($pos_settings['shipped']) ? $pos_settings['shipped'] : null, ['class' => 'form-control', 'id' => 'shipped']); !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('delivered', __('Delivered Status') . ':') !!}
                {!! Form::text('pos_settings[delivered]', isset($pos_settings['delivered']) ? $pos_settings['delivered'] : null, ['class' => 'form-control', 'id' => 'delivered']); !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('cancelled', __('Cancelled Status') . ':') !!}
                {!! Form::text('pos_settings[cancelled]', isset($pos_settings['cancelled']) ? $pos_settings['cancelled'] : null, ['class' => 'form-control', 'id' => 'cancelled']); !!}
            </div>
        </div> 
    </div>  