<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ __('report.sales_representative') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            direction: rtl;
            margin: 0 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            word-wrap: break-word;
        }
        h2 {
            text-align: center;
        }
        .summary {
            margin: 20px 0;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ccc;
            line-height: 1.8;
        }
        .summary div {
            margin-bottom: 4px;
        }
        .summary .highlight {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #ccc;
            margin-top: 8px;
            padding-top: 6px;
        }
        .totals-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>

<h2>{{ __('report.sales_representative') }}</h2>

<div class="summary">
    <div style="display: flex; justify-content: space-between;">
        <div style="width: 48%;">
            <div>{{ __('report.user') }}: {{ $user_name }}</div>
            <div>{{ __('report.date_range') }}: {{ $date_range ?? __('report.all_dates') }}</div>
            <div>{{ __('report.total_sell') }}: {{ number_format($total_sales, 2) }}</div>
            <div>{{ __('report.paid') }}: {{ number_format($total_paid, 2) }}</div>
        </div>
        <div style="width: 48%; text-align: right;">
            <div>{{ __('business.business_location') }}: {{ $location_name }}</div>
            <div>{{ __('report.total_expense') }}: {{ number_format($total_expenses, 2) }}</div>
            <div>{{ __('lang_v1.total_sales_return') }}: {{ number_format($total_returns, 2) }}</div>
            <div>{{ __('report.due') }}: {{ number_format($total_due, 2) }}</div>
        </div>
    </div>
    <div class="highlight" style="text-align: center; margin-top: 12px;">
        {{ number_format($net_sales, 2) }}
    </div> 
</div>


<table>
    <thead>
        <tr>
            <th>{{ __('report.date') }}</th>
            <th>{{ __('sale.invoice_no') }}</th>
            <th>{{ __('contact.customer') }}</th>
            <th>{{ __('business.business_location') }}</th>
            <th>{{ __('sale.payment_status') }}</th>
            <th>{{ __('sale.total') }}</th>
            <th>{{ __('report.paid') }}</th>
            <th>{{ __('report.due') }}</th>
            <th>{{ __('report.invoice_age') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $row_total = 0;
            $row_paid = 0;
            $row_due = 0;
        @endphp
        @foreach ($sales as $sale)
            @php
                $row_total += $sale->final_total;
                $row_paid += $sale->total_paid ?? 0;
                $row_due += $sale->total_due ?? 0;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($sale->transaction_date)->format('Y-m-d H:i') }}</td>
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ $sale->contact->name ?? '' }}</td>
                <td>{{ $sale->location->name ?? '' }}</td>
                <td>{{ __('lang_v1.' . $sale->payment_status) }}</td>
                <td>{{ number_format($sale->final_total, 2) }}</td>
                <td>{{ number_format($sale->total_paid ?? 0, 2) }}</td>
                <td>{{ number_format($sale->total_due ?? 0, 2) }}</td>
                @php
    $created_at_date = \Carbon\Carbon::parse($sale->created_at)->startOfDay();
    $today = \Carbon\Carbon::now()->startOfDay();
    $days = $created_at_date->diffInDays($today);
@endphp
<td>{{ $days }} {{ __('يوم') }}</td>

            </tr>
        @endforeach
        <tr class="totals-row">
            <td colspan="5">{{ __('report.total') }}</td>
            <td>{{ number_format($row_total, 2) }}</td>
            <td>{{ number_format($row_paid, 2) }}</td>
            <td>{{ number_format($row_due, 2) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
<div style="margin-top: 20px; text-align: center; font-size: 11px; color: #000;">
    {{ __('report.printed_on') }}: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }} |
    {{ config('app.name') }}
</div>

</body>
</html>
