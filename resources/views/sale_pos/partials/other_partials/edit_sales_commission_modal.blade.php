
<!-- Edit Shipping Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="editCommissionInfo">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add/Edit Sales Commission</h4>
			</div>
			<div class="modal-body">
                <div class="row">
                    @if(!empty($commission_agent))
                        @php
                            $is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
                        @endphp
                        <style>
                            .select2 {
                                width: 100%;
                            }
                        </style>
                        <div class="col-md-6"> 
                            {!! Form::label('commission_agent', 'Select Commission Agent' . ':') !!}
                            {!! Form::select('commission_agent', $commission_agent, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent'), 'id' => 'commission_agent', 'required' => $is_commission_agent_required]); !!}
                        </div>
                    @endif  
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('commission_amount', 'Commission Amount' . ':') !!}
                            {!! Form::text('commission_amount', @num_format(0.00), ['class' => 'form-control input_number commission_amount', 'placeholder' => 'Please enter commission amount' ]); !!}
                        </div>
                    </div> 
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('cmmsn_percent', __( 'lang_v1.cmmsn_percent' ) . ':') !!}
                            {!! Form::text('cmmsn_percent', @num_format(0.00), ['class' => 'form-control input_number cmmsn_percent', 'placeholder' => __( 'lang_v1.cmmsn_percent' ), 'required' ]); !!}
                        </div>
                    </div>  
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('max_sales_discount_percent', __( 'lang_v1.max_sales_discount_percent' ) . ':') !!} @show_tooltip(__('lang_v1.max_sales_discount_percent_help'))
                            {!! Form::text('max_sales_discount_percent', @num_format(0), ['class' => 'form-control input_number max_sales_discount_percent', 'placeholder' => __( 'lang_v1.max_sales_discount_percent' ) ]); !!}
                        </div>
                    </div> 
                </div> 
                <div class="row">
                    <div style="display: none"  class="col-md-6"> 
                        <div class="form-group">
                            {!! Form::label('selected_commission_receiver', 'Commission Receiver' ) !!}
                            {!! Form::text('selected_commission_receiver', '', ['class' => 'form-control selected_commission_receiver', 'placeholder' => 'N/Applied', 'disabled' => 'disabled']); !!}
                        </div>
                    </div> 
                </div>
			</div>
			<div class="modal-footer">
				<button style="display: none" type="button" class="btn btn-primary" id="editCommissionInfoUpdate">@lang('messages.update')</button>
			    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.update')</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

