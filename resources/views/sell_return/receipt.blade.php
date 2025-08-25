<style>
    /* === Reset & Base === */
* {
    box-sizing: border-box;
}

html, body {
    margin: 0;
    padding: 0;
    font-family: "Courier New", Courier, monospace;
    font-size: 12px;
    direction: rtl;
    background: #fff;
    color: #000;
    line-height: 1.5;
}

/* === Wrapper === */
.receipt-wrapper.full-page-print {
    width: 100%;
    max-width: 210mm;
    margin: 0 auto;
    padding: 12px;
}

/* === Images === */
img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* === Barcode === */
.barcode {
    margin: 10px auto;
    text-align: center;
}

/* === Product Table === */
.table-products {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 10px;
    table-layout: fixed;
}

.table-products th,
.table-products td {
    border: 1px solid #999;
    padding: 6px 4px;
    text-align: center;
    vertical-align: middle;
    word-break: break-word;
}

.table-products th {
    background-color: #f2f2f2;
    font-weight: bold;
}

/* Column widths (adjust for 80mm compatibility) */
.table-products th:nth-child(1),
.table-products td:nth-child(1) { width: 5%; }    /* م */
.table-products th:nth-child(2),
.table-products td:nth-child(2) { width: 35%; }   /* المنتج */
.table-products th:nth-child(3),
.table-products td:nth-child(3) { width: 15%; }   /* الكمية */
.table-products th:nth-child(4),
.table-products td:nth-child(4) { width: 20%; }   /* السعر */
.table-products th:nth-child(5),
.table-products td:nth-child(5) { width: 25%; }   /* الإجمالي */

/* === Totals Table === */
.table-totals {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 12px;
    font-size: 12px;
}

.table-totals td,
.table-totals th {
    padding: 4px 6px;
    text-align: right;
    border: none;
    font-weight: normal;
}

/* Last row (total) styling */
.table-totals tr:last-child td,
.table-totals tr:last-child th {
    font-weight: bold;
    border-top: 1px solid #000;
}

/* === Text Utilities === */
.text-right { text-align: right !important; }
.text-left  { text-align: left !important; }
.text-center { text-align: center !important; }

/* === Footer === */
.footer {
    text-align: center;
    font-size: 11px;
    margin-top: 20px;
    white-space: pre-line;
}

.authorized-signatory {
    margin-top: 20px;
    font-weight: bold;
    text-align: center;
}

/* === No-Print Elements === */
.no-print {
    display: none !important;
}

/* === Print Rules === */
@media print {
    html, body {
        width: auto;
        height: auto;
        margin: 0;
        padding: 0;
        background: #fff;
    }

    .receipt-wrapper.full-page-print {
        width: 100%;
        padding: 0;
        margin: 0;
    }

    .table-products th,
    .table-products td,
    .table-totals td,
    .table-totals th {
        font-size: 11px;
        padding: 4px 3px;
    }

    table, tr, td, th {
        page-break-inside: avoid;
    }

    thead {
        display: table-header-group;
    }

    .footer {
        margin-top: 10px;
    }

    .barcode {
        margin: 8px auto;
    }

    .no-print {
        display: none !important;
    }
}

/* === Responsive for Small Widths (80mm Roll) === */
@media screen and (max-width: 400px), print and (max-width: 80mm) {
    html, body {
        width: 72mm;
    }

    .receipt-wrapper.full-page-print {
        padding: 6px;
    }

    .table-products th,
    .table-products td,
    .table-totals td,
    .table-totals th {
        font-size: 11px;
        padding: 4px 2px;
    }
}

</style>


