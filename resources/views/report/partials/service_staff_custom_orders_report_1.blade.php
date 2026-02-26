<table class="table table-bordered table-striped custom_order_report_1" id="custom_order_report_1" style="width: 100%;">
    <thead>
        <tr>
            <th>@lang('messages.date')</th>
            <th>@lang('sale.invoice_no')</th>
            <th>@lang('restaurant.service_staff')</th> 
            <th>@lang('sale.product')</th>
            <th>@lang('custom.quantity')</th>
            <th>@lang('custom.units')</th>
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 footer-total text-center">
            <td colspan="4"><strong>@lang('sale.total'):</strong></td>
            <td id="ss_cor1_quantity"></td>
            <td><span id="ss_cor1_unit" data-currency_symbol ="true"></span></td> 
        </tr>
    </tfoot>
</table>