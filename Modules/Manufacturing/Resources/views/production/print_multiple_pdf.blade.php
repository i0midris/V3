<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>@lang('manufacturing::lang.Xproduction_details')</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #000;
            direction: rtl;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
        }
        h2 {
            margin-top: 40px;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .no-border {
            border: none !important;
        }
    </style>
</head>
<body>

<h2>@lang('manufacturing::lang.Xproduction_details')</h2>

<table>
    <thead>
        <tr>
            <th>@lang('purchase.ref_no')</th>
            <th>@lang('messages.date')</th>
            <th>@lang('sale.product')</th>
            <th>@lang('lang_v1.quantity')</th>
            <th>@lang('manufacturing::lang.unit_cost')</th>
            <th>@lang('manufacturing::lang.total_price')</th>
            <th>@lang('purchase.business_location')</th> 

        </tr>
    </thead>
    <tbody>
        @php
            $grand_total = 0;
            $grand_quantity = 0;
        @endphp
        @foreach($transactions as $transaction)
            @foreach($transaction->purchase_lines as $line)
                @php
                    $product_name = $line->variation->product->name ?? '';
                    $sub_sku = $line->variation->sub_sku ?? '';
                    $unit_cost = $line->purchase_price_inc_tax ?? 0;
                    $qty = $line->quantity ?? 0;
                    $total = $unit_cost * $qty;
                    $grand_total += $total;
                    $grand_quantity += $qty;
                @endphp
                <tr>
                    <td>{{ $transaction->ref_no }}</td>
                    <td>{{ @format_date($transaction->transaction_date) }}</td>
                    <td>{{ $product_name }} ({{ $sub_sku }})</td>
                    <td class="text-center">{{ @format_quantity($qty) }}</td>
                    <td class="text-right">{{ @num_format($unit_cost) }}</td>
                    <td class="text-right">{{ @num_format($total) }}</td>
                    <td>{{ $transaction->location->name ?? '-' }}</td> 
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="3" class="text-right"><strong>@lang('lang_v1.total_quantity')</strong></td>
            <td class="text-center"><strong>{{ @format_quantity($grand_quantity) }}</strong></td>
            <td class="text-right"><strong>@lang('manufacturing::lang.total_cost')</strong></td>
            <td  class="text-right"><strong>{{ @num_format($grand_total) }}</strong></td>
            <td></td>
        </tr>
    </tbody>
</table>

</body>
</html>

<script>
    window.onload = function () {
        window.print();

        window.onafterprint = function () {
            setTimeout(() => window.close(), 500); 
        };

        setTimeout(() => {
            window.close();
        }, 15000); 
    };
</script>



