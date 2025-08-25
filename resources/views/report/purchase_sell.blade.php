@extends('layouts.app')
@section('title', __( 'report.purchase_sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang( 'report.purchase_sell' )
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang( 'report.purchase_sell_msg' )</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="print_section"><h2>{{session()->get('business.name')}} - @lang( 'report.purchase_sell' )</h2></div>
    <div class="row no-print">
        <div class="col-md-6 col-sm-6 col-sm-6">
            <div class="tw-flex tw-items-center">
                <span class="add-btn custom-addon">
                    <!-- <i class="fa fa-map-marker"></i></span> -->
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.364 4.636a9 9 0 0 1 .203 12.519l-.203 .21l-4.243 4.242a3 3 0 0 1 -4.097 .135l-.144 -.135l-4.244 -4.243a9 9 0 0 1 12.728 -12.728zm-6.364 3.364a3 3 0 1 0 0 6a3 3 0 0 0 0 -6z" /></svg>
                </span>
                 <select class="form-control select2" id="purchase_sell_location_filter">
                    @foreach($business_locations as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6 col-md-offset-2 col-md-4">
            <div class="form-group pull-right">
                <div class="input-group">
                  <button type="button" class="add-btn tw-gap-3" id="purchase_sell_date_filter">
                    <span class="tw-flex tw-items-center tw-gap-1">
                      <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-calendar-week"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16 2c.183 0 .355 .05 .502 .135l.033 .02c.28 .177 .465 .49 .465 .845v1h1a3 3 0 0 1 2.995 2.824l.005 .176v12a3 3 0 0 1 -2.824 2.995l-.176 .005h-12a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-12a3 3 0 0 1 2.824 -2.995l.176 -.005h1v-1a1 1 0 0 1 .514 -.874l.093 -.046l.066 -.025l.1 -.029l.107 -.019l.12 -.007q .083 0 .161 .013l.122 .029l.04 .012l.06 .023c.328 .135 .568 .44 .61 .806l.007 .117v1h6v-1a1 1 0 0 1 1 -1m3 7h-14v9.625c0 .705 .386 1.286 .883 1.366l.117 .009h12c.513 0 .936 -.53 .993 -1.215l.007 -.16z" /><path d="M9.015 13a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /><path d="M13.015 13a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /><path d="M17.02 13a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /><path d="M12.02 15a1 1 0 0 1 0 2a1.001 1.001 0 1 1 -.005 -2z" /><path d="M9.015 16a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /></svg>
                       {{ __('messages.filter_by_date') }}
                    </span>
                    <i class="fa fa-caret-down"></i>
                  </button>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            @component('components.widget', ['title' => __('purchase.purchases')])
                <table class="table table-striped">
                    <tr>
                        <th>{{ __('report.total_purchase') }}:</th>
                        <td>
                            <span class="total_purchase">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('report.purchase_inc_tax') }}:</th>
                        <td>
                             <span class="purchase_inc_tax">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('lang_v1.total_purchase_return_inc_tax') }}:</th>
                        <td>
                             <span class="purchase_return_inc_tax">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('report.purchase_due') }}: @show_tooltip(__('tooltip.purchase_due'))</th>
                        <td>
                             <span class="purchase_due">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                </table>
            @endcomponent
        </div>

        <div class="col-md-6">
            @component('components.widget', ['title' => __('sale.sells')])
                <table class="table table-striped">
                    <tr>
                        <th>{{ __('report.total_sell') }}:</th>
                        <td>
                            <span class="total_sell">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('report.sell_inc_tax') }}:</th>
                        <td>
                             <span class="sell_inc_tax">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('lang_v1.total_sell_return_inc_tax') }}:</th>
                        <td>
                             <span class="total_sell_return">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('report.sell_due') }}: @show_tooltip(__('tooltip.sell_due'))</th>
                        <td>
                            <span class="sell_due">
                                <i class="fas fa-sync fa-spin fa-fw"></i>
                            </span>
                        </td>
                    </tr>
                </table>
            @endcomponent
        </div>
    </div>

    <div class="row tw-px-4">
        <div>
            @component('components.widget')
                @slot('title')
                    {{ __('lang_v1.overall') }} 
                    ((@lang('business.sale') - @lang('lang_v1.sell_return')) - (@lang('lang_v1.purchase') - @lang('lang_v1.purchase_return')) ) 
                    @show_tooltip(__('tooltip.over_all_sell_purchase'))
                @endslot
                <h4 class="text-muted">
                    {{ __('report.sell_minus_purchase') }}: 
                    <span class="sell_minus_purchase">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                </h4>

                <h4 class="text-muted">
                    {{ __('report.difference_due') }}: 
                    <span class="difference_due">
                        <i class="fas fa-sync fa-spin fa-fw"></i>
                    </span>
                </h4>
            @endcomponent
        </div>
    </div>
    <div class="row no-print">
        <div class="col-sm-12 tw-mt-4">
            <button class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right" aria-label="Print"
                onclick="window.print();">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-printer">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                    <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                    <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                </svg> @lang('messages.print')
            </button>
        </div>
    </div>
	

</section>
<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

@endsection
