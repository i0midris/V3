<style>
    body {
        font-family: 'Arial', sans-serif;
        direction: rtl; /* Arabic alignment */
        text-align: right;
    }

    .receipt-container {
        max-width: 900px;
        margin: auto;
        padding: 20px;
        background: #ffffff;
        position: relative;
    }

    .header-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        color: white;
        border-radius: 10px;
    }

    .header-container img {
        max-height: 80px;
    }

    .amount-box {
        background: red;
        color: white;
        padding: 10px;
        font-size: 18px;
        border-radius: 5px;
        text-align: center;
    }

    .info-box {
        margin-top: 10px;
        display: flex;
        justify-content: center;
        flex-direction: column;
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
        font-size: 16px;
        font-weight: bold;
        margin-top: 20px;
        flex-direction: column;
        border: 1px solid #ccc;
        padding: 0.75rem;
        border-radius: 0.5rem;
    }

    .title{
        font-size: 1.5rem;
        font-weight: bold;
        display: flex;
        justify-content: center;
        column-gap: 0.5rem;
        background-color: oklch(97.7% 0.013 236.62);
        padding: 0.75rem;
        border-radius: 0.5rem;
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

<div class="receipt-container">
    <!-- Header Section -->
    <div class="header-container">
        <img src="{{asset('img/new-logo.jpg')}}" alt="Logo">
    </div>

    <!-- Receipt title -->
    <div class="title"> سند قبض <span>رقم: 45892</span></div>

    <!-- Receipt info -->
    <div class="info-box">
        <div style="display:flex; justify-content: space-between;">
            <p><strong>أستلمت أنا من الأخ / الإخوة:</strong> شركة النور للتجارة</p>
            <p><strong>التاريخ:</strong> 02-09-2025</p>
        </div>
        <p><strong>مبلغ وقدره:</strong> 150,000 ﷼ (مائة وخمسون ألف) ريال فقط</p>
        <div style="display:flex; justify-content: space-between;">
            <p><strong>وذلك مقابل:</strong> سداد فاتورة مبيعات</p>
            <p><strong>نقداً / شيك رقم:</strong> 745821</p>
        </div>
        <div style="display:flex; justify-content: space-between;">
            <p><strong>على بنك:</strong> بنك التضامن</p>
            <p><strong>يقيد على حساب:</strong> العملاء - الذمم المدينة</p>
        </div>
    </div>

    <!-- <div class="divider"></div> -->

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
                <span>/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/</span>
            </div>
        </div>
        <div style="display:flex;justify-content: space-between;">
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
    <p style="font-size:0.8rem">{{now()}}</p>
    <div class="divider"></div>
    <div style="display:flex;flex-direction:column;align-items:center;">
        <p style="font-weight:bold">الوثيقة صادرة من النظام الالي ولا تحتاج إلى توقيع أو ختم</p>
        <p>bar code section **to be edit**</p>
    </div>
</div>