<div class="receipt-wrapper full-page-print">

    {{-- Invoice Heading --}}
    @if(!empty($receipt_details->invoice_heading))
        <p class="text-right">{{ $receipt_details->invoice_heading }}</p>
    @endif

    {{-- Invoice Number --}}
    <p class="text-right">
        @if(!empty($receipt_details->invoice_no_prefix))
            {!! $receipt_details->invoice_no_prefix !!}
        @endif
        {{ $receipt_details->invoice_no }}
    </p>

    {{-- Header Text --}}
    @if(!empty($receipt_details->header_text))
        <div>{!! $receipt_details->header_text !!}</div>
    @endif

    {{-- Business Info --}}
    <div>
        @if(!empty($receipt_details->logo))
            <img src="{{ $receipt_details->logo }}" alt="Logo">
        @endif

        @if(!empty($receipt_details->display_name))
            <p>
                {{ $receipt_details->display_name }}<br>
                {!! $receipt_details->address !!}
                @if(!empty($receipt_details->contact))<br>{{ $receipt_details->contact }}@endif
                @if(!empty($receipt_details->website))<br>{{ $receipt_details->website }}@endif
                @if(!empty($receipt_details->tax_info1))<br>{{ $receipt_details->tax_label1 }} {{ $receipt_details->tax_info1 }}@endif
                @if(!empty($receipt_details->tax_info2))<br>{{ $receipt_details->tax_label2 }} {{ $receipt_details->tax_info2 }}@endif
                @if(!empty($receipt_details->location_custom_fields))<br>{{ $receipt_details->location_custom_fields }}@endif
            </p>
        @endif
    </div>

    {{-- Table Info --}}
    @if(!empty($receipt_details->table_label) || !empty($receipt_details->waiter_label))
        <p>
            {!! $receipt_details->table_label ?? '' !!} {{ $receipt_details->table ?? '' }}<br>
            {!! $receipt_details->waiter_label ?? '' !!} {{ $receipt_details->waiter ?? '' }}
        </p>
    @endif

    {{-- Customer Info --}}
    <div>
        <strong>{{ $receipt_details->customer_label ?? '' }}</strong><br>

        @if(!empty($receipt_details->customer_name))
            {!! $receipt_details->customer_name !!}<br>
        @endif

        @if(!empty($receipt_details->customer_info))
            {!! $receipt_details->customer_info !!}<br>
        @endif

        @if(!empty($receipt_details->client_id_label))
            {{ $receipt_details->client_id_label }} {{ $receipt_details->client_id }}<br>
        @endif

        @if(!empty($receipt_details->customer_tax_label))
            {{ $receipt_details->customer_tax_label }} {{ $receipt_details->customer_tax_number }}<br>
        @endif

        @if(!empty($receipt_details->customer_custom_fields))
            {!! $receipt_details->customer_custom_fields !!}<br>
        @endif
    </div>

    {{-- Subheadings --}}
    @for ($i = 1; $i <= 5; $i++)
        @php $line = 'sub_heading_line' . $i; @endphp
        @if(!empty($receipt_details->$line))
            <p>{{ $receipt_details->$line }}</p>
        @endif
    @endfor

    {{-- Items Table --}}
    <table class="table-products">
        <thead>
            <tr>
                <th>م</th>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt_details->lines as $line)
			<tr>
    <td>{{ $loop->iteration }}</td>
    <td>
        {{ $line->name }}
        @if(!empty($line->variation) && trim(strtoupper($line->variation)) !== 'DUMMY')
            {{ $line->variation }}
        @endif
        @if(!empty($line->product_variation) && trim(strtoupper($line->product_variation)) !== 'DUMMY')
            {{ $line->product_variation }}
        @endif
        @if(!empty($line->sub_sku))<br>{{ $line->sub_sku }}@endif
        @if(!empty($line->brand))<br>{{ $line->brand }}@endif
        @if(!empty($line->sell_line_note))<br>({{ $line->sell_line_note }})@endif
    </td>
    <td>{{ $line->quantity }} {{ $line->units }}</td>
    <td>{{ $line->unit_price_exc_tax }}</td>
    <td>{{ $line->line_total }}</td>
</tr>

            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="table-totals">
        <tbody>
            {{-- Subtotal --}}
            <tr>
                <td>{{ $receipt_details->subtotal_label }}</td>
                <td class="text-right">{{ $receipt_details->subtotal }}</td>
            </tr>

            {{-- Tax summary --}}
            @if(!empty($receipt_details->group_tax_details))
                @foreach($receipt_details->group_tax_details as $label => $amount)
                    <tr>
                        <td>{!! $label !!}</td>
                        <td class="text-right">{{ $amount }}</td>
                    </tr>
                @endforeach
            @elseif(!empty($receipt_details->taxes))
                @foreach($receipt_details->taxes as $label => $amount)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-right">{{ $amount }}</td>
                    </tr>
                @endforeach
            @elseif(!empty($receipt_details->tax))
                <tr>
                    <td>{!! $receipt_details->tax_label !!}</td>
                    <td class="text-right">{{ $receipt_details->tax }}</td>
                </tr>
            @endif

            {{-- Discount --}}
            @if(!empty($receipt_details->discount))
                <tr>
                    <td>{!! $receipt_details->discount_label !!}</td>
                    <td class="text-right">(-) {{ $receipt_details->discount }}</td>
                </tr>
            @endif

            {{-- Total --}}
            <tr>
                <th>{!! $receipt_details->total_label !!}</th>
                <th class="text-right">{{ $receipt_details->total }}</th>
            </tr>
        </tbody>
    </table>

    {{-- Payment Summary --}}
    @if(!empty($receipt_details->total_due))
        <p><strong>{!! $receipt_details->total_due_label !!}:</strong> {{ $receipt_details->total_due }}</p>
    @endif

    @if(!empty($receipt_details->total_paid))
        <p><strong>{!! $receipt_details->total_paid_label !!}:</strong> {{ $receipt_details->total_paid }}</p>
    @endif

    @if(!empty($receipt_details->date_label))
        <p><strong>{{ $receipt_details->date_label }}:</strong> {{ $receipt_details->invoice_date }}</p>
    @endif

    {{-- Notes --}}
    @if(!empty($receipt_details->additional_notes))
        <p>{{ $receipt_details->additional_notes }}</p>
    @endif

    {{-- Barcode --}}
    <p>,ح.,حسيبىتنيب</p>
    @if($receipt_details->show_barcode)
        <img class="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,[39, 48, 54], true) }}" alt="barcode">
    @endif

    {{-- Footer --}}
    @if(!empty($receipt_details->footer_text))
        <div>{!! $receipt_details->footer_text !!}</div>
    @endif

    {{-- Signature --}}
    <p><strong>Authorized Signatory</strong></p>
</div>
