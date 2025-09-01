@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-4" style="margin-bottom:7rem;">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                <!-- <div class="row"> -->
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <!-- <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif"> -->
                    <!-- <div class="tw-px-3 tw-w-full  md:tw-px-0 md:tw-pr-0 md:tw-w-[70%]"> -->
                    <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-px-5 tw-w-full lg:tw-w-[40%]">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')
    @include('sale_pos.partials.pos_edit_commission_modal')


@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
@endsection

@push('scripts')
<script>
(function () {
  // Safely parse localized numeric strings (if any)
  function num(x){ x = (x==null?'':String(x)).replace(/,/g,''); var n=Number(x); return isNaN(n)?0:n; }

  // “Before tax” basis for preview. If tax isn’t available inline, we’ll just use final_total as a fallback.
  function basisBeforeTax(){
    var total = num($('#final_total_input').val());               // POS keeps this updated
    var tax   = num($('#tax_calculation_amount').val());          // hidden in totals panel
    // If your UI keeps shipping in another field, include it here if you want it excluded from basis
    return Math.max(0, total - tax);
  }

  function applyPercentToUi(percent){
    // Tell backend we’re supplying a percentage (not a fixed amount)
    $('#commission_type').val('percentage');
    $('#commission_amount').val(percent);        // <- IMPORTANT: this is a PERCENT value
    $('#commission_chosen').val('1');            // if you use this marker

    // Live preview (purely visual; totals aren’t changed)
    var computed = Math.round(basisBeforeTax() * (Math.max(0, Number(percent)) / 100) * 100) / 100;
    if (typeof __currency_trans_from_en === 'function') {
      $('#total_commission').text(__currency_trans_from_en(computed, true));
    } else {
      $('#total_commission').text(computed.toFixed(2));
    }

    // Keep the modal (if open) in sync
    $('#commission_amount_modal').val(percent);
    $('#commission_type_modal').val('percentage').trigger('change.select2');
  }

  // When user picks an agent, fetch their default percent and fill everything
  $(document).on('change', 'select[name="commission_agent"]', function(){
    var id = $(this).val();

    if (!id) {
      // Cleared: revert to server fallback (empty input) and wipe preview
      $('#commission_amount').val('');
      $('#commission_chosen').val('0');
      $('#total_commission').text('0');
      return;
    }

    $.get('{{ route('pos.agent.commission', ['user' => ':id']) }}'.replace(':id', id))
      .done(function (res) { applyPercentToUi(res && res.percent ? res.percent : 0); })
      .fail(function () { applyPercentToUi(0); });
  });

  // Recompute preview when totals/tax change
  $(document).on('keyup change', '#final_total_input, #tax_calculation_amount, #shipping_charges_modal', function(){
    var p = Number($('#commission_amount').val());
    if ($('#commission_type').val() === 'percentage' && p) applyPercentToUi(p);
  });
})();
</script>
@endpush
