@extends('layouts.app')

@section('title', __('accounting::lang.journal_entry'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'accounting::lang.journal_entry' )</h1>
</section>
<section class="content no-print">
    @component('components.widget', ['class' => 'box-solid'])

            <div class="box-header">
                <h3 class="box-title"> <i class="fa fa-filter" aria-hidden="true"></i> @lang('report.filters') </h3>
            </div>
            <div class="box-body" style="margin-top: 10px;">

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('location_id', __('business.business_locations') ) !!}

                            {!! Form::select('location_id', $business_locations, request()->location_id, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('account_id', __( 'accounting::lang.account' ) ) !!}
                            {!! Form::select('account_id', $all_accounts, request()->account_id,[
                                'class' => 'form-control select2',
                                'placeholder' => __('lang_v1.all'),
                                ]); !!}
                            </div>
                        </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('page_number', __('invoice.preview') ) !!}
                            {!! Form::select('page_number', [1 => "1",3 => "3",5 => "5",10 => "10",25 => "25",50 => "50",100 => "100",1000 => "1000"], request()->page_number, [
                                'class' => 'form-control select2',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('transaction_date_range', __('report.date_range') ) !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('transaction_date_range', null, ['class' => 'form-control', 'readonly', 'placeholder' => __('report.date_range')]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                        <a style="width: 100px;" class="btn btn-block btn-primary" href="javascript:void(0)" onclick="goto()">
                            @lang('report.filters')
                        </a>
                        </div>
                    </div>

                    <div class="col-sm-3">
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                        <input type="search" id="search" class="form-control" value="{{ request()->search }}" placeholder="@lang('lang_v1.search')" >
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                        <a style="width: 100px;" class="btn btn-block btn-primary" href="javascript:void(0)" onclick="search_now()">
                            @lang('lang_v1.search')
                        </a>
                        </div>
                    </div>
                    <div class="col-sm-3">
                    </div>
            </div>

    @endcomponent
    @component('components.widget', ['class' => 'box-solid'])

        @can('accounting.add_journal')
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-4">
                        <a class="btn btn-block btn-primary"
                           href="{{action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'create'])}}">
                            <i class="fas fa-plus"></i> @lang( 'accounting::lang.add_journal' )</a>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{action([\Modules\Accounting\Http\Controllers\TransferController::class, 'opening_balance_create1'])}}"
                                data-container="#create_opening_balance_modal" >
                            <i class="fas fa-plus"></i> @lang( 'accounting::lang.add_opening_balance1' )</button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{action([\Modules\Accounting\Http\Controllers\TransferController::class, 'opening_balance_create2'])}}"
                                data-container="#create_opening_balance_modal" >
                            <i class="fas fa-plus"></i> @lang( 'accounting::lang.add_opening_balance2' )</button>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-6">
                        <a class="btn btn-block btn-primary"
                           href="{{action([\Modules\Accounting\Http\Controllers\TransferController::class, 'receipt_create'])}}">
                            <i class="fas fa-plus"></i> @lang( 'accounting::lang.receipt' )</a>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-block btn-primary"
                           href="{{action([\Modules\Accounting\Http\Controllers\TransferController::class, 'expense_create'])}}">
                            <i class="fas fa-plus"></i> @lang( 'accounting::lang.expense' )</a>
                    </div>
                </div>
        @endcan
        @forelse($data as $journal)
            <div class="table-responsive manage-currency-table reports-table">
                <table class="table cf table-hover">
                    <thead class="cf">
                    <tr style="background-color: #1572e8 !important;color: white !important">
                        <th colspan="1">@lang('accounting::lang.journal_type') {{$journal->type_trans}}</th>
                        <th colspan="2">
                        <?php
    if (isset($journal->link_table) && $journal->link_table == "transactions") {
        $transaction = \App\Transaction::find($journal->link_id);

        if ($transaction && $transaction->type == "purchase")
            echo __('accounting::lang.Purchases Invoice') . " " . $transaction->ref_no;

        elseif ($transaction && $transaction->type == "purchase_return")
            echo __('lang_v1.purchase_return') . " " . $transaction->ref_no;

        elseif ($transaction && $transaction->type == "sell")
            echo __('accounting::lang.Sells Invoice') . " " . $transaction->invoice_no;

        elseif ($transaction && $transaction->type == "sell_return")
            echo __('lang_v1.sell_return') . " " . optional($transaction->return_parent_sell)->invoice_no;

        elseif ($transaction && $transaction->type == "purchase_transfer")
            echo __('accounting::lang.purchase_transfer') . " " . $transaction->ref_no;

        elseif ($transaction && $transaction->type == "stock_adjustment" && $transaction->adjustment_type == "normal")
            echo __('accounting::lang.stock_adjustment_normal') . " " . $transaction->ref_no;

        elseif ($transaction && $transaction->type == "stock_adjustment" && $transaction->adjustment_type == "abnormal")
            echo __('accounting::lang.stock_adjustment_abnormal') . " " . $transaction->ref_no;

        elseif ($transaction && $transaction->type == "opening_stock")
            echo __('accounting::lang.Supply Bonds');
    }

    elseif (isset($journal->link_table) && $journal->link_table == "transaction_payments") {
        $transaction_payment = \App\TransactionPayment::find($journal->link_id);
        $linked_transaction = optional($transaction_payment)->transaction;

        if ($linked_transaction && $linked_transaction->type == "purchase")
            echo __('accounting::lang.payment_purchases_invoice') . " " . $linked_transaction->ref_no . "<br>" . __('accounting::lang.payment') . " " . $transaction_payment->payment_ref_no;

        elseif ($linked_transaction && $linked_transaction->type == "purchase_return")
            echo __('accounting::lang.payment_return_purchases_invoice') . " " . $linked_transaction->ref_no . "<br>" . __('accounting::lang.payment') . " " . $transaction_payment->payment_ref_no;

        elseif ($linked_transaction && $linked_transaction->type == "sell")
            echo __('accounting::lang.payment_sells_invoice') . " " . $linked_transaction->invoice_no . "<br>" . __('accounting::lang.payment') . " " . $transaction_payment->payment_ref_no;

        elseif ($linked_transaction && $linked_transaction->type == "sell_return")
            echo __('accounting::lang.payment_return_sells_invoice') . " " . $linked_transaction->invoice_no . "<br>" . __('accounting::lang.payment') . " " . $transaction_payment->payment_ref_no;
    }
?>

                        </th>
                        <th>@lang('accounting::lang.registration_number') {{$journal->ref_no}}</th>
                        <th>@lang('receipt.date') {{@format_date($journal->operation_date)}} </th>
                        <th style="text-align: end;">
                            <a href="#" class="btn-modal" data-href="{{ action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'print'], [$journal->id]) }}"  data-container=".view_modal">
                                <i style="color: white !important" class="fa fa-print fa-lg" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="@lang('messages.print')"></i>
                            </a>
                            <?php
                            if($journal->type == "journal_entry")
                            {

                            $transaction = \App\Transaction::where("id",$journal->link_id)->first();

                                if(isset($transaction->id) && $transaction->type == "opening_stock")
                                {

                                }
                                else
                                {
                            ?>
                            <a href="{{action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'edit'], [$journal->id])}}">
                                <i style="color: white !important" class="fas fa-edit fa-lg" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="@lang('messages.edit')"></i>
                            </a>
                            <?php
                                }
                            }
                            elseif($journal->type == "expense")
                            {
                            ?>
                            <a class="print-invoice" href="#" data-href="{{action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'print_expense'], [$journal->id])}}">
                                <i style="color: white !important" class="fa fa-print fa-lg" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="@lang('expense.add_expense')"></i>
                            </a>
                            <a href="{{action([\Modules\Accounting\Http\Controllers\TransferController::class, 'expense_edit'], [$journal->id])}}">
                                <i style="color: white !important" class="fas fa-edit fa-lg" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="@lang('messages.edit')"></i>
                            </a>
                            <?php
                            }
                            elseif($journal->type == "receipt")
                            {
                            ?>
                            <a class="print-invoice" href="#" data-href="{{action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'print_receipt'], [$journal->id])}}">
                                <i style="color: white !important" class="fa fa-print fa-lg" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="@lang('accounting::lang.add_receipt')"></i>
                            </a>
                            <a href="{{action([\Modules\Accounting\Http\Controllers\TransferController::class, 'receipt_edit'], [$journal->id])}}">
                                <i style="color: white !important" class="fas fa-edit fa-lg" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="@lang('messages.edit')"></i>
                            </a>
                            <?php
                            }
                            elseif($journal->type == "opening_balance")
                            {

                            ?>
                            <a href="#" 
                                data-href="{{ action([\Modules\Accounting\Http\Controllers\TransferController::class, 'opening_balance_edit'], [$journal->id]) }}" 
                                class="btn-modal" 
                                data-container="#create_opening_balance_modal">
                                <i class="fas fa-edit fa-lg" style="color: white !important" data-toggle="tooltip" title="@lang('messages.edit')"></i>
                            </a>

                            <?php

                            }
                            ?>
                        </th>
                    </tr>
                    <tr>
                        <th class="journal-report-align-cols" style="width: 30%;">@lang('accounting::lang.account')</th>
                        <th class="journal-report-align-cols">@lang('accounting::lang.detail')</th>
                        <th class="journal-report-align-cols">@lang('accounting::lang.debit')</th>
                        <th class="journal-report-align-cols">@lang('accounting::lang.credit')</th>
                        <th class="journal-report-align-cols" colspan="2">@lang('lang_v1.suspend_note')</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $debit_amount = 0;
                        $credit_amount = 0;
                    ?>
                    @foreach($journal->childs() as $child)
                        @if($child->type == "debit")
                            <?php $acc = $child->account()->first(); ?>
                            <tr>
                                <td>{{isset($acc->id) ? $acc->gl_code." - ".$acc->name : __('accounting::lang.no_account')  }} </td>
                                <td>{{$child->location->name ?? ""}}</td>
                                <td>
                                        {{@num_format($child->amount)}}
                                        <?php $debit_amount += $child->amount; ?>
                                    </td>
                                <td></td>
                                <td colspan="2">
                                    {{$child->note}}
                                </td>
                            </tr>
                        @endif
                        @if($child->type == "credit")
                            <?php $acc = $child->account()->first(); ?>
                            <tr>
                                <td>{{isset($acc->id) ? $acc->gl_code." - ".$acc->name : __('accounting::lang.no_account')  }}  </td>
                                <td>{{$child->location->name ?? ""}}</td>
                                <td></td>
                                <td>
                                        {{@num_format($child->amount)}}
                                        <?php $credit_amount += $child->amount; ?>
                                    </td>
                                <td colspan="2">
                                    {{$child->note}}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="reports-total">
                        <td data-title="Account">@lang('business.created_by') {{$journal->createdBy->user_full_name}}</td>
                        <td data-title="Contact"></td>
                        <td data-title="Debit">{{@num_format($debit_amount)}}</td>
                        <td data-title="Credit">{{@num_format($credit_amount)}}</td>
                        <td data-title="comment" colspan="2"></td>
                    </tr>
                    <tr style="background-color: #1572e8 !important;color: white !important">
                        <td colspan="6">@lang('purchase.payment_note') {{$journal->note}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <hr style="margin: 0px;"/>
        @empty
           <div class="row">
               <div class="col-md-12" style="text-align: center;">
                   @lang('lang_v1.no_data')
               </div>
           </div>
        @endforelse
        @if(isset($data) && count($data))
        {{ $data->appends(request()->query())->links() }}
        @endif

    @endcomponent
        <div class="modal fade" id="create_opening_balance_modal" tabindex="-1" role="dialog">
</section>

@stop

@section('javascript')
@include('accounting::accounting.common_js')
<script>
    $(document).ready( function() {
        $(document).on('shown.bs.modal', '#create_opening_balance_modal', function () {
            $('#operation_date').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
            $('#opening_balance_form').submit(function (e) {
                e.preventDefault();
            }).validate({
                submitHandler: function (form) {
                    var data = $(form).serialize();

                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        beforeSend: function (xhr) {
                            __disable_submit_button($(form).find('button[type="submit"]'));
                        },
                        success: function (result) {
                            if (result.success == true) {
                                $('div#create_opening_balance_modal').modal('hide');
                                toastr.success(result.msg);
                                location.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                },
            })
        });
    });
    @if (! empty(request()->start_date) && ! empty(request()->end_date))
        dateRangeSettings.startDate = new Date('{{ request()->start_date }}');
        dateRangeSettings.endDate = new Date('{{request()->end_date}}');
    @endif
    $('#transaction_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            //goto();
        }
    );

    $(document).on('change', '#location_id',
        function() {
            //goto();
        });


    $(document).on('change', '#account_id',
        function() {
            //goto();
        });

    function goto()
    {
        url = base_path + '/accounting/journal-entry/?'
            + 'start_date=' + $('#transaction_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD')
            + '&end_date=' + $('#transaction_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD')
            + '&location_id=' + $('#location_id :selected').val()
            + '&account_id=' + $('#account_id :selected').val()
            + '&page_number=' + $('#page_number :selected').val();

        window.location = url;
    }

    function search_now()
    {
        url = base_path + '/accounting/journal-entry/?'
            + 'search=' + $('#search').val();

        window.location = url;
    }
</script>
@endsection
