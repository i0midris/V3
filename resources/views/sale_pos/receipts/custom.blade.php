<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        .page {
            position: relative !important;
            width: 210mm;
            height: 297mm;
            /* background: url('{{ asset("img/template.jpg") }}') no-repeat center !important;
            background-size: 100% 100% !important;  */
        }
        .field {
            position: absolute !important;
            font-size: 15px !important;
            font-family: Arial, sans-serif !important;
            font-weight: bolder !important;
            color: gray !important;
        }
        
    </style>
</head>
<body>
    <div class="page">
         <!-- Header fields -->
        <div class="field" style="top: 132px; left: 100px;">{{$receipt_details->invoice_no}}</div> <!-- Invoice No -->
        <div class="field" style="top: 127px; left: 334px;">S.P-009</div>      <!-- near S.P -->
        <div class="field" style="top: 129px; left: 670px;font-size: smaller !important;">{{$receipt_details->invoice_date}}</div>  <!-- Date -->
        
        <!-- Customer Name -->
        <div class="field" style="top: 160px; left: 115px;">
            @if(!empty($receipt_details->customer_name))
                {{ $receipt_details->customer_name }} 
            @elseif(!empty($receipt_details->customer_info))
                {!! $receipt_details->customer_info !!}
            @endif    
        </div>
        <div class="field" style="top: 160px; left: 570px;">763254652632</div>     <!-- Customer contact number -->
        <!-- Product rows -->

        @php
            // The starting position from the top of the page for the entire block of items.
            $topStart = 295;
        @endphp
    
        <div class="lines-container" style="position: absolute; top: {{ $topStart }}px; left: 30px;">
            @foreach($receipt_details->lines as $line)
                <div class="line-item-row" style="margin-bottom: 30px !important;">

                    <div class="field" style="left: 5px;">
                        {{ $loop->iteration }}
                    </div>

                    <div class="field" style="left: 40px;">
                        {{$line['name']}} {{$line['product_variation']}} {{$line['variation']}}
                        @if(!empty($line['brand'])), {{$line['brand']}} @endif
                        @if(!empty($line['product_custom_fields'])) {{$line['product_custom_fields']}} @endif

                        @if(!empty($line['product_description'])){!!$line['product_description']!!}@endif
                    </div>

                    <div class="field" style="left: 300px;">
                        {{ $line['units'] }}
                    </div>

                    <div class="field" style="left: 435px;">
                        {{ $line['quantity'] }}
                    </div>

                    <div class="field" style="left: 545px;">
                        {{ $line['unit_price_before_discount'] }}
                    </div>

                    <div class="field" style="left: 675px;">
                        {{ $line['line_total'] }}
                    </div>

                </div>
            @endforeach
        </div>

        <!-- barcode section -->
         @if($receipt_details->show_barcode || $receipt_details->show_qr_code)
				<div style="position:absolute;top:850px;left:160px">
					@if($receipt_details->show_barcode)
						{{-- Barcode --}}
						<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
					@endif
					
					@if($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
						<img class="center-block mt-5" src="data:image/png;base64,{{DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54])}}">
					@endif
				</div>
			@endif

        <!-- VAT -->
        <!-- TOTAL TAX (single line) + TOTALS -->
@php
    // Prefer a preformatted total tax if your app provides it
    $totalTaxDisplay = $receipt_details->total_tax ?? null;

    // If not provided, sum group_tax_details OR taxes (fallback to single tax)
    if ($totalTaxDisplay === null) {
        $taxSum = 0.0;
        $hasAny = false;

        // Helper to coerce formatted strings like "SAR 12.34" to number
        $num = function($v) {
            if (is_numeric($v)) return (float)$v;
            // keep digits, dot, and comma; then normalize comma to dot if needed
            $s = preg_replace('/[^0-9.,-]/', '', (string)$v);
            // if both separators exist, assume comma thousands -> remove commas
            if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
                $s = str_replace(',', '', $s);
            } else {
                // if only comma exists, treat as decimal
                if (strpos($s, ',') !== false && strpos($s, '.') === false) {
                    $s = str_replace(',', '.', $s);
                }
            }
            return is_numeric($s) ? (float)$s : 0.0;
        };

        if (!empty($receipt_details->group_tax_details)) {
            foreach ($receipt_details->group_tax_details as $k => $v) {
                $taxSum += $num($v);
                $hasAny = true;
            }
        } elseif (!empty($receipt_details->taxes)) {
            foreach ($receipt_details->taxes as $k => $v) {
                $taxSum += $num($v);
                $hasAny = true;
            }
        } elseif (!empty($receipt_details->tax)) {
            $taxSum += $num($receipt_details->tax);
            $hasAny = true;
        }

        // If you have a currency formatter upstream, you can keep $taxSum raw.
        // Otherwise, show the same formatting the inputs had (simple fallback here):
        if ($hasAny) {
            $totalTaxDisplay = (string) ($taxSum);
        }
    }

    // Layout: place "Total Tax" just above the totals row
    $taxLabelLeft  = 250; // px
    $taxValueLeft  = 630; // px (align with your right column)
    $taxY          = 955; // px (a bit above totals at 975 to avoid overlap)
@endphp

{{-- Single "Total Tax" line --}}
@if(!empty($totalTaxDisplay))
    <div class="field" style="top: {{ $taxY }}px; left: {{ $taxLabelLeft }}px;">
        {!! $receipt_details->tax_label ?? 'Total Tax' !!}
    </div>
    <div class="field" style="top: {{ $taxY }}px; left: {{ $taxValueLeft }}px;">
        {{ $totalTaxDisplay }}
    </div>
@endif

{{-- Totals (render once) --}}
@if(!empty($receipt_details->total_paid))
    <!-- Left total (your existing left slot) -->
    <div class="field" style="top: 975px; left: 70px;">
        {{ $receipt_details->total_paid }}
    </div>
@endif

<!-- Grand total on the right — use the app-provided formatted total -->
<div class="field" style="top: 975px; left: 630px;">
    {{ $receipt_details->total ?? ((!empty($receipt_details->total_paid) && !empty($totalTaxDisplay) && is_numeric($receipt_details->total_paid) && is_numeric($totalTaxDisplay)) ? ($receipt_details->total_paid + $totalTaxDisplay) : ($receipt_details->total_paid ?? '')) }}
</div>


    </div>
</body>

</html>