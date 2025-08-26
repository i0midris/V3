@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'accounting::lang.journal_entry' ) - {{$journal->ref_no}}</h1>
</section>
<section class="content">

{!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'update'], $journal->id), 
    'method' => 'PUT', 'id' => 'journal_add_form']) !!}

	@component('components.widget', ['class' => 'box-primary'])

        <div class="row">
            
            <div class="col-md-6">
				<div class="form-group">
					{!! Form::label('journal_date', __('accounting::lang.journal_date') . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
						{!! Form::text('journal_date', @format_datetime($journal->operation_date), ['class' => 'form-control datetimepicker', 'readonly', 'required']); !!}
					</div>
				</div>
			</div>

        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('note', __('purchase.payment_note')) !!}
                    {!! Form::textarea('note', $journal->note, ['class' => 'form-control', 'rows' => 3]); !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div style="overflow-x:auto;">
                    <table class="table table-bordered table-striped hide-footer" id="journal_table">
                        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                            <tr>
                                <th class="col-md-1">#</th>
                                <th class="col-md-4">@lang( 'accounting::lang.account' )</th>
                                <th class="col-md-1">@lang( 'accounting::lang.debit' )</th>
                                <th class="col-md-1">@lang( 'accounting::lang.credit' )</th>
                                <th class="col-md-3">@lang( 'purchase.business_location' )</th>
                                <th class="col-md-2">@lang( 'purchase.payment_note' )</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $row_count = count($accounts_transactions); @endphp
                                @for($i = 1; $i <= $row_count; $i++)
                                <tr>

                                    @php
                                        $account_id = '';
                                        $debit = '';
                                        $credit = '';
                                        $default_array = [];
                                        $default_array1 = [];
                                        $rec_note = '';
                                    @endphp

                                    @if(isset($accounts_transactions[$i-1]))
                                        @php
                                            $account_id = $accounts_transactions[$i-1]['accounting_account_id'];
                                            $location_id = $accounts_transactions[$i-1]['location_id'];
                                            $rec_note = $accounts_transactions[$i-1]['note'];
                                            $debit = ($accounts_transactions[$i-1]['type'] == 'debit') ? $accounts_transactions[$i-1]['amount'] : '';
                                            $credit = ($accounts_transactions[$i-1]['type'] == 'credit') ? $accounts_transactions[$i-1]['amount'] : '';
                                            $default_array = [$account_id => $accounts_transactions[$i-1]['account']['name'] ?? ""];
                                            $default_array1 = [$location_id => $accounts_transactions[$i-1]['location']['name'] ?? ""];
                                        @endphp

                                        {!! Form::hidden('accounts_transactions_id[' . $i . ']', $accounts_transactions[$i-1]['id']); !!}
                                    @endif
                                
                                    <td>{{$i}}</td>
                                    <td>
                                        {!! Form::select('account_id[' . $i . ']', $default_array, $account_id, 
                                                    ['class' => 'form-control accounts-dropdown account_id', 
                                                    'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;min-width:150px']); !!}
                                    </td>

                                    <td>
                                        {!! Form::text('debit[' . $i . ']', $debit, ['class' => 'form-control input_number debit','style' => 'width: 100%;min-width:100px']); !!}
                                    </td>

                                    <td>
                                        {!! Form::text('credit[' . $i . ']', $credit, ['class' => 'form-control input_number credit','style' => 'width: 100%;min-width:100px']); !!}
                                    </td>
                                    <td>
                                        {!! Form::select('location_id[' . $i . ']',  $default_array1, $location_id,
                                                    ['class' => 'form-control locations-dropdown location_id',
                                                    'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;min-width:150px']); !!}
                                    </td>
                                    <td>
                                        {!! Form::text('rec_note[' . $i . ']', $rec_note, ['class' => 'form-control','style' => 'width: 100%;min-width:100px']); !!}
                                    </td>
                                </tr>
                            @endfor
                        </tbody>

                        <tfoot style="background-color: #e9ecef;">
                            <tr>
                                <th></th>
                                <th class="text-center">@lang( 'accounting::lang.total' )</th>
                                <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                                <th colspan="3"><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <button type="button" id="add_journal_row" class="add-btn tw-gap-2 !tw-text-xs" style="margin-bottom: 10px; border-radius: 5px; font-weight: 500;">
    <i class="fa fa-plus-circle" style="margin-right: 5px;"></i> @lang('messages.add_row')
</button>


        <div class="row">
            <div class="col-sm-12">
                <button type="button" class="add-btn pull-right journal_add_btn">@lang('messages.save')</button>
            </div>
        </div>
        
    @endcomponent

    {!! Form::close() !!}
</section>

@stop

@section('javascript')
@include('accounting::accounting.common_js')

@section('javascript')
@include('accounting::accounting.common_js')
<script type="text/javascript">
$(document).ready(function () {
    let rowIndex = {{ count($accounts_transactions) }};


    // === Init All Existing Rows ===
    $('#journal_table tbody tr').each(function () {
        initRowFeatures($(this));
    });

    // === Save Button Handler ===
    $('.journal_add_btn').click(function (e) {
        e.preventDefault();

        calculate_total();

        let is_valid = true;

        if ($('.total_credit_hidden').val() != $('.total_debit_hidden').val()) {
            alert("@lang('accounting::lang.credit_debit_equal')");
            is_valid = false;
        }

        $('#journal_table tbody tr').each(function () {
            const credit = __read_number($(this).find('.credit'));
            const debit = __read_number($(this).find('.debit'));

            if ((credit !== 0 || debit !== 0) && $(this).find('.account_id').val() == '') {
                alert("@lang('accounting::lang.select_all_accounts')");
                is_valid = false;
            }
        });

        if (is_valid) {
            $('form#journal_add_form').submit();
        }

        return is_valid;
    });

    // === Add New Rows Button ===
    $('#add_journal_row').click(function () {
        for (let i = 0; i < 2; i++) {
            rowIndex++;

            const $newRow = $(`
                <tr>
                    <td>${rowIndex}</td>
                    <td>
                        <select name="account_id[${rowIndex}]" class="form-control accounts-dropdown account_id" style="width: 100%;min-width:150px;"></select>
                    </td>
                    <td>
                        <input type="text" name="debit[${rowIndex}]" class="form-control input_number debit" style="width: 100%;min-width:100px;" />
                    </td>
                    <td>
                        <input type="text" name="credit[${rowIndex}]" class="form-control input_number credit" style="width: 100%;min-width:100px;" />
                    </td>
                    <td>
                        <select name="location_id[${rowIndex}]" class="form-control locations-dropdown location_id" style="width: 100%;min-width:150px;"></select>
                    </td>
                    <td>
                        <input type="text" name="rec_note[${rowIndex}]" class="form-control" style="width: 100%;min-width:100px;" />
                    </td>
                </tr>
            `);

            $('#journal_table tbody').append($newRow);
            initRowFeatures($newRow);
        }
    });

    // === Utility Functions ===

    function initRowFeatures($row) {
        adding_accounts_dropdown($row);
        adding_locations_dropdown($row);

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

    // Trigger initial total calculation
    calculate_total();
});
</script>
@endsection
