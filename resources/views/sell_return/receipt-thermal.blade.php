<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $receipt_details->invoice_no ?? 'Receipt' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Courier New", Courier, monospace;
            font-size: 11px;
            direction: rtl;
            color: #000;
        }

        .receipt-wrapper {
            max-width: 80mm;
            margin: auto;
            padding: 10px;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: auto;
            word-wrap: break-word;
        }

        td {
            vertical-align: top;
            padding: 2px 0;
            word-break: break-word;
            white-space: normal;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer, .authorized-signatory {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            font-size: 10px;
        }

        .barcode {
            display: block;
            margin: 10px auto;
        }

        @media print {
            body, html {
                margin: 0;
                padding: 0;
            }

            .receipt-wrapper {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="receipt-wrapper">

    {{-- Header --}}
    @if(!empty($receipt_details->invoice_heading))
        <p class="text-center"><strong>{{ $receipt_details->invoice_heading }}</strong></p>
    @endif

    <p class="text-center">
        @if(!empty($receipt_details->invoice_no_prefix))
            {!! $receipt_details->invoice_no_prefix !!}
        @endif
        {{ $receipt_details->invoice_no }}
    </p>

    {{-- Business Info --}}
    @if(!empty($receipt_details->logo))
        <img src="{{ $receipt_details->logo }}" class="img">
    @endif

    @if(!empty($receipt_details->display_name))
        <p class="text-center">
            {{ $receipt_details->display_name }}<br>
            {!! $receipt_details->address !!}<br>
            @if(!empty($receipt_details->contact)){{ $receipt_details->contact }}<br>@endif
            @if(!empty($receipt_details->website)){{ $receipt_details->website }}<br>@endif
            @if(!empty($receipt_details->tax_info1)){{ $receipt_details->tax_label1 }} {{ $receipt_details->tax_info1 }}<br>@endif
            @if(!empty($receipt_details->tax_info2)){{ $receipt_details->tax_label2 }} {{ $receipt_details->tax_info2 }}<br>@endif
        </p>
    @endif

    {{-- Customer --}}
    @if(!empty($receipt_details->customer_name))
        <p>
            <strong>{{ $receipt_details->customer_label ?? 'العميل' }}:</strong><br>
            {!! $receipt_details->customer_name !!}<br>
            @if(!empty($receipt_details->customer_info)) {!! $receipt_details->customer_info !!}<br>@endif
            @if(!empty($receipt_details->client_id_label)){{ $receipt_details->client_id_label }} {{ $receipt_details->client_id }}<br>@endif
            @if(!empty($receipt_details->customer_tax_label)){{ $receipt_details->customer_tax_label }} {{ $receipt_details->customer_tax_number }}<br>@endif
        </p>
    @endif

    {{-- Items --}}
    <table>
        <tbody>
        @foreach($receipt_details->lines as $line)
            <tr>
                <td>
                    <strong>{{ $loop->iteration }}. {{ $line->name }} {{ $line->variation }}</strong><br>
                    @if(!empty($line->sub_sku)) كود: {{ $line->sub_sku }}<br>@endif
                    @if(!empty($line->brand)) الماركة: {{ $line->brand }}<br>@endif
                    @if(!empty($line->sell_line_note)) ملاحظة: {{ $line->sell_line_note }}<br>@endif

                    الكمية: {{ $line->quantity }} {{ $line->units }}<br>
                    السعر: {{ $line->unit_price_exc_tax }}<br>
                    @if(!empty($line->tax))
                        الضريبة: {{ optional($line->tax)->name }} ({{ optional($line->tax)->amount }}%)<br>
                        {{ @num_format($line->item_tax ?? 0) }}<br>
                    @endif
                    الإجمالي: {{ $line->line_total }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <hr>
    <table>
        <tbody>
            <tr>
                <td>المجموع الفرعي:</td>
                <td class="text-right">{{ $receipt_details->subtotal }}</td>
            </tr>
            @if(!empty($receipt_details->taxes))
                @foreach($receipt_details->taxes as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        <td class="text-right">{{ $value }}</td>
                    </tr>
                @endforeach
            @endif
            @if(!empty($receipt_details->discount))
                <tr>
                    <td>الخصم:</td>
                    <td class="text-right">(-) {{ $receipt_details->discount }}</td>
                </tr>
            @endif
            @if(!empty($receipt_details->group_tax_details))
                @foreach($receipt_details->group_tax_details as $key => $value)
                    <tr>
                        <td>{{ $key }}</td>
                        <td class="text-right">(+) {{ $value }}</td>
                    </tr>
                @endforeach
            @elseif(!empty($receipt_details->tax))
                <tr>
                    <td>{{ $receipt_details->tax_label }}</td>
                    <td class="text-right">(+) {{ $receipt_details->tax }}</td>
                </tr>
            @endif
            <tr>
                <th>المجموع:</th>
                <th class="text-right">{{ $receipt_details->total }}</th>
            </tr>
        </tbody>
    </table>

    {{-- Payment Info --}}
    @if(!empty($receipt_details->total_paid))
        <p><strong>المدفوع:</strong> {{ $receipt_details->total_paid }}</p>
    @endif

    @if(!empty($receipt_details->total_due))
        <p><strong>المتبقي:</strong> {{ $receipt_details->total_due }}</p>
    @endif

    @if(!empty($receipt_details->date_label))
        <p><strong>{{ $receipt_details->date_label }}:</strong> {{ $receipt_details->invoice_date }}</p>
    @endif

    {{-- Barcode --}}
    مسينتبيتبت
    @if($receipt_details->show_barcode)
        <img class="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,[39,48,54], true) }}">
    @endif

    {{-- Footer --}}
    @if(!empty($receipt_details->footer_text))
        <div class="footer">{!! $receipt_details->footer_text !!}</div>
    @endif

    <p class="authorized-signatory">Authorized Signatory</p>
</div>
</body>
</html>
