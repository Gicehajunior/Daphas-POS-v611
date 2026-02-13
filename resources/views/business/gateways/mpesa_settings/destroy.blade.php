<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\App\Http\Controllers\Custom\MpesaCustomController::class, 'destroy'], [$mpesa_setting->id]), 'method' => 'POST', 'id' => 'edit_mpesa_edit_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'mpesa.destroy_shortcode_settings' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="alert alert-warning" role="alert">
          <strong>
            @lang('mpesa.delete_shortcode_warning')
          </strong>
        </div>
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'mpesa.destroy_settings' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->