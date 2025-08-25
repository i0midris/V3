@extends('layouts.app')

@section('title', __('accounting::lang.edit_expense'))

@section('content')

@include('accounting::layouts.nav')

<!--Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('accounting::lang.edit_expense')</h1>
</section>

<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => action([\Modules\Accounting\Http\Controllers\TransferController::class, 'expense_update'],
         $mapping_transaction->id), 'method' => 'put', 'id' => 'expense_form' ]) !!}

    <div class="box box-solid">
        <div class="box-body">
            <div class="row">

                <div class="form-group">
                    {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                    @show_tooltip(__('lang_v1.leave_empty_to_autogenerate'))
                    {!! Form::text('ref_no',  $mapping_transaction->ref_no, ['class' => 'form-control']); !!}
                </div>
                <div class="form-group">
                    {!! Form::label('from_account',  __( 'lang_v1.payment_method' ) .":*") !!}
                    {!! Form::select('from_account', [ $debit_tansaction->accounting_account_id =>  $debit_tansaction->account()->first()->name ?? "" ] , $debit_tansaction->accounting_account_id, ['class' => 'form-control accounts-dropdown', 'required',
                        'parent_ids' => $cash.','.$bank,
                        'placeholder' => __('messages.please_select') ]); !!}
                </div>

                <div class="form-group">
                    {!! Form::label('to_account', __( 'accounting::lang.acc_expence_to' ) .":*") !!}
                    {!! Form::select('to_account', [ $credit_tansaction->accounting_account_id =>  $credit_tansaction->account()->first()->name ?? "" ] , $credit_tansaction->accounting_account_id, ['class' => 'form-control accounts-dropdown', 'required',
                        'placeholder' => __('messages.please_select') ]); !!}
                </div>

                <div class="form-group">
                    {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                    {!! Form::text('amount', isset($credit_tansaction2->id) ? $debit_tansaction->amount - $credit_tansaction2->amount : $debit_tansaction->amount, ['class' => 'form-control input_number',
                        'required','placeholder' => __( 'sale.amount' ) ]); !!}
                </div>

                <div class="form-group">
                    {!! Form::label('to_account2', __( 'business.tax' ) .":") !!}
                    {!! Form::select('to_account2', isset($credit_tansaction2->id) ? [ $credit_tansaction2->accounting_account_id =>  $credit_tansaction2->account()->first()->name ?? "" ] : [] , isset($credit_tansaction2->id) ? $credit_tansaction2->accounting_account_id : "", ['class' => 'form-control accounts-dropdown',
                         'same_ids' => '2105',
                        'placeholder' => __('messages.please_select') ]); !!}
                </div>

                <div class="form-group">
                    {!! Form::label('amount2', __( 'accounting::lang.amount_tax' ) .":") !!}
                    {!! Form::text('amount2', isset($credit_tansaction2->id) ? $credit_tansaction2->amount+0 : "", ['class' => 'form-control input_number',
                       'placeholder' => __( 'sale.amount' ) ]); !!}
                </div>

                <div class="form-group">
                    {!! Form::label('operation_date', __( 'messages.date' ) .":*") !!}
                    <div class="input-group">
                        {!! Form::text('operation_date',  @format_datetime($mapping_transaction->operation_date), ['class' => 'form-control',
                            'required','placeholder' => __( 'messages.date' ), 'id' => 'operation_date' ]); !!}
                        <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
                </span>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('location_id', __( 'business.location' ) .":*") !!}
                    {!! Form::select('location_id',  $locations_array, $debit_tansaction->location_id, ['class' => 'form-control locations-dropdown', 'required',
                        'placeholder' => __('messages.please_select') ]); !!}
                </div>


                <div class="form-group">
                    {!! Form::label('note', __( 'brand.note' )) !!}
                    {!! Form::textarea('note',  $mapping_transaction->note, ['class' => 'form-control',
                        'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
                </div>
            </div>
        </div>
    </div> <!--box end-->


    <div class="col-sm-12 text-center">
        <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
    </div>
    {!! Form::close() !!}
</section>
@endsection

@section('javascript')
    @include('accounting::accounting.common_js')
    <script type="text/javascript">
        $(document).ready( function(){

            $('#expense_form').submit(function(e){

                $("#error_tax").remove();

                $("#error_account2").remove();

                if( $("#amount2").val() != "" )
                {
                    if( parseFloat($("#amount2").val()) < parseFloat($("#amount").val()) )
                    {

                        if( $('#to_account2 :selected').val() == "")
                        {
                            $("#to_account2").after("<p id='error_account2' class='text-danger'>{{__('accounting::lang.no_account')}}</p>");

                            e.preventDefault();

                            return  false;
                        }

                    }
                    else
                    {
                        $("#amount2").after("<p id='error_tax' class='text-danger'>{{__('accounting::lang.error_tax')}}</p>");

                        e.preventDefault();

                        return  false;
                    }
                }

            });

            $('#operation_date').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
        });

        __page_leave_confirmation('#add_expense_form');
        $(document).on('change', 'input#final_total, input.payment-amount', function() {
            calculateExpensePaymentDue();
        });

        function calculateExpensePaymentDue() {
            var final_total = __read_number($('input#final_total'));
            var payment_amount = __read_number($('input.payment-amount'));
            var payment_due = final_total - payment_amount;
            $('#payment_due').text(__currency_trans_from_en(payment_due, true, false));
        }

        $(document).on('change', '#recur_interval_type', function() {
            if ($(this).val() == 'months') {
                $('.recur_repeat_on_div').removeClass('hide');
            } else {
                $('.recur_repeat_on_div').addClass('hide');
            }
        });

        $('#is_refund').on('ifChecked', function(event){
            $('#recur_expense_div').addClass('hide');
        });
        $('#is_refund').on('ifUnchecked', function(event){
            $('#recur_expense_div').removeClass('hide');
        });

        $(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
            var default_accounts = $('select#location_id').length ?
                $('select#location_id')
                    .find(':selected')
                    .data('default_payment_accounts') : [];
            var payment_types_dropdown = $('.payment_types_dropdown');
            var payment_type = payment_types_dropdown.val();
            if (payment_type) {
                var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                    default_accounts[payment_type]['account'] : '';
                var payment_row = payment_types_dropdown.closest('.payment_row');
                var row_index = payment_row.find('.payment_row_index').val();

                var account_dropdown = payment_row.find('select#account_' + row_index);
                if (account_dropdown.length && default_accounts) {
                    account_dropdown.val(default_account);
                    account_dropdown.change();
                }
            }
        });
    </script>
@endsection