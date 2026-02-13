@extends('layouts.app')
@section('title', __( 'mpesa.mpesa_settings' ))

@section('content')

<style>
    table.dataTable tbody td {
        word-break: break-word; word-break: break-all; white-space: normal;
    }
</style>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'mpesa.mpesa_settings' )
        <small>@lang( 'mpesa.manage_all_your_mpesa_settings' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'mpesa.all_your_mpesa_settings' )])
        @can('mpesa_settings.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                            data-href="{{action([\App\Http\Controllers\Custom\MpesaCustomController::class, 'create'])}}" 
                            data-container=".mpesa_setting_create_modal">
                            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
        @endcan
        @can('mpesa_settings.view') 
            <div class="table-responsive">
                <table class="table table-bordered table-striped wrap mpesa_settings_table" 
                    id="mpesa_settings_table">
                    <thead>
                        <tr> 
                            <th>@lang('messages.action')</th>
                            <th>@lang('mpesa.mpesa_api_environment')</th>
                            <th>@lang('mpesa.mpesa_consumer_key')</th>
                            <th>@lang('mpesa.mpesa_consumer_secret')</th>
                            <th>@lang('mpesa.mpesa_pass_key')</th>
                            <th>@lang('mpesa.mpesa_business_short_code')</th>
                            <th>@lang('mpesa.mpesa_business_msisdn')</th>
                            <th>@lang('mpesa.mpesa_party_A')</th>
                            <th>@lang('mpesa.mpesa_party_B')</th>
                            <th>@lang('mpesa.mpesa_initiator_name')</th>
                            <th>@lang('mpesa.mpesa_confirmation_endpoint')</th> 
                            <th>@lang('mpesa.mpesa_validation_endpoint')</th> 
                            <th>@lang('mpesa.shortcode_type')</th> 
                            <th>@lang('mpesa.status')</th> 
                            <th>@lang('mpesa.created_at')</th> 
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade register_mpesa_endpoints_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade mpesa_setting_create_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div> 
    <div class="modal fade mpesa_setting_edit_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div> 
    <div class="modal fade mpesa_setting_delete_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>  
</section>
<!-- /.content -->
@endsection

@section('javascript')
<script src="{{ asset('js/custom/modules/gateways.js?v=' . $asset_v) }}"></script>
@endsection
