<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\Custom\MpesaCustomController::class, 'update'], [$mpesa_setting->id]), 'method' => 'post', 'id' => 'edit_mpesa_edit_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'mpesa.edit_shortcode' )</h4>
    </div>

    <div class="modal-body">
      @include('business.gateways.mpesa_settings.partials.edit_mpesa_daraja_api_settings')
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->