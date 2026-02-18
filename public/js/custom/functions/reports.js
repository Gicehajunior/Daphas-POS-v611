
function __sum_stock_qty_count_func(table, class_name) {
    var stocks = 0;

    table.find('tbody tr').each(function() {
        var element = $(this).find('.' + class_name);
        var qtyCount = element.attr('data-quantity') || element.data('quantity'); 
        
        if (qtyCount) {
            stocks += parseFloat(qtyCount);
        }
    });

    var stock_html = `<p class="text-left"><small>
                    <span data-is_quantity="true">
                    ${stocks}
                    </span></small></p>`;

    return stock_html;
}

function __sum_stock_unit_func(table, class_name) {
    var stock_counts = {};

    table.find('tbody').find('tr').each(function () {
        var element = $(this).find('.' + class_name);
        if (element.data('orig-value')) { 
            var unit_name = element.data('unit') || ''; 
            var qtyCount = element.data('quantity'); 

            console.log(qtyCount);
            // Count occurrences of each unique unit value
            if (unit_name in stock_counts) {
                stock_counts[unit_name] += parseFloat(qtyCount);
            } else {
                stock_counts[unit_name] = parseFloat(qtyCount);
            }
        }
    });

    var stock_html = '<p class="text-left"><small>';

    for (var key in stock_counts) {
        stock_html += stock_counts[key] + ' x ' + key + '</br>';
    }

    stock_html += '</small></p>';

    return stock_html;
}
