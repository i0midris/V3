@extends('layouts.app')

@section('title', __('accounting::lang.accounting'))

@section('content')
    @include('accounting::layouts.nav')

    <section class="content">
        <div class="row tw-mt-5">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary',
                'title' => 'الربط التلقائي :'])
                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4 tw-px-4" style="direction: rtl;">
                        <div>
                            <h5>اضافة التلقائية للحسابات في شجرة الحسابات:</h5>
                            <ul class="tw-mx-5" style="list-style-type: decimal;">
                                <li>اضافة وتعديل المستخدمون</li>
                                <li>اضافة وتعديل العملاء</li>
                                <li>اضافة وتعديل الموردين</li>
                                <li>اضافة الحسابات المالية</li>
                                <li>اضافة فئات المصاريف</li>
                            </ul>
                        </div>
                        <div>
                            <h5>اضافة تلقائية في القيود:</h5>
                            <ul class="tw-mx-5" style="list-style-type: decimal;">
                                <li>اضافة وتعديل رصيد افتتاحي للمنتجات</li>
                                <li>قيد اثبات فاتورة المشتريات</li>
                                <li>قيد اثبات مرتج فاتورة المشتريات</li>
                                <li>قيد اثبات فاتورة المبيعات</li>
                                <li>قيد اثبات مرتجع المبيعات</li>
                                <li>قيد تحويل المخزون</li>
                            </ul>
                        </div>
                        <div>
                            <h5>اضافة تلقائية لسند صرف:</h5>
                            <ul class="tw-mx-5" style="list-style-type: decimal;">
                                <li>اضافة مصاريف</li>
                                <li>اضافة قسط فاتورة مشتريات</li>
                                <li>اضافة قسط مرتجع فاتورة المبيعات</li>
                                <li>اضافة دفع مسبق للمورد</li>
                            </ul>
                        </div>
                        <div>
                            <h5>اضافة تلقائية لسند القبض:</h5>
                            <ul class="tw-mx-5" style="list-style-type: decimal;">
                                <li>اضافة قسط مرتجع فاتورة المشتريات</li>
                                <li>اضافة قسط فاتورة المبيعات</li>
                                <li>اضافة دفع مسبق للعميل</li>
                            </ul>
                        </div>

                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group pull-right">
                    <div class="input-group">
                        <button type="button" class="add-btn tw-gap-3" id="dashboard_date_filter">
                            <span>
                            <i class="fa fa-calendar tw-ml-1"></i> {{ __('messages.filter_by_date') }}
                            </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 
                'title' => __('accounting::lang.chart_of_account_overview')])
                    <div class="col-md-4">
                        <div class="table-wrapper tw-border tw-overflow-hidden" style="border-radius:0.5rem">

                            <table class="table table-striped" style="margin-bottom:0 !important">
                                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                    <tr>
                                        <th>@lang('accounting::lang.account_type')</th>
                                        <th>@lang('accounting::lang.current_balance')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($account_types as $k => $v)
                                        @php
                                            $bal = 0;
                                            foreach($coa_overview as $overview) {
                                                if($overview->account_primary_type==$k && !empty($overview->balance)) {
                                                    $bal = (float)$overview->balance;
                                                }
                                            }
                                        @endphp
    
                                        <tr>
                                            <td>
                                                {{$v['label']}}
    
                                                {{-- Suffix CR/DR as per value --}}
                                                @if($bal < 0)
                                                    {{ (in_array($v['label'], ['Asset', 'Expenses']) ? ' (CR)' : ' (DR)') }}
                                                @endif
                                            </td>
                                            <td>
                                                @format_currency(abs($bal))
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-8">
                        {!! $coa_overview_chart->container() !!}
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            @foreach($all_charts as $key => $chart)
                <div class="col-md-6">
                    @component('components.widget', ['class' => 'box-primary',
                    'title' => __('accounting::lang.' . $key)])
                        {!! $chart->container() !!}
                    @endcomponent
                </div>
            @endforeach
        </div>
    </section>
@stop

@section('javascript')
    {!! $coa_overview_chart->script() !!}
    @foreach($all_charts as $key => $chart)
        {!! $chart->script() !!}

        <script type="text/javascript">
            $(document).ready(function () {
                dateRangeSettings.startDate = moment('{{$start_date}}', 'YYYY-MM-DD');
                dateRangeSettings.endDate = moment('{{$end_date}}', 'YYYY-MM-DD');
                $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
                    $('#dashboard_date_filter span').html(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );

                    var start = $('#dashboard_date_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');

                    var end = $('#dashboard_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                    var url = "{{action([\Modules\Accounting\Http\Controllers\AccountingController::class, 'dashboard'])}}?start_date=" + start + '&end_date=' + end;

                    window.location.href = url;
                });
            });
        </script>
    @endforeach


@stop