@extends('layouts.app')
@section('title', __('custom.stock_issuances'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('custom.stock_issuances')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('custom.all_stock_issuances')])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\App\Http\Controllers\Custom\StockIssuanceCustomController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="stock_issuance_table">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('purchase.ref_no')</th>
                        <th>@lang('custom.business_location')</th>
                        <th>@lang('custom.department_from')</th>
                        <th>@lang('custom.department_to')</th>
                        <th>@lang('sale.status')</th>
                        <th>@lang('lang_v1.shipping_charges')</th>
                        <th>@lang('stock_adjustment.total_amount')</th>
                        <th>@lang('purchase.additional_notes')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>

@include('stock_issuance.partials.update_status_modal')

<section id="receipt_section" class="print_section"></section>

<!-- /.content -->
@stop
@section('javascript')
	<script src="{{ asset('js/custom/modules/stock_issuance.js?v=' . $asset_v) }}"></script>
@endsection