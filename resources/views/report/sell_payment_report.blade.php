@extends('layouts.app')
@section('title', __('lang_v1.sell_payment_report'))

@section('content')

<!-- Content Header (Page header) --> 
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('lang_v1.sell_payment_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
           @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => '#', 'method' => 'get', 'id' => 'sell_payment_report_form' ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                        {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2',  'style' => 'width:100%', 'placeholder' => __('messages.all'), 'required']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2',  'style' => 'width:100%', 'placeholder' => __('messages.all'), 'required']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('payment_types', __('lang_v1.payment_method').':') !!}
                        {!! Form::select('payment_types', $payment_types, null, ['class' => 'form-control select2','style' => 'width:100%', 'placeholder' => __('messages.all'), 'required']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('customer_group_filter', __('lang_v1.customer_group').':') !!}
                        {!! Form::select('customer_group_filter', $customer_groups, null, ['class' => 'form-control select2','style' => 'width:100%']); !!}
                    </div>
                </div>
                <br>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('currency_code', __('lang_v1.currency') . ':') !!}
                       {!! Form::select('currency_code', $currencies, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('messages.all'),
                            'id' => 'currency_code'
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('spr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'spr_date_filter', 'readonly']); !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="sell_payment_report_table">
                        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                            <tr>
                                <th>&nbsp;</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.paid_on')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('lang_v1.customer_group')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('sale.sale')</th>
                                <th>Base Total</th> {{-- NEW --}}
                                <th>Currency</th>   {{-- NEW --}}
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tfoot style="background-color: #e9ecef;">
                            <tr class="footer-total text-center">
                                <td colspan="3"><strong>@lang('sale.total'):</strong></td>
                                <td><span class="display_currency" id="footer_total_amount" data-currency_symbol="true"></span></td>
                                <td colspan="4"></td>
                                <td><span class="display_currency" id="footer_base_total" data-currency_symbol="true"></span></td> {{-- 👈 Ensure this exists --}}
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>

                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection