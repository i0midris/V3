<style>
    #summaryChart {
        height: 300px !important;
        max-height: 400px;
        width: 100% !important;
        margin-top: 20px;
        margin-bottom: 20px;
        page-break-inside: avoid !important;
        break-inside: avoid;
        visibility: visible;
    }

    table {
        page-break-inside: auto;
        width: 100%;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }
</style>

@extends('layouts.app')
@section('title', __('report.location_summary'))

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        {{ __('report.location_summary') }}
    </h1>
</section>

<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id[]', $business_locations, null, ['class' => 'form-control select2', 'multiple', 'id' => 'location_id']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('date_range', __('report.date_range') . ':') !!}
                {!! Form::text('date_range', null, ['class' => 'form-control', 'id' => 'date_range', 'readonly']) !!}
            </div>
        </div>
        <div class="col-md-12 tw-mt-4">
            <label>
                <input type="checkbox" id="include_chart" checked>
                {{ __('report.include_chart') ?? 'Include Chart in PDF' }}
            </label>
            <button class="add-btn !tw-text-xs pull-right" id="export_pdf">
                <i class="fa fa-file-pdf-o"></i> {{ __('report.export_pdf') ?? 'Export PDF' }}
            </button>
        </div> 
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div id="report-print-wrapper">
            <div id="report-results"></div>
            <div class=" tw-grid tw-w-full tw-overflow-x-auto tw-bg-white tw-px-2 tw-pb-4 tw-pt-2 tw-border-2 tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 tw-mt-5">
                <canvas id="summaryChart" height="100"></canvas>
            </div>
        </div>
    @endcomponent
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        let chart;

        $('#location_id, #date_range').change(fetchLocationSummary);

        $('#date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                fetchLocationSummary();
            }
        );

        $('#date_range').on('cancel.daterangepicker', function () {
            $('#date_range').val('');
            fetchLocationSummary();
        });

        $('#export_pdf').click(function () {
            const includeChart = $('#include_chart').is(':checked');
            const chartCanvas = $('#summaryChart');

            if (!includeChart) {
                chartCanvas.css('visibility', 'hidden');
            } else {
                chartCanvas.css('visibility', 'visible');
                if (chart) {
                    chart.resize();
                    chart.update('none');
                }
            }

            setTimeout(() => {
                const element = document.getElementById('report-print-wrapper');
                html2pdf().set({
                    margin: 0.5,
                    filename: 'location_summary_report.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: {
                        scale: 3,
                        useCORS: true,
                        allowTaint: true,
                        scrollX: 0,
                        scrollY: 0,
                        logging: false
                    },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
                }).from(element).save().then(() => {
                    chartCanvas.css('visibility', 'visible');
                });
            }, 400);
        });

        function fetchLocationSummary() {
            const dateRange = $('#date_range').val();
            let startDate = '', endDate = '';

            if (dateRange && dateRange.includes('~')) {
                [startDate, endDate] = dateRange.split('~').map(d => d.trim());
            }

            $.ajax({
                url: '{{ action("\\App\\Http\\Controllers\\ReportController@getLocationSummary") }}',
                data: {
                    location_id: $('#location_id').val(),
                    start_date: startDate,
                    end_date: endDate
                },
                success: function (response) {
                    renderTables(response.data);
                    renderChart(response.data);
                }
            });
        }

        function renderTables(data) {
            const container = $('#report-results');
            container.empty();

            data.forEach(function (locationReport) {
                const locationId = locationReport.location_id;
                const rows = locationReport.data;

                let tableHtml = `
                    <h3 class="tw-text-lg tw-font-bold mt-4">${rows[0]?.location_name || 'Location ' + locationId}</h3>
                    <div class="table-responsive">
                        <table class="table tw-border table-striped">
                            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                <tr>
                                    <th>{{ __('report.product') }}</th>
                                    <th>{{ __('report.current_stock') }}</th>
                                    <th>{{ __('report.total_sold') }}</th>
                                    <th>{{ __('report.total_adjusted') }}</th>
                                    <th>{{ __('report.total_purchase_value') }}</th>
                                    <th>{{ __('report.total_sales_value') }}</th>
                                    <th>{{ __('report.total_return_qty') }}</th>
                                    <th>{{ __('report.total_return_value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                const totals = {
                    stock: 0,
                    sold: 0,
                    adjusted: 0,
                    purchase: 0,
                    sales: 0,
                    return_qty: 0,
                    return_val: 0
                };

                rows.forEach(function (row) {
                    totals.stock += parseFloat(row.current_stock);
                    totals.sold += parseFloat(row.total_sold);
                    totals.adjusted += parseFloat(row.total_adjusted);
                    totals.purchase += parseFloat(row.total_purchase_value);
                    totals.sales += parseFloat(row.total_sales_value);
                    totals.return_qty += parseFloat(row.total_return_qty);
                    totals.return_val += parseFloat(row.total_return_value);

                    tableHtml += `
                        <tr>
                            <td>${row.product_name}</td>
                            <td>${parseFloat(row.current_stock).toFixed(2)}</td>
                            <td>${parseFloat(row.total_sold).toFixed(2)}</td>
                            <td>${parseFloat(row.total_adjusted).toFixed(2)}</td>
                            <td>${parseFloat(row.total_purchase_value).toFixed(2)}</td>
                            <td>${parseFloat(row.total_sales_value).toFixed(2)}</td>
                            <td>${parseFloat(row.total_return_qty).toFixed(2)}</td>
                            <td>${parseFloat(row.total_return_value).toFixed(2)}</td>
                        </tr>
                    `;
                });

                tableHtml += `</tbody>
                    <tfoot style="background-color:#e9ecef;">
                        <tr>
                            <th>{{ __('report.total') }}</th>
                            <th>${totals.stock.toFixed(2)}</th>
                            <th>${totals.sold.toFixed(2)}</th>
                            <th>${totals.adjusted.toFixed(2)}</th>
                            <th>${totals.purchase.toFixed(2)}</th>
                            <th>${totals.sales.toFixed(2)}</th>
                            <th>${totals.return_qty.toFixed(2)}</th>
                            <th>${totals.return_val.toFixed(2)}</th>
                        </tr>
                    </tfoot>
                </table></div>`;

                container.append(tableHtml);
            });


            
            
        }

        function renderChart(data) {
            const labels = [];
            const salesTotals = [];
            const returnTotals = [];

            data.forEach(locationReport => {
                const rows = locationReport.data;
                let totalSales = 0;
                let totalReturns = 0;

                rows.forEach(row => {
                    totalSales += parseFloat(row.total_sales_value || 0);
                    totalReturns += parseFloat(row.total_return_value || 0);
                });

                labels.push(rows[0]?.location_name || 'Location ' + locationReport.location_id);
                salesTotals.push(parseFloat(totalSales).toFixed(2));
                returnTotals.push(parseFloat(totalReturns).toFixed(2));
            });

            const ctx = document.getElementById('summaryChart').getContext('2d');
            if (chart) chart.destroy();

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '{{ __('report.total_sales_value') }}',
                            data: salesTotals,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)'
                        },
                        {
                            label: '{{ __('report.total_return_value') }}',
                            data: returnTotals,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        

        fetchLocationSummary();
    });
</script>
@endsection
