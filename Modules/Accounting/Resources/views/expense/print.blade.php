<style>
    body {
        font-family: 'Arial', sans-serif;
        direction: rtl; /* Arabic alignment */
        text-align: right;
        background-color: #f5f5f5;
    }

    .receipt-container {
        max-width: 900px;
        margin: auto;
        padding: 20px;
        background: #ffffff;
        border-radius: 10px;
        border: 3px solid #dd0000;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .header-container {
        height: 200px;
        text-align: center;
        padding: 10px;
        background: #000;
        color: white;
        border-radius: 10px;
    }

    .header-container img {
        height: 200px;
        width:  100% !important;
        display: block;
        margin: 0 auto;
    }

    .header-container .title {
        font-size: 22px;
        font-weight: bold;
        margin-top: 10px;
    }

    .info-box {
        border: 2px solid #000;
        padding: 10px;
        margin-top: 10px;
        border-radius: 10px;
        background: #f9f9f9;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 10px;
        font-size: 16px;
        font-weight: bold;
    }

    .divider {
        border-top: 2px dashed #000;
        margin: 15px 0;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        font-size: 16px;
        font-weight: bold;
        margin-top: 20px;
    }

    .signature-box {
        text-align: center;
        width: 30%;
    }

    .signature-box .line {
        display: block;
        margin-top: 30px;
        border-top: 2px dashed black;
        width: 100%;
    }
</style>

@php
    function number_to_words($number) {
        $formatter = new NumberFormatter('ar', NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number));
    }

    // Calculate Total Amount
    $all_amount = 0;
    foreach ($journal->childs() as $child) {
        if ($child->type == "debit") {
            $all_amount += $child->amount;
        }
    }
@endphp

<div class="receipt-container">
    <!-- Header with Logo -->
    <div class="header-container">
        <?php $logo = asset('uploads/invoice_logos/' . $invoice_layout->logo); ?>
        @if(!empty($logo) && $invoice_layout->show_logo)
            <img src="{{ $logo }}" class="logo">
        @endif
    </div>



    <div class="divider"></div>

    <!-- Expense Info -->
    <div class="info-box">
        <div class="info-row">
            <span>رقم السند: {{ $journal->ref_no }}</span>
            <div class="title">سند صرف</div>
            <span>التاريخ: {{ @format_datetime($journal->operation_date) }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Payment Details -->
    <div class="info-box">
        <p><strong>أعطيت إلى:</strong> {{ $debit->account()->first()->name }}</p>
        <p><strong>المبلغ وقدره:</strong> @format_currency($all_amount)</p>
        <p><strong>المبلغ كتابةً:</strong> {{ number_to_words($all_amount) }}</p>
        <p><strong>طريقة الدفع:</strong> 
            <?php $parent_credit = $credit->account()->first()->parent()->first(); ?>
            @if(isset($parent_credit->id))
                @if($parent_credit->gl_code == 1101)
                    @lang('lang_v1.cash')
                @elseif($parent_credit->gl_code == 1102)
                    @lang('accounting::lang.from_bank')
                @else
                    {{ $credit->account()->first()->name }}
                @endif
            @else
                {{ $credit->account()->first()->name }}
            @endif
        </p>
        <p><strong>الملاحظات:</strong> {{ $journal->note }}</p>
    </div>

    <div class="divider"></div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <p>اسم المستلم</p>
            <span class="line"></span>
        </div>
        <div class="signature-box">
            <p>الحسابات</p>
            <span class="line"></span>
        </div>
        <div class="signature-box">
            <p>المدير</p>
            <span class="line"></span>
        </div>
    </div>
</div>
