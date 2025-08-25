$(document).ready(function() {
    $(document).on('click', '.add_payment_modal', function(e) {
        e.preventDefault();
        var container = $('.payment_modal');

        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(result) {
                if (result.status == 'due') {
                    container.html(result.view).modal('show');
                    __currency_convert_recursively(container);
                    $('#paid_on').datetimepicker({
                        format: moment_date_format + ' ' + moment_time_format,
                        ignoreReadonly: true,
                    });
                    container.find('form#transaction_payment_add_form').validate();
                    set_default_payment_account();

                    $('.payment_modal')
                        .find('input[type="checkbox"].input-icheck')
                        .each(function() {
                            $(this).iCheck({
                                checkboxClass: 'icheckbox_square-blue',
                                radioClass: 'iradio_square-blue',
                            });
                        });
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    $(document).on('click', '.edit_payment', function(e) {
        e.preventDefault();
        var container = $('.edit_payment_modal');

        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                container.html(result).modal('show');
                __currency_convert_recursively(container);
                $('#paid_on').datetimepicker({
                    format: moment_date_format + ' ' + moment_time_format,
                    ignoreReadonly: true,
                });
                container.find('form#transaction_payment_add_form').validate();
            },
        });
    });

    $(document).on('click', '.view_payment_modal', function(e) {
        e.preventDefault();
        var container = $('.payment_modal');

        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                __currency_convert_recursively(container);
            },
        });
    });
    $(document).on('click', '.delete_payment', function(e) {
        e.preventDefault();
        const button = $(this); // preserve context
    
        Swal.fire({
            title: LANG.sure,
            text: LANG.confirm_delete_payment,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: LANG.ok || 'Yes',
            cancelButtonText: LANG.cancel || 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: button.data('href'),
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(result) {
                        if (result.success === true) {
                            $('div.payment_modal').modal('hide');
                            $('div.edit_payment_modal').modal('hide');
                            toastr.success(result.msg);
    
                            if (typeof purchase_table !== 'undefined') {
                                purchase_table.ajax.reload();
                            }
                            if (typeof sell_table !== 'undefined') {
                                sell_table.ajax.reload();
                            }
                            if (typeof expense_table !== 'undefined') {
                                expense_table.ajax.reload();
                            }
                            if (typeof ob_payment_table !== 'undefined') {
                                ob_payment_table.ajax.reload();
                            }
                            if (typeof project_invoice_datatable !== 'undefined') {
                                project_invoice_datatable.ajax.reload();
                            }
    
                            if ($('#contact_payments_table').length) {
                                get_contact_payments();
                            }
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
    

    //view single payment
    $(document).on('click', '.view_payment', function() {
        var url = $(this).data('href');
        var container = $('.view_modal');
        $.ajax({
            method: 'GET',
            url: url,
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                __currency_convert_recursively(container);
            },
        });
    });
});

$(document).on('change', '#transaction_payment_add_form .payment_types_dropdown', function(e) {
    set_default_payment_account();
});

function set_default_payment_account() {
    var default_accounts = {};

    if (!_.isUndefined($('#transaction_payment_add_form #default_payment_accounts').val())) {
        default_accounts = JSON.parse($('#transaction_payment_add_form #default_payment_accounts').val());
    }

    var payment_type = $('#transaction_payment_add_form .payment_types_dropdown').val();
    if (payment_type && payment_type != 'advance') {
        var default_account = !_.isEmpty(default_accounts) && default_accounts[payment_type]['account'] ? 
            default_accounts[payment_type]['account'] : '';
        $('#transaction_payment_add_form #account_id').val(default_account);
        $('#transaction_payment_add_form #account_id').change();
    }
}

$(document).on('change', '.payment_types_dropdown', function(e) {
    var payment_type = $('#transaction_payment_add_form .payment_types_dropdown').val();
    account_dropdown = $('#transaction_payment_add_form #account_id');
    if (payment_type == 'advance') {
        if (account_dropdown) {
            account_dropdown.prop('disabled', true);
            account_dropdown.closest('.form-group').addClass('hide');
        }
    } else {
        if (account_dropdown) {
            account_dropdown.prop('disabled', false); 
            account_dropdown.closest('.form-group').removeClass('hide');
        }    
    }
});
$(document).on('submit', '#transaction_payment_add_form', function (e) {
    const $form = $(this);
    let is_valid = true;

    const payment_type = $form.find('.payment_types_dropdown').val();
    const selectedCurrency = $form.find('#currency_code').val();

    // ✅ Multi-currency conversion
    if (selectedCurrency) {
        const $rateInput = $form.find('#exchange_rate');
        const $baseInput = $form.find('#base_amount_input');
        const $amountInput = $form.find('.payment_amount');
        const $baseHidden = $form.find('#base_amount');

        const rate = parseFloat($rateInput.val()) || 1;
        const baseVal = __read_number($baseInput);

        if (isNaN(baseVal) || baseVal <= 0) {
            toastr.error('Base amount is missing or invalid.');
            e.preventDefault();
            return false;
        }

        const convertedAmount = baseVal * rate;

        console.log('[💰 Converted Amount]', { convertedAmount: convertedAmount.toFixed(2) });

        $amountInput.val(convertedAmount.toFixed(2));
        $baseHidden.val(baseVal.toFixed(2));
    }

    // 💵 Cash denomination validation
    let denomination_for_payment_types = [];
    const rawDenominations = $form.find('.enable_cash_denomination_for_payment_methods').val();
    if (rawDenominations) {
        try {
            denomination_for_payment_types = JSON.parse(rawDenominations);
        } catch (err) {
            console.warn('Invalid JSON in .enable_cash_denomination_for_payment_methods:', err);
        }
    }

    if (
        denomination_for_payment_types.includes(payment_type) &&
        $form.find('.is_strict').val() === '1'
    ) {
        const paymentAmount = __read_number($form.find('.payment_amount'));
        const totalDenomination = parseFloat($form.find('.denomination_total_amount').val()) || 0;

        if (paymentAmount !== totalDenomination) {
            is_valid = false;
        }
    }

    // ❌ Final form check
    if (!is_valid) {
        $form.find('.cash_denomination_error').removeClass('hide');
        e.preventDefault();
    } else {
        $form.find('.cash_denomination_error').addClass('hide');
    }

    // 🔁 Ensure submit not blocked
    $form.find('button[type="submit"]').attr('disabled', false);
});

$(document).on('shown.bs.modal', '.payment_modal', function () {
    const rates = window.currencyRates || {};

    const $modal = $(this);
    const $currencyCode = $modal.find('#currency_code');
    const $amount = $modal.find('.payment_amount');
    const $baseAmountInput = $modal.find('#base_amount_input');
    const $baseAmountHidden = $modal.find('#base_amount');
    const $exchangeRate = $modal.find('#exchange_rate');
    const $exchangeRateDisplay = $modal.find('#exchange_rate_display');
    const $baseAmountWrapper = $modal.find('#base_amount_wrapper');

    function updateAmountFromBase() {
        const rate = parseFloat($exchangeRate.val()) || 1;
        const baseVal = __read_number($baseAmountInput);

        if (!isNaN(baseVal) && baseVal > 0 && rate > 0) {
            const calcAmount = baseVal * rate;

            $amount.val(calcAmount.toFixed(2));
            $baseAmountHidden.val(baseVal.toFixed(2));
        } else {
            $amount.val('');
            $baseAmountHidden.val('');
        }
    }

    function updateCurrencyFields() {
        const selected = $currencyCode.val();
        const rate = parseFloat(rates[selected]) || 1;

        if (selected) {
            $exchangeRate.val(rate);
            $exchangeRateDisplay.val(rate);
            $baseAmountWrapper.show();
            updateAmountFromBase();
        } else {
            $exchangeRate.val('');
            $exchangeRateDisplay.val(1);
            $baseAmountWrapper.hide();
            $baseAmountInput.val('');
            $baseAmountHidden.val('');
            $amount.val('');
        }
    }

    $currencyCode.off('change').on('change', updateCurrencyFields);
    $baseAmountInput.off('input').on('input', updateAmountFromBase);

    // Initialize fields on modal open
    updateCurrencyFields();
});
