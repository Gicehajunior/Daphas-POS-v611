
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
