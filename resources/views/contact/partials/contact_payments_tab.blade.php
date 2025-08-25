<table class="table table-striped" id="contact_payments_table">
    <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
        <tr>
            <th>@lang('lang_v1.paid_date')</th>
            <th>@lang('purchase.ref_no')</th>
            <th>@lang('sale.amount')</th>
            <th>@lang('account.payment_for')</th>
            <th>@lang('account.payer_account')</th>
            <th>@lang('account.recipient_account')</th>
            <th>@lang('messages.action')</th>
        </tr>
    </thead>
    <tbody>
    @forelse($payments as $payment)
        @php
            $count_child_payments = isset($payment->child_payments) ? count($payment->child_payments) : 0;
        @endphp

        @include('contact.partials.payment_row', compact('payment', 'count_child_payments'))

        @if($count_child_payments > 0)
            @foreach($payment->child_payments as $child_payment)
                @include('contact.partials.payment_row', [
                    'payment' => $child_payment, 
                    'count_child_payments' => 0, 
                    'parent_payment_ref_no' => $payment->payment_ref_no
                ])
            @endforeach
        @endif
    @empty
        <tr>
            <td colspan="7" class="text-center">@lang('purchase.no_records_found')</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="text-right" style="width: 100%;" id="contact_payments_pagination">
    {{ $payments->links() }}
</div>
