$(document).ready(function () {
    // Form reference
    var pos_form_obj = $('form#sell_return_form').length > 0
        ? $('form#sell_return_form')
        : $('form#add_pos_sell_form');

    // Initialize printer socket if needed
    if (pos_form_obj.length > 0) {
        initialize_printer();
    }

    // Initialize date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    // Show max returnable quantity below inputs
    $('input.return_qty').each(function () {
        var max = $(this).data('rule-max-value');
        var $hint = $('<small class="text-muted return-limit-hint tw-mt-1">')
            .text('Max: ' + max)
            .css('display', 'block');
        $(this).after($hint);
    });

    // Live feedback on quantity input
    $(document).on('input', 'input.return_qty', function () {
        var max = parseFloat($(this).data('rule-max-value'));
        var val = parseFloat($(this).val()) || 0;

        if (val > max) {
            $(this).addClass('is-invalid');
            $(this).siblings('.return-limit-hint').text('⚠️ Max allowed: ' + max);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.return-limit-hint').text('Max: ' + max);
        }

        updateLineSubtotal($(this).closest('tr'));
        update_sell_return_total();
    });

    // Update total on discount change
    $(document).on('change', '#discount_type, #discount_amount', function () {
        update_sell_return_total();
    });

    // Initialize validator and handle form submit
    pos_form_validator = pos_form_obj.validate({
        submitHandler: function (form) {
            var data = $(form).serialize();
            var url = $(form).attr('action');

            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (result.success == 1) {
                        toastr.success(result.msg);
                        if (result.receipt.is_enabled) {
                            pos_print(result.receipt);
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });

            return false;
        },
    });

    // Initial calculation
    $('table#sell_return_table tbody tr').each(function () {
        updateLineSubtotal($(this));
    });
    update_sell_return_total();
});

// Calculates line subtotal for a given row
function updateLineSubtotal($row) {
    var qty = __read_number($row.find('input.return_qty'));
    var price = __read_number($row.find('input.unit_price'));
    var subtotal = qty * price;
    $row.find('.return_subtotal').text(__currency_trans_from_en(subtotal, true));
}

// Calculates and updates the return totals
function update_sell_return_total() {
    let net_return = 0;
    let total_qty_returned = 0;
    let total_qty_sold = 0;
    let fixed_discount = 0;

    // Get total discount and type
    const discount_type = $('#discount_type').val();
    const discount_amount = __read_number($("#discount_amount"));

    // Step 1: Calculate subtotal & total returned quantity
    $('table#sell_return_table tbody tr').each(function () {
        const qty = __read_number($(this).find('input.return_qty'));
        const sold_qty = parseFloat($(this).find('td:nth-child(4)').text()) || 0; // format: "X Pc" or similar
        const price = __read_number($(this).find('input.unit_price'));
        const line_total = qty * price;

        net_return += line_total;
        total_qty_returned += qty;
        total_qty_sold += parseFloat(sold_qty) || 0;

        $(this).find('.return_subtotal').text(__currency_trans_from_en(line_total, true));
    });

    // Step 2: Calculate discount
    let discount = 0;

   if (discount_type === 'fixed') {
    if (total_qty_sold > 0) {
        const original_total = net_return / total_qty_returned * total_qty_sold;
        const fixed_to_percentage = (discount_amount / original_total) * 100;

        $('#discount_type').val('percentage');
        $('#discount_amount').val(fixed_to_percentage.toFixed(6));

        discount = __calculate_amount('percentage', fixed_to_percentage, net_return);
    }
}

    const discounted_total = net_return - discount;

    // Step 3: Calculate tax
    const tax_percent = __read_number($('#tax_percent'));
    const tax_amount = __calculate_amount('percentage', tax_percent, discounted_total);

    // Step 4: Final total
    const final_total = discounted_total + tax_amount;

    // Update UI
    $('#tax_amount').val(tax_amount);
    $('#total_return_discount').text(__currency_trans_from_en(discount, true));
    $('#total_return_tax').text(__currency_trans_from_en(tax_amount, true));
    $('#net_return').text(__currency_trans_from_en(final_total, true));
}


// Initializes socket if printer type is "printer"
function initialize_printer() {
    if ($('input#location_id').data('receipt_printer_type') == 'printer') {
        initializeSocket();
    }
}

// Handles printing (browser or hardware printer)
function pos_print(receipt) {
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';

        if (socket.readyState != 1) {
            initializeSocket();
            setTimeout(function () {
                socket.send(JSON.stringify(content));
            }, 700);
        } else {
            socket.send(JSON.stringify(content));
        }
    } else if (receipt.html_content != '') {
        var title = document.title;
        if (typeof receipt.print_title != 'undefined') {
            document.title = receipt.print_title;
        }

        $('#receipt_section').html(receipt.html_content);
        __currency_convert_recursively($('#receipt_section'));
        setTimeout(function () {
            window.print();
            document.title = title;
        }, 1000);
    }
}

 //Set the location and initialize printer
// function set_location(){
// 	if($('input#location_id').length == 1){
// 	       $('input#location_id').val($('select#select_location_id').val());
// 	       //$('input#location_id').data('receipt_printer_type', $('select#select_location_id').find(':selected').data('receipt_printer_ty
// 	}

// 	if($('input#location_id').val()){
// 	       $('input#search_product').prop( "disabled", false ).focus();
// 	} else {
// 	       $('input#search_product').prop( "disabled", true );
// 	}

// 	initialize_printer();
// }
