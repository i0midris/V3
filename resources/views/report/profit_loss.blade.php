@extends('layouts.app')
@section('title', __('report.profit_loss'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('report.profit_loss')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="print_section">
            <h2>{{ session()->get('business.name') }} - @lang('report.profit_loss')</h2>
        </div>

        <div class="row no-print">
            <div class="col-md-6 col-sm-6 col-sm-6">
                <div class="tw-flex tw-items-center">
                    <span class="add-btn custom-addon">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.364 4.636a9 9 0 0 1 .203 12.519l-.203 .21l-4.243 4.242a3 3 0 0 1 -4.097 .135l-.144 -.135l-4.244 -4.243a9 9 0 0 1 12.728 -12.728zm-6.364 3.364a3 3 0 1 0 0 6a3 3 0 0 0 0 -6z" /></svg>
                    </span>
                    <select class="form-control select2" id="profit_loss_location_filter">
                        @foreach($business_locations as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-sm-6 col-md-offset-2 col-md-4">
                <div class="form-group pull-right">
                    <div class="input-group">
                    <button type="button" class="add-btn tw-gap-3" id="profit_loss_date_filter">
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
        <div class="row">
            <div id="pl_data_div">
            </div>
        </div>   
        <div class="row no-print">
            <div class="col-sm-12 tw-mb-2">
                {{-- <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            ><i class="fa fa-print"></i> @lang( 'messages.print' )</button> --}}

                <button class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right" aria-label="Print" onclick="window.print();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-printer">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                    </svg> @lang('messages.print')
                </button>
            </div>
        </div>
        <div class="row no-print">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="tw-rounded-lg tw-overflow-hidden tw-bg-white">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs nav-justified custom-nav-tabs">
                            <li class="active">
                                <a href="#profit_by_products" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_products')</a>
                            </li>

                            <li>
                                <a href="#profit_by_categories" data-toggle="tab" aria-expanded="true"><i class="fa fa-tags"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_categories')</a>
                            </li>

                            <li>
                                <a href="#profit_by_brands" data-toggle="tab" aria-expanded="true"><i class="fa fa-diamond"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_brands')</a>
                            </li>

                            <li>
                                <a href="#profit_by_locations" data-toggle="tab" aria-expanded="true"><i
                                        class="fa fa-map-marker" aria-hidden="true"></i> @lang('lang_v1.profit_by_locations')</a>
                            </li>

                            <li>
                                <a href="#profit_by_invoice" data-toggle="tab" aria-expanded="true"><i class="fa fa-file-alt"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_invoice')</a>
                            </li>

                            <li>
                                <a href="#profit_by_date" data-toggle="tab" aria-expanded="true"><i class="fa fa-calendar"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_date')</a>
                            </li>
                            <li>
                                <a href="#profit_by_customer" data-toggle="tab" aria-expanded="true"><i class="fa fa-user"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_customer')</a>
                            </li>
                            <li>
                                <a href="#profit_by_day" data-toggle="tab" aria-expanded="true"><i class="fa fa-calendar"
                                        aria-hidden="true"></i> @lang('lang_v1.profit_by_day')</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tw-p-4">
                        <div class="tab-pane active" id="profit_by_products">
                            @include('report.partials.profit_by_products')
                        </div>

                        <div class="tab-pane" id="profit_by_categories">
                            @include('report.partials.profit_by_categories')
                        </div>

                        <div class="tab-pane" id="profit_by_brands">
                            @include('report.partials.profit_by_brands')
                        </div>

                        <div class="tab-pane" id="profit_by_locations">
                            @include('report.partials.profit_by_locations')
                        </div>

                        <div class="tab-pane" id="profit_by_invoice">
                            @include('report.partials.profit_by_invoice')
                        </div>

                        <div class="tab-pane" id="profit_by_date">
                            @include('report.partials.profit_by_date')
                        </div>

                        <div class="tab-pane" id="profit_by_customer">
                            @include('report.partials.profit_by_customer')
                        </div>

                        <div class="tab-pane" id="profit_by_day">

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            profit_by_products_table = $('#profit_by_products_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                dom: `
                    <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                        <"drps-section tw-flex tw-gap-2 tw-items-center"
                            <" tw-flex tw-items-center tw-gap-2"B>
                            l
                        >
                        f
                    >
                    rt
                    <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                `,
                "ajax": {
                    "url": "/reports/get-profit/product",
                    "data": function(d) {
                        d.start_date = $('#profit_loss_date_filter')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        d.end_date = $('#profit_loss_date_filter')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                        d.location_id = $('#profit_loss_location_filter').val();
                    }
                },
                columns: [{
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'gross_profit',
                        "searchable": false
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    var total_profit = 0;
                    for (var r in data) {
                        total_profit += $(data[r].gross_profit).data('orig-value') ?
                            parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
                    }

                    $('#profit_by_products_table .footer_total').html(__currency_trans_from_en(
                        total_profit));
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr('href');
                if (target == '#profit_by_categories') {
                    if (typeof profit_by_categories_datatable == 'undefined') {
                        profit_by_categories_datatable = $('#profit_by_categories_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            dom: `
                                <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                                    <"drps-section tw-flex tw-gap-2 tw-items-center"
                                        <" tw-flex tw-items-center tw-gap-2"B>
                                        l
                                    >
                                    f
                                >
                                rt
                                <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                            `,

                            "ajax": {
                                "url": "/reports/get-profit/category",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'category',
                                    name: 'C.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_categories_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_categories_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_brands') {
                    if (typeof profit_by_brands_datatable == 'undefined') {
                        profit_by_brands_datatable = $('#profit_by_brands_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            dom: `
                                <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                                    <"drps-section tw-flex tw-gap-2 tw-items-center"
                                        <" tw-flex tw-items-center tw-gap-2"B>
                                        l
                                    >
                                    f
                                >
                                rt
                                <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                            `,
                            "ajax": {
                                "url": "/reports/get-profit/brand",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'brand',
                                    name: 'B.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_brands_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_brands_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_locations') {
                    if (typeof profit_by_locations_datatable == 'undefined') {
                        profit_by_locations_datatable = $('#profit_by_locations_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            dom: `
                                <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                                    <"drps-section tw-flex tw-gap-2 tw-items-center"
                                        <" tw-flex tw-items-center tw-gap-2"B>
                                        l
                                    >
                                    f
                                >
                                rt
                                <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                            `,
                            "ajax": {
                                "url": "/reports/get-profit/location",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'location',
                                    name: 'L.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_locations_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_locations_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_invoice') {
                    if (typeof profit_by_invoice_datatable == 'undefined') {
                        profit_by_invoice_datatable = $('#profit_by_invoice_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            dom: `
                                <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                                    <"drps-section tw-flex tw-gap-2 tw-items-center"
                                        <" tw-flex tw-items-center tw-gap-2"B>
                                        l
                                    >
                                    f
                                >
                                rt
                                <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                            `,
                            "ajax": {
                                "url": "/reports/get-profit/invoice",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'invoice_no',
                                    name: 'sale.invoice_no'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_invoice_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_invoice_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_date') {
                    if (typeof profit_by_date_datatable == 'undefined') {
                        profit_by_date_datatable = $('#profit_by_date_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            dom: `
                                <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                                    <"drps-section tw-flex tw-gap-2 tw-items-center"
                                        <" tw-flex tw-items-center tw-gap-2"B>
                                        l
                                    >
                                    f
                                >
                                rt
                                <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                            `,
                            "ajax": {
                                "url": "/reports/get-profit/date",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'transaction_date',
                                    name: 'sale.transaction_date'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_date_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_date_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_customer') {
                    if (typeof profit_by_customers_table == 'undefined') {
                        profit_by_customers_table = $('#profit_by_customer_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            dom: `
                                <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                                    <"drps-section tw-flex tw-gap-2 tw-items-center"
                                        <" tw-flex tw-items-center tw-gap-2"B>
                                        l
                                    >
                                    f
                                >
                                rt
                                <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                            `,
                            "ajax": {
                                "url": "/reports/get-profit/customer",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'customer',
                                    name: 'CU.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_customer_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_customers_table.ajax.reload();
                    }
                } else if (target == '#profit_by_day') {
                    var start_date = $('#profit_loss_date_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');

                    var end_date = $('#profit_loss_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                    var location_id = $('#profit_loss_location_filter').val();

                    var url = '/reports/get-profit/day?start_date=' + start_date + '&end_date=' + end_date +
                        '&location_id=' + location_id;
                    $.ajax({
                        url: url,
                        dataType: 'html',
                        success: function(result) {
                            $('#profit_by_day').html(result);
                            profit_by_days_table = $('#profit_by_day_table').DataTable({
                                "searching": false,
                                'paging': false,
                                'ordering': false,
                                dom: `
                                    <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-justify-end tw-items-center tw-gap-2"B>
                                    rt
                                    <"tw-mt-4 tw-flex tw-justify-end tw-items-center"i>
                                `,
                            });
                            var total_profit = sum_table_col($('#profit_by_day_table'),
                                'gross-profit');
                            $('#profit_by_day_table .footer_total').text(total_profit);
                            __currency_convert_recursively($('#profit_by_day_table'));
                        },
                    });
                } else if (target == '#profit_by_products') {
                    profit_by_products_table.ajax.reload();
                }
            });
        });
    </script>

@endsection
