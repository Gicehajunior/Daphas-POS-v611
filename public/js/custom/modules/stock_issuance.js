class StockIssuance {
    constructor () {
        // LOGIC APPLICABLE
    }

    initializeSI() {
        this.search_product_for_stock_issuance();
        this.ui_events();  
        this.onChangeEvents();  
        this.stock_issuance_status_onclick_event();
        this.update_stock_issuance_status_form_onsubmit();
        this.onview_modal();
    }

    //Add products
    search_product_for_stock_issuance() {
        if ($('#search_product_for_stock_issuance').length > 0) { 
            $('#search_product_for_stock_issuance')
                .autocomplete({
                    source: function(request, response) {
                        $.getJSON(
                            '/products/list',
                            { 
                                location_id: $('#location_id').val(), 
                                department_id: $('#department_id').val(), 
                                term: request.term 
                            },
                            response
                        );
                    },
                    minLength: 2,
                    response: function(event, ui) {
                        if (ui.content.length == 1) {
                            ui.item = ui.content[0];
                            if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
                                $(this)
                                    .data('ui-autocomplete')
                                    ._trigger('select', 'autocompleteselect', ui);
                                $(this).autocomplete('close');
                            }
                        } else if (ui.content.length == 0) {
                            swal(LANG.no_products_found);
                        }
                    },
                    focus: function(event, ui) {
                        if (ui.item.qty_available <= 0) {
                            return false;
                        }
                    },
                    select: function(event, ui) {
                        if (ui.item.qty_available > 0) {
                            $(this).val(null);
                            (new StockIssuance()).stock_issuance_product_row(ui.item.variation_id);
                        } else {
                            alert(LANG.out_of_stock);
                        }
                    },
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                if (item.qty_available <= 0) {
                    var string = '<li class="ui-state-disabled">' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    string += ' (' + item.sub_sku + ') (Out of stock) </li>';
                    return $(string).appendTo(ul);
                } else if (item.enable_stock != 1) {
                    return ul;
                } else {
                    var string = '<div>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    string += ' (' + item.sub_sku + ') </div>';
                    return $('<li>')
                        .append(string)
                        .appendTo(ul);
                }
            };
        }
    }

    ui_events() { 
        $('select#location_id').change(function() { 
            // jquery request departments for the respective choosen location:
            $.ajax({
                url: `/business/location/get-departments?location_id=${$('#location_id').val()}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) { 
                    if (response && response.options && $('#location_id').val()) { 
                        $('#department_id').removeAttr('disabled');
                        $('#issuance_department_id').removeAttr('disabled');
                        $('#department_id').empty();
                        $('#issuance_department_id').empty();
                        $('#department_id').append($('<option>', {
                            value: '',
                            text: 'Select department'
                        }));

                        $('#issuance_department_id').append($('<option>', {
                            value: '',
                            text: 'Select department'
                        }));
                        
                        $.each(response.options, function(key, option) {  
                            $('#department_id').append($('<option>', {
                                value: key,
                                text: option
                            }));

                            $('#issuance_department_id').append($('<option>', {
                                value: key,
                                text: option
                            }));
                        });
                    } 
                    else {
                        $('#department_id').attr('disabled', 'disabled');
                        $('#issuance_department_id').attr('disabled', 'disabled');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        $('select#department_id').change(function() {
            if ($(this).val()) {
                $('#search_product_for_stock_issuance').removeAttr('disabled');
            } else {
                $('#search_product_for_stock_issuance').attr('disabled', 'disabled');
            }
            
            $('table#stock_issuance_product_table tbody').html('');
            $('#product_row_index').val(0);
            (new StockIssuance()).update_table_total();
        });

        $(document).on('change', 'input.product_quantity', function() {
            (new StockIssuance()).update_table_row($(this).closest('tr'));
        });

        $(document).on('change', 'input.product_unit_price', function() {
            (new StockIssuance()).update_table_row($(this).closest('tr'));
        });

        $(document).on('click', '.remove_product_row', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    $(this)
                        .closest('tr')
                        .remove();
                    (new StockIssuance()).update_table_total();
                }
            });
        });

        //Date picker
        $('#transaction_date').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

        jQuery.validator.addMethod(
            'notEqual',
            function(value, element, param) {
                return this.optional(element) || value != param;
            },
            'Please select different location'
        );

        $('form#stock_issuance_form').validate({
            rules: {
                issuance_department_id: {
                    notEqual: function() {
                        return $('select#department_id').val();
                    },
                },
            },
        });
        
        $('#save_stock_issuance').click(function(e) {
            e.preventDefault();

            if ($('table#stock_issuance_product_table tbody').find('.product_row').length <= 0) {
                toastr.warning(LANG.no_products_added);
                return false;
            }
            if ($('form#stock_issuance_form').valid()) {
                $('form#stock_issuance_form').submit();
            } else {
                return false;
            }
        });

        const _stock_issuance_table = document.querySelector('#stock_issuance_table');
        if (document.body.contains(_stock_issuance_table)) {
            var stock_issuance_table = undefined; 
            stock_issuance_table = $('#stock_issuance_table').DataTable({ 
                pageLength: 25,
                processing: true,
                serverSide: true,
                aaSorting: [[0, 'desc']],
                ajax: '/stock-issuances',
                columnDefs: [
                    {
                        targets: 8,
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'ref_no', name: 'ref_no' },
                    { data: 'issuance_location', name: 'l1.name' },
                    { data: 'department_from', name: 'bdts1.name' },
                    { data: 'department_to', name: 'bdts2.name' },
                    { data: 'status', name: 'status' },
                    { data: 'shipping_charges', name: 'shipping_charges' },
                    { data: 'final_total', name: 'final_total' },
                    { data: 'additional_notes', name: 'additional_notes' },
                    { data: 'action', name: 'action' },
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#stock_issuance_table'));
                },
            });

            var detailRows = [];

            $('#stock_issuance_table tbody').on('click', '.view_stock_issuance', function() {
                var tr = $(this).closest('tr');
                var row = stock_issuance_table.row(tr);
                var idx = $.inArray(tr.attr('id'), detailRows);

                if (row.child.isShown()) {
                    $(this)
                        .find('i')
                        .removeClass('fa-eye')
                        .addClass('fa-eye-slash');
                    row.child.hide();

                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    $(this)
                        .find('i')
                        .removeClass('fa-eye-slash')
                        .addClass('fa-eye');

                    row.child((new StockIssuance()).get_stock_issuance_details(row.data())).show();

                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });

            // On each draw, loop over the `detailRows` array and show any child rows
            stock_issuance_table.on('draw', function() {
                $.each(detailRows, function(i, id) {
                    $('#' + id + ' .view_stock_issuance').trigger('click');
                });
            });

            //Delete Stock Transfer
            $(document).on('click', 'button.delete_stock_issuance', function() {
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    stock_issuance_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            }); 
        }
    }

    stock_issuance_product_row(variation_id) {
        var row_index = parseInt($('#product_row_index').val());
        var location_id = $('select#location_id').val();
        $.ajax({
            method: 'POST',
            url: '/stock-adjustments/get_product_row',
            data: { row_index: row_index, variation_id: variation_id, location_id: location_id, type: 'stock_issuance' },
            dataType: 'html',
            success: function(result) {
                $('table#stock_issuance_product_table tbody').append(result);
                (new StockIssuance()).update_table_total();
                $('#product_row_index').val(row_index + 1);
            },
        });
    }

    update_table_total() {
        var table_total = 0;
        $('table#stock_issuance_product_table tbody tr').each(function() {
            var this_total = parseFloat(__read_number($(this).find('input.product_line_total')));
            if (this_total) {
                table_total += this_total;
            }
        });

        $('span#total_adjustment').text(__number_f(table_total));

        if ($('input#shipping_charges').length) {
            var shipping_charges = __read_number($('input#shipping_charges'));
            table_total += shipping_charges;
        }

        $('span#final_total_text').text(__number_f(table_total));
        $('input#total_amount').val(table_total);
    }

    onChangeEvents() {
        $(document).on('change', '#shipping_charges', function() {
            (new StockIssuance()).update_table_total();
        });

        $(document).on('change', 'select.sub_unit', function() {
            var tr = $(this).closest('tr');
            var selected_option = $(this).find(':selected');
            var multiplier = parseFloat(selected_option.data('multiplier'));
            var allow_decimal = parseInt(selected_option.data('allow_decimal'));
            tr.find('input.base_unit_multiplier').val(multiplier);

            var base_unit_price = tr.find('input.hidden_base_unit_price').val();

            var unit_price = base_unit_price * multiplier;
            var unit_price_element = tr.find('input.product_unit_price');
            __write_number(unit_price_element, unit_price);
            
            var qty_element = tr.find('input.product_quantity');
            var base_max_avlbl = qty_element.data('qty_available');
            var error_msg_line = 'pos_max_qty_error';

            if (tr.find('select.lot_number').length > 0) {
                var lot_select = tr.find('select.lot_number');
                if (lot_select.val()) {
                    base_max_avlbl = lot_select.find(':selected').data('qty_available');
                    error_msg_line = 'lot_max_qty_error';
                }
            }
            qty_element.attr('data-decimal', allow_decimal);
            var abs_digit = true;
            if (allow_decimal) {
                abs_digit = false;
            }
            qty_element.rules('add', {
                abs_digit: abs_digit,
            });

            if (base_max_avlbl) {
                var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
                var formated_max_avlbl = __number_f(max_avlbl);
                var unit_name = selected_option.data('unit_name');
                var max_err_msg = __translate(error_msg_line, {
                    max_val: formated_max_avlbl,
                    unit_name: unit_name,
                });
                qty_element.attr('data-rule-max-value', max_avlbl);
                qty_element.attr('data-msg-max-value', max_err_msg);
                qty_element.rules('add', {
                    'max-value': max_avlbl,
                    messages: {
                        'max-value': max_err_msg,
                    },
                });
                qty_element.trigger('change');
            }
            qty_element.valid();
            (new StockIssuance()).update_table_row($(this).closest('tr'));
        });
    }

    update_table_row(tr) {
        var quantity = parseFloat(__read_number(tr.find('input.product_quantity')));
        var multiplier = 1;

        if (tr.find('select.sub_unit').length) {
            multiplier = parseFloat(
                tr.find('select.sub_unit')
                    .find(':selected')
                    .data('multiplier')
            );
        }
        quantity = quantity * multiplier;
        
        var unit_price = parseFloat(tr.find('input.hidden_base_unit_price').val());
        var row_total = 0;
        if (quantity && unit_price) {
            row_total = quantity * unit_price;
        }
        tr.find('input.product_line_total').val(__number_f(row_total));
        (new StockIssuance()).update_table_total();
    }

    get_stock_issuance_details(rowData) {
        var div = $('<div/>')
            .addClass('loading')
            .text('Loading...');
        $.ajax({
            url: '/stock-issuances/' + rowData.DT_RowId,
            dataType: 'html',
            success: function(data) {
                div.html(data).removeClass('loading');
            },
        });

        return div;
    }

    stock_issuance_status_onclick_event() {
        $(document).on('click', 'a.stock_issuance_status', function(e) {
            e.preventDefault();
            var href = $(this).data('href');
            var status = $(this).data('status');
            $('#update_stock_issuance_status_modal').modal('show');
            $('#update_stock_issuance_status_form').attr('action', href);
            $('#update_stock_issuance_status_form #update_status').val(status);
            $('#update_stock_issuance_status_form #update_status').trigger('change');
        });
    }

    update_stock_issuance_status_form_onsubmit() {
        $(document).on('submit', '#update_stock_issuance_status_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
    
            $.ajax({
                method: 'post',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                beforeSend: function(xhr) {
                    __disable_submit_button(form.find('button[type="submit"]'));
                },
                success: function(result) {
                    if (result.success == true) {
                        $('div#update_stock_issuance_status_modal').modal('hide');
                        toastr.success(result.msg);
                        stock_issuance_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                    $('#update_stock_issuance_status_form')
                    .find('button[type="submit"]')
                    .attr('disabled', false);
                },
            });
        });
    }

    onview_modal() {
        $(document).on('shown.bs.modal', '.view_modal', function() {
            __currency_convert_recursively($('.view_modal'));
        });
    }
}

document.addEventListener('DOMContentLoaded', event => {
    const stockIssuanceInstance = new StockIssuance();
    stockIssuanceInstance.initializeSI();
});