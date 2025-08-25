<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header no-print">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title no-print">
        @lang(  'accounting::lang.journal_entry' )
      </h4>
    </div>
    <div class="modal-body">

        <table style="width:100%;">
            <thead>
            <tr>
                <td>

                    @if($invoice_layout != null)
                        <?php $logo = asset( 'uploads/invoice_logos/' . $invoice_layout->logo); ?>
                        @if(!empty($logo) &&  $invoice_layout->show_logo )
                            <div class="text-center" style="text-align: center">
                                <img src="{{$logo}}" style="height: 150px" class="img">
                            </div>
                        @endif
                    @endif

                    <p class="text-left">
                        @if(!empty($business_location->name))
                            <br>
                                {{__('lang_v1.location_ins1')}}: {{$business_location->name}}
                        @endif
                        @if(!empty($business_location->city) || !empty($business_location->state) || !empty($business_location->country))
                            <br>
                            {{implode(',', array_filter([$business_location->city, $business_location->state, $business_location->country]))}}
                        @endif
                        @if(!empty($business_location->city) || !empty($business_location->state) || !empty($business_location->country))
                            <br>
                            {{__('contact.mobile')}}: {{ $business_location->mobile }}
                        @endif
                        @if(!empty($business_location->custom_field1) || !empty($business_location->custom_field2))
                            <br>
                            @lang('accounting::lang.commercial_registration_no'):
                            {{ $business_location->custom_field1 }}
                            -
                            @lang('contact.tax_no'):
                            {{ $business_location->custom_field2 }}
                        @endif
                    </p>
                    <hr/>
                </td>
            </tr>
            </thead>
        </table>

        <table style="width:100%;" class="table cf table-hover">
            <thead class="cf">
            <tr>
                <th colspan="1">@lang('accounting::lang.journal_type')  {{$journal->type_trans}}</th>
                <th colspan="2">
                    <?php
                    if(isset($journal->link_table) && $journal->link_table == "transactions")
                    {
                        $transaction = \App\Transaction::where("id",$journal->link_id)->first();

                        if(isset($transaction->id) && $transaction->type == "purchase")
                            echo __('accounting::lang.Purchases Invoice')." ".$transaction->ref_no;

                        elseif(isset($transaction->id) && $transaction->type == "purchase_return")
                            echo __('lang_v1.purchase_return')." ".$transaction->ref_no;

                        elseif(isset($transaction->id) && $transaction->type == "sell")
                            echo __('accounting::lang.Sells Invoice')." ".$transaction->invoice_no;

                        elseif(isset($transaction->id) && $transaction->type == "sell_return")
                            echo __('lang_v1.sell_return')." ".$transaction->return_parent_sell->invoice_no;

                        elseif(isset($transaction->id) && $transaction->type == "purchase_transfer")
                            echo __('accounting::lang.purchase_transfer')." ".$transaction->ref_no;

                        elseif(isset($transaction->id) && $transaction->type == "stock_adjustment" && $transaction->adjustment_type == "normal")
                            echo __('accounting::lang.stock_adjustment_normal')." ".$transaction->ref_no;

                        elseif(isset($transaction->id) && $transaction->type == "stock_adjustment" && $transaction->adjustment_type == "abnormal")
                            echo __('accounting::lang.stock_adjustment_abnormal')." ".$transaction->ref_no;

                        elseif(isset($transaction->id) && $transaction->type == "opening_stock")
                            echo __('accounting::lang.Supply Bonds');
                    }
                    elseif(isset($journal->link_table) && $journal->link_table == "transaction_payments")
                    {
                        $transaction_payment = \App\TransactionPayment::where("id",$journal->link_id)->first();

                        if($transaction_payment->transaction->type == "purchase")
                            echo __('accounting::lang.payment_purchases_invoice')." ".$transaction_payment->transaction->ref_no."<br>".__('accounting::lang.payment')." ".$transaction_payment->payment_ref_no;

                        elseif($transaction_payment->transaction->type == "purchase_return")
                            echo __('accounting::lang.payment_return_purchases_invoice')." ".$transaction_payment->transaction->ref_no."<br>".__('accounting::lang.payment')." ".$transaction_payment->payment_ref_no;

                        elseif($transaction_payment->transaction->type == "sell")
                            echo __('accounting::lang.payment_sells_invoice')." ".$transaction_payment->transaction->invoice_no."<br>".__('accounting::lang.payment')." ".$transaction_payment->payment_ref_no;

                        elseif($transaction_payment->transaction->type == "sell_return")
                            echo __('accounting::lang.payment_return_sells_invoice')." ".$transaction_payment->transaction->invoice_no."<br>".__('accounting::lang.payment')." ".$transaction_payment->payment_ref_no;


                    }
                    ?>
                </th>
                <th>@lang('accounting::lang.registration_number') {{$journal->ref_no}}</th>
                <th>@lang('receipt.date') {{@format_date($journal->operation_date)}} </th>
            </tr>
            <tr>
                <th class="journal-report-align-cols" style="width: 30%;">@lang('accounting::lang.account')</th>
                <th class="journal-report-align-cols">@lang('accounting::lang.detail')</th>
                <th class="journal-report-align-cols">@lang('accounting::lang.debit')</th>
                <th class="journal-report-align-cols">@lang('accounting::lang.credit')</th>
                <th class="journal-report-align-cols">@lang('lang_v1.suspend_note')</th>
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
                        <td>{{isset($acc->id) ? $acc->gl_code." - ".$acc->name : __('accounting::lang.no_account')  }} </td>
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
            <tr>
                <td colspan="6">@lang('purchase.payment_note') {{$journal->note}}</td>
            </tr>
            </tbody>
        </table>


    </div>
    <div class="modal-footer no-print">
      <button type="button" class="btn btn-primary no-print" 
        aria-label="Print" 
          onclick="$(this).closest('div.modal').printThis();">
        <i class="fa fa-print"></i> @lang( 'messages.print' )
      </button>
      <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )
      </button>
    </div>
  </div>
</div>