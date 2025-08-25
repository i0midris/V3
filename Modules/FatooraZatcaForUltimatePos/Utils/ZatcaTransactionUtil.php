<?php

namespace Modules\FatooraZatcaForUltimatePos\Utils;

use App\Utils\TransactionUtil;
use Bl\FatooraZatca\Invoices\Invoiceable;
use Modules\FatooraZatcaForUltimatePos\Services\ZatcaService;

class ZatcaTransactionUtil extends TransactionUtil
{
    /**
     * This QR code is used in saudi arabia, TLV format
     * https://github.com/SallaApp/ZATCA/blob/master/src/Tag.php
     * Need to validate the qr code from mobile app
     *
     * @return string
     */
    protected function _zatca_qr_text($transaction, $seller, $tax_number, $invoice_date, $invoice_total_amount, $invoice_tax_amount)
    {
        // Get zatca phase 2 qr when verified.
        if ($zatca = json_decode($transaction->zatca)) {
            $invoiceable = new Invoiceable;
            $invoiceable->setResult((array) $zatca);

            return $invoiceable->getQr();
        }

        // else get qr of zatca phase 1.
        return parent::_zatca_qr_text(...func_get_args());
    }

    /**
     * common function to get
     * list sell
     *
     * @param  int  $business_id
     * @return object
     */
    public function getListSells($business_id, $sale_type = 'sell')
    {
        return parent::getListSells(...func_get_args())
            ->addSelect(['transactions.zatca', 'transactions.uuid']);
    }

    public function addSellReturn($input, $business_id, $user_id, $uf_number = true)
    {
        $transaction = parent::addSellReturn(...func_get_args());

        if (config('fatoorazatcaforultimatepos.auto_reporting')) {
            ZatcaService::report($transaction);
        }

        return $transaction;
    }
}
