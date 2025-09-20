<style>
    body {
        font-family: 'Arial', sans-serif;
        direction: rtl; /* Arabic alignment */
        text-align: right;
        -webkit-print-color-adjust: exact !important; /* Chrome, Safari */
        print-color-adjust: exact !important; /* Modern browsers */
    }

    .receipt-container {
        padding: 20px;
        width: 100%;
        background: #ffffff;
        position: fixed;
        top: 10px;
        left:0;
    }

    .header-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        color: white;
        border-radius: 10px;
    }

    .amount-box {
        background: red;
        color: white;
        padding: 10px;
        font-size: 18px;
        border-radius: 5px;
        text-align: center;
    }

    .info-boxx {
        margin-top: 10px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        font-size:1rem;
        margin: 3rem 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 5px;
        font-size: 16px;
        font-weight: bold;
        margin-bottom: -30px;
    }

    .divider {
        border-top: 1px solid #aaa;
        margin: 5px 0;
    }

    .signature-section {
        display: flex;
        font-size: 12px;
        font-weight: bold;
        margin-top: 20px;
        flex-direction: column;
        border: 1px solid #ddd;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-top:1rem;
        row-gap:0.25rem;
    }

    .title{
        font-size: 1.5rem;
        font-weight: bold;
        display: flex;
        justify-content: center;
        column-gap: 0.5rem;
        background-color: oklch(97.7% 0.013 236.62) !important;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin: 1rem 0;
    }

    .signature-box {
        text-align: center;
        width: 30%;
    }

    .signature-box .line {
        display: block;
        border-top: 1.5px dashed black;
        width: 80%;
        margin: 30px 10% 0 10%;
    }
</style>

@php
    function number_to_words($number) {
        $formatter = new NumberFormatter('ar', NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number));
    }

    // Fix Amount Calculation
    $all_amount = 0;
    foreach ($journal->childs() as $child) {
        if ($child->type == "debit") {
            $all_amount += $child->amount;
        }
    }
@endphp
<div class="header">

</div>

<div class="receipt-container">
    <!-- Header Section -->
    <div class="header-container" style="display: flex; justify-content: center; align-items: center;">
        <?php $logo = asset('uploads/invoice_logos/' . $invoice_layout->logo); ?>
        @if(!empty($logo) && $invoice_layout->show_logo)
            <img src="{{$logo}}" class="logo">
        @endif
    </div>

    <!-- Receipt title -->
    <div class="title"> سند قبض <span>رقم: {{ $journal->ref_no }}</span></div>

    <!-- Receipt info -->
    <div class="info-boxx">
        <div style="display:flex; justify-content: space-between;">
            <p><strong>أستلمت أنا من الأخ / الإخوة:</strong> {{ $debit->account()->first()->name }}</p>
            <p><strong>التاريخ:</strong> {{ @format_datetime($journal->operation_date) }}</p>
        </div>
        <p><strong>مبلغ وقدره:</strong> @format_currency($all_amount) ({{ number_to_words($all_amount) }})</p>
        <p><strong>نقداً / شيك رقم:</strong> {{ $credit->account()->first()->name }}</p>       
        <p><strong>على بنك:</strong> 
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
        <p><strong>وذلك مقابل:</strong> {{ $journal->note }}</p>
        <p><strong>يقيد على حساب:</strong> {{ $credit->account()->first()->name }}</p>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div style="display:flex;justify-content: space-between;">
            <div class="signature-box">
                <p>المستلم</p>
                <span class="line"></span>
            </div>
            <div class="signature-box">
                <p>التوقيع</p>
                <span class="line"></span>
            </div>
            <div class="signature-box">
                <p>بتاريخ</p>
                <p style="margin-top:15px">/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/</p>
            </div>
        </div>
        <div style="display:flex;justify-content: space-between;margin-top:2rem;">
            <div class="signature-box">
                <p>أمين الصندوق</p>
                <span class="line"></span>
            </div>
            <div class="signature-box">
                <p>المحاسب</p>
                <span class="line"></span>
            </div>
            <div class="signature-box">
                <p>المدير المالي</p>
                <span class="line"></span>
            </div>
            <div class="signature-box">
                <p>المدير العام</p>
                <span class="line"></span>
            </div>
        </div>  
    </div>

    <br><br>
    <p style="font-size:0.75rem">{{now()}}</p>
    <div class="divider"></div>
    <div style="display:flex;flex-direction:column;align-items:center;">
        <p style="font-weight:bold">الوثيقة صادرة من النظام الالي ولا تحتاج إلى توقيع أو ختم</p>
        <p style="margin-top:0.5rem">bar code section **to be edit**</p>
    </div>
</div>
