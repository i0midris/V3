<style>
    /* Shrink the first column (#) */
    #journal_table th:first-child,
    #journal_table td:first-child {
        width: 35px !important;
        max-width: 35px !important;
        text-align: center;
        padding-left: 5px;
        padding-right: 5px;
    }


</style>

@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'accounting::lang.journal_entry' )</h1>
</section>
<section class="content">

{!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'store']), 
    'method' => 'post', 'id' => 'journal_add_form']) !!}

	@component('components.widget', ['class' => 'box-primary'])

        <div class="row">
            <div class="col-sm-3">
                <div class="form-group">
                    {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                    @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                    {!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
                </div>
            </div>

            <div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('journal_date', __('accounting::lang.journal_date') . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
						{!! Form::text('journal_date', @format_datetime('now'), ['class' => 'form-control datetimepicker', 'readonly', 'required']); !!}
					</div>
				</div>
			</div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('note', __('purchase.payment_note')) !!}
                    {!! Form::textarea('note', null, ['class' => 'form-control', 'rows' => 3]); !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div style="overflow-x:auto;">
            <table class="table table-bordered table-striped hide-footer" id="journal_table">
                <thead>
                    <tr>
                        <th >#</th>
                        <th class="col-md-4">@lang( 'accounting::lang.account' )</th>
                        <th class="col-md-1">@lang( 'accounting::lang.debit' )</th>
                        <th class="col-md-1">@lang( 'accounting::lang.credit' )</th>
                        <th class="col-md-2">@lang( 'purchase.business_location' )</th>
                        <th class="col-md-4">@lang( 'purchase.payment_note' )</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= 4; $i++)
                        <tr>
                            <td>{{$i}}</td>
                            <td>
                                {!! Form::select('account_id[' . $i . ']', [], null, 
                                            ['class' => 'form-control accounts-dropdown account_id', 
                                            'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;min-width:150px']); !!}
                            </td>

                            <td>
                                {!! Form::text('debit[' . $i . ']', null, ['class' => 'form-control input_number debit','style' => 'width: 100%;min-width:100px']); !!}
                            </td>

                            <td>
                                {!! Form::text('credit[' . $i . ']', null, ['class' => 'form-control input_number credit','style' => 'width: 100%;min-width:100px']); !!}
                            </td>
                            <td>
    {!! Form::select('location_id[' . $i . ']', [], null,
        [
            'class' => 'form-control locations-dropdown location_id',
            'placeholder' => __('messages.please_select'),
            'required' => true,
            'style' => 'width: 100%;min-width:100px'
        ]); !!}
</td>

                            <td>
                                {!! Form::text('rec_note[' . $i . ']', null, ['class' => 'form-control','style' => 'width: 100%;min-width:200px']); !!}
                            </td>
                        </tr>
                    @endfor
                </tbody>

                <tfoot>
                    <tr>
                        <th></th>
                        <th class="text-center">@lang( 'accounting::lang.total' )</th>
                        <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                        <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span></th>
                    </tr>
                </tfoot>
            </table>
                </div>
            </div>
        </div>


        <button type="button" id="add_journal_row" class="btn btn-primary btn-sm" style="margin-bottom: 10px; border-radius: 5px; font-weight: 500;">
            <i class="fa fa-plus-circle" style="margin-right: 5px;"></i> @lang('messages.add_row')
        </button>

        <div class="row">
            <div class="col-sm-12">
                <button type="button" class="btn btn-primary pull-right btn-flat journal_add_btn">@lang('messages.save')</button>
            </div>
        </div>
        
    @endcomponent

    {!! Form::close() !!}
</section>

@stop

@section('javascript')
@include('accounting::accounting.common_js')
<script type="text/javascript">
$(document).ready(function () {
    let rowIndex = 4;

    // === INIT HELPERS ===
    function initRowFeatures($row) {
        // Initialize dropdowns using existing helpers
        adding_accounts_dropdown($row);
        adding_locations_dropdown($row);

        // Debit/Credit exclusivity
        $row.find('.credit').on('change', function () {
            if ($(this).val() > 0) {
                $(this).closest('tr').find('.debit').val('');
            }
            calculate_total();
        });

        $row.find('.debit').on('change', function () {
            if ($(this).val() > 0) {
                $(this).closest('tr').find('.credit').val('');
            }
            calculate_total();
        });
    }

    // === Initialize existing 4 rows
    $('#journal_table tbody tr').each(function () {
        initRowFeatures($(this));
    });

    // === Add New Rows ===
    $('#add_journal_row').click(function () {
        for (let i = 0; i < 2; i++) {
            rowIndex++;

            const $newRow = $(`
                <tr>
                    <td>${rowIndex}</td>
                    <td>
                        <select name="account_id[${rowIndex}]" class="form-control accounts-dropdown account_id"></select>
                    </td>
                    <td>
                        <input type="text" name="debit[${rowIndex}]" class="form-control input_number debit" />
                    </td>
                    <td>
                        <input type="text" name="credit[${rowIndex}]" class="form-control input_number credit" />
                    </td>
                    <td>
                        <select name="location_id[${rowIndex}]" class="form-control locations-dropdown location_id" required></select>
                    </td>
                    <td>
                        <input type="text" name="rec_note[${rowIndex}]" class="form-control" />
                    </td>
                </tr>
            `);

            $('#journal_table tbody').append($newRow);
            initRowFeatures($newRow);
        }
    });

    // === Calculate Totals ===
    function calculate_total() {
        let total_credit = 0;
        let total_debit = 0;

        $('#journal_table tbody tr').each(function () {
            total_credit += __read_number($(this).find('.credit'));
            total_debit += __read_number($(this).find('.debit'));
        });

        $('.total_credit_hidden').val(total_credit);
        $('.total_debit_hidden').val(total_debit);
        $('.total_credit').text(__currency_trans_from_en(total_credit));
        $('.total_debit').text(__currency_trans_from_en(total_debit));
    }

    // === Form Submit Button ===
    $('.journal_add_btn').click(function (e) {
    e.preventDefault();

    const $btn = $(this);
    if ($btn.data('submitted') === true) return;

    calculate_total();
    let is_valid = true;

    let totalCredit = parseFloat($('.total_credit_hidden').val()) || 0;
    let totalDebit = parseFloat($('.total_debit_hidden').val()) || 0;

    if (Math.abs(totalCredit - totalDebit) > 0.001) {
        alert("@lang('accounting::lang.credit_debit_equal')");
        is_valid = false;
    }

    $('#journal_table tbody tr').each(function () {
        const credit = __read_number($(this).find('.credit'));
        const debit = __read_number($(this).find('.debit'));
        const location_id = $(this).find('.location_id').val();

        if ((credit !== 0 || debit !== 0)) {
    const $locationSelect = $(this).find('.location_id');

    // If empty, show custom message
    if (!$locationSelect.val()) {
        $locationSelect[0].setCustomValidity("@lang('accounting::lang.select_location_required')");
        $locationSelect[0].reportValidity();
        is_valid = false;
        return false; // exit loop early
    } else {
        // 🚨 Clear the message if the field is now valid
        $locationSelect[0].setCustomValidity('');
    }
}



    });

    if (is_valid) {
        $btn.data('submitted', true);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> @lang("messages.saving")');
        $('#journal_add_form').submit();
    }

    return is_valid;
});

});
</script>

@endsection