@php
    use Carbon\Carbon;

    $col_class = !empty($accounts) ? 'col-md-4' : 'col-md-6';
    $readonly = ($payment_line['method'] ?? '') === 'advance';
    $paid_on = $payment_line['paid_on'] ?? now();
    $paid_on_formatted = Carbon::parse($paid_on)
        ->timezone(session('business.timezone', config('app.timezone')))
        ->format(session('business.date_format') . ' ' . (session('business.time_format') == 24 ? 'H:i' : 'h:i A'));
@endphp

<div class="row tw-border-b tw-pb-2 tw-mb-2">
    <input type="hidden" class="payment_row_index" value="{{ $row_index }}">

    {{-- Amount --}}
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("amount_$row_index", __('sale.amount') . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                {!! Form::text("payment[$row_index][amount]", @num_format($payment_line['amount'] ?? 0), [
                    'class' => 'form-control payment-amount input_number',
                    'required',
                    'id' => "amount_$row_index",
                    'placeholder' => __('sale.amount'),
                    'readonly' => $readonly,
                ]) !!}
            </div>
        </div>
    </div>

    {{-- Paid On --}}
    @if(!empty($show_date))
        <div class="{{ $col_class }}">
            <div class="form-group">
                {!! Form::label("paid_on_$row_index", __('lang_v1.paid_on') . ':*') !!}
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text("payment[$row_index][paid_on]", $paid_on_formatted, [
                        'class' => 'form-control paid_on',
                        'readonly',
                        'required',
                    ]) !!}
                </div>
            </div>
        </div>
    @endif

    {{-- Method --}}
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("method_$row_index", __('lang_v1.payment_method') . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                @php
                    $_payment_method = $payment_line['method'] ?? (array_key_exists('cash', $payment_types) ? 'cash' : '');
                @endphp
                {!! Form::select("payment[$row_index][method]", $payment_types, $_payment_method, [
                    'class' => 'form-control col-md-12 payment_types_dropdown',
                    'required',
                    'id' => !$readonly ? "method_$row_index" : "method_advance_$row_index",
                    'style' => 'width:100%;',
                    'disabled' => $readonly,
                ]) !!}

                @if($readonly)
                    {!! Form::hidden("payment[$row_index][method]", $payment_line['method'], [
                        'class' => 'payment_types_dropdown',
                        'required',
                        'id' => "method_$row_index"
                    ]) !!}
                @endif
            </div>
        </div>
    </div>

    {{-- Denominations --}}
    @php
        $pos_settings = json_decode(session('business.pos_settings', '{}'), true);
        $enable_cash_denomination_for_payment_methods = $pos_settings['enable_cash_denomination_for_payment_methods'] ?? [];
    @endphp

    @if(!empty($pos_settings['enable_cash_denomination_on']) &&
        ($pos_settings['enable_cash_denomination_on'] == 'all_screens' || !empty($show_in_pos)) &&
        !empty($show_denomination))
        
        <input type="hidden" class="enable_cash_denomination_for_payment_methods" value="{{ json_encode($enable_cash_denomination_for_payment_methods) }}">
        <div class="clearfix"></div>

        <div class="col-md-12 cash_denomination_div @if(!in_array($payment_line['method'], $enable_cash_denomination_for_payment_methods)) hide @endif">
            <hr>
            <strong>@lang('lang_v1.cash_denominations')</strong>

            @if(!empty($pos_settings['cash_denominations']))
                <table class="table table-slim">
                    <thead>
                        <tr>
                            <th width="20%" class="text-right">@lang('lang_v1.denomination')</th>
                            <th width="20%"></th>
                            <th width="20%" class="text-center">@lang('lang_v1.count')</th>
                            <th width="20%"></th>
                            <th width="20%" class="text-left">@lang('sale.subtotal')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach(explode(',', $pos_settings['cash_denominations']) as $dnm)
                            @php
                                $count = 0;
                                $sub_total = 0;
                                if (!empty($payment_line['denominations'])) {
                                    foreach ($payment_line['denominations'] as $d) {
                                        if ($d['amount'] == $dnm) {
                                            $count = $d['total_count'];
                                            $sub_total = $count * $dnm;
                                            $total += $sub_total;
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="text-right">{{ $dnm }}</td>
                                <td class="text-center">X</td>
                                <td>{!! Form::number("payment[$row_index][denominations][$dnm]", $count, [
                                    'class' => 'form-control cash_denomination input-sm',
                                    'min' => 0,
                                    'data-denomination' => $dnm,
                                    'style' => 'width: 100px; margin:auto;'
                                ]) !!}</td>
                                <td class="text-center">=</td>
                                <td class="text-left">
                                    <span class="denomination_subtotal">{{ @num_format($sub_total) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-center">@lang('sale.total')</th>
                            <td>
                                <span class="denomination_total">{{ @num_format($total) }}</span>
                                <input type="hidden" class="denomination_total_amount" value="{{ $total }}">
                                <input type="hidden" class="is_strict" value="{{ $pos_settings['cash_denomination_strict_check'] ?? '' }}">
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <p class="cash_denomination_error error hide">@lang('lang_v1.cash_denomination_error')</p>
            @else
                <p class="help-block">@lang('lang_v1.denomination_add_help_text')</p>
            @endif
        </div>
        <div class="clearfix"></div>
    @endif

    {{-- Payment Account --}}
    @if(!empty($accounts))
        <div class="{{ $col_class }}">
            <div class="form-group @if($readonly) hide @endif">
                {!! Form::label("account_$row_index", __('lang_v1.payment_account') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                    {!! Form::select("payment[$row_index][account_id]", $accounts, $payment_line['account_id'] ?? '', [
                        'class' => 'form-control select2 account-dropdown',
                        'id' => !$readonly ? "account_$row_index" : "account_advance_$row_index",
                        'style' => 'width:100%;',
                        'disabled' => $readonly
                    ]) !!}
                </div>
            </div>
        </div>
    @endif

    <div class="clearfix"></div>

    {{-- Include Payment Type Details --}}
    @include('sale_pos.partials.payment_type_details')

    {{-- Note --}}
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label("note_$row_index", __('sale.payment_note') . ':') !!}
            {!! Form::textarea("payment[$row_index][note]", $payment_line['note'] ?? '', [
                'class' => 'form-control',
                'rows' => 3,
                'id' => "note_$row_index"
            ]) !!}
        </div>
    </div>
</div>
