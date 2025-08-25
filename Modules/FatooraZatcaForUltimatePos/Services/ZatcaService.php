<?php

namespace Modules\FatooraZatcaForUltimatePos\Services;

use App\Transaction;
use Bl\FatooraZatca\Classes\InvoiceType;
use Bl\FatooraZatca\Classes\PaymentType;
use Bl\FatooraZatca\Invoices\B2C;
use Bl\FatooraZatca\Objects\ChargeItem;
use Bl\FatooraZatca\Objects\DiscountItem;
use Bl\FatooraZatca\Objects\Invoice;
use Bl\FatooraZatca\Objects\InvoiceItem;
use Bl\FatooraZatca\Objects\Seller;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Modules\FatooraZatcaForUltimatePos\Entities\ZatcaBusiness;
use Modules\FatooraZatcaForUltimatePos\Exceptions\ZatcaException;

class ZatcaService
{
    private static $subtotal = 0.00;

    private static $tax = 0.00;

    private static $discountItems = [];

    private static $chargeItems = [];

    /**
     * report the invoice to zatca portal.
     *
     * @return bool
     */
    public static function report(Transaction $transaction)
    {
        // stop when transaction status not final...
        if ($transaction->status !== 'final') {
            $transaction->update(['uuid' => null]);

            return false;
        }

        $settings = ZatcaBusiness::query()->find($transaction->business_id);

        // stop when zatca not verified...
        if (! $settings->zatca_verified) {
            $transaction->update(['uuid' => null]);

            return false;
        }

        $transaction->load('payment_lines');

        config()->set('zatca.app.environment', $settings->getZatcaField('environment'));

        try {
            // dd(self::invoice($transaction, $settings));
            $b2c = B2C::make(self::seller($settings), self::invoice($transaction, $settings))->report();

            // update zatca attribute...
            $transaction->update([
                'zatca' => json_encode($b2c->getResult()),
            ]);

            // update previous hash attribute...
            $settings->setZatcaFields(['previous_hash' => $b2c->getInvoiceHash()]);
            $settings->save();

            return true;
        } catch (Exception $e) {
            throw new ZatcaException($e->getMessage());
        }
    }

    public static function seller(ZatcaBusiness $settings)
    {
        return new Seller(
            $settings->getZatcaField('registration_number'),
            $settings->getZatcaField('street_name'),
            $settings->getZatcaField('building_number'),
            $settings->getZatcaField('plot_identification'),
            $settings->getZatcaField('city_sub_division'),
            $settings->getZatcaField('city'),
            $settings->getZatcaField('postal_number'),
            $settings->getZatcaField('tax_number'),
            $settings->getZatcaField('organization_name'),
            $settings->zatca_settings['private_key'],
            $settings->zatca_settings['cert_production'],
            $settings->zatca_settings['secret_production'],
        );
    }

    public static function invoice(Transaction $transaction, ZatcaBusiness $settings)
    {
        [$paymentType, $paymentNote] = static::getPaymentProperties($transaction->payment_lines);

        [$invoiceType, $invoiceBillingId, $invoiceNote] = self::getDynamicInvoiceDetails($transaction);

        $invoiceItems = self::calculateInvoiceItems($transaction);

        if ($transaction->discount_amount > 0) {
            self::applyInvoiceDiscount($transaction);
        }

        if ($transaction->shipping_charges > 0) {
            self::applyShippingCharges($transaction->shipping_charges);
        }

        return new Invoice(
            $transaction->id,
            $transaction->invoice_no,
            $transaction->uuid,
            Carbon::parse($transaction->created_at)->format('Y-m-d'),
            Carbon::parse($transaction->created_at)->format('H:i:s'),
            $invoiceType,
            $paymentType,
            self::$subtotal,
            self::$discountItems,
            self::$tax,
            $transaction->final_total,
            $invoiceItems,
            $settings->getZatcaField('previous_hash'),
            $invoiceBillingId,
            $invoiceNote,
            $paymentNote,
            'SAR',
            15,
            Carbon::parse($transaction->transaction_date)->format('Y-m-d'),
            0,
            0,
            self::$chargeItems
        );
    }

    private static function getDynamicInvoiceDetails(Transaction $transaction): array
    {
        $invoiceType = null;
        $invoiceBillingId = null;
        $invoiceNote = null;

        // when refund invoice
        if ($transaction->type === 'sell_return') {
            $invoiceType = InvoiceType::CREDIT_NOTE;
            $invoiceBillingId = $transaction->return_parent_sell->invoice_no;
            $invoiceNote = $transaction->zatca_note;
        }
        // when debit note invoice
        elseif ($transaction->is_debit_note) {
            $invoiceType = InvoiceType::DEBIT_NOTE;
            $invoiceBillingId = $transaction->parent->invoice_no;
            $invoiceNote = $transaction->zatca_note;
        }
        // when tax invoice
        else {
            $invoiceType = InvoiceType::TAX_INVOICE;
        }

        return [$invoiceType, $invoiceBillingId, $invoiceNote];
    }

    public static function getPaymentProperties(Collection $paymentLines): array
    {
        $paymentType = null;
        $paymentNote = null;

        if ($paymentLines->count() === 0) {
            $paymentType = PaymentType::CREDIT;
        } elseif ($paymentLines->count() === 1) {
            switch ($paymentLines->first()->method) {
                case 'card':
                    $paymentType = PaymentType::BANK_CARD;
                    break;
                default:
                    $paymentType = PaymentType::CASH;
                    break;

            }
        } else {
            $paymentType = PaymentType::MULTIPLE;
            $paymentNote = $paymentLines->map(fn ($paymentLine) => $paymentLine->method)->implode(',');
        }

        return [
            $paymentType,
            $paymentNote,
        ];
    }

    public static function calculateInvoiceItems(Transaction $transaction): array
    {
        $invoiceItems = [];

        switch ($transaction->type) {
            case 'sell_return':
                $sellLines = $transaction->sell_return_lines;
                break;

            default:
                $sellLines = $transaction->sell_lines;
                break;
        }

        $sellLines->load('product', 'line_tax');

        foreach ($sellLines as $index => $sellLine) {
            $quantity = $transaction->type === 'sell_return'
            ? $sellLine->pivot->quantity
            : $sellLine->quantity;

            $subtotal = $sellLine->unit_price_before_discount * $quantity;
            $discount = $sellLine->get_discount_amount() * $quantity;
            $tax = $sellLine->item_tax * $quantity;
            $total = $sellLine->unit_price_inc_tax * $quantity;

            $invoiceItems[] = new InvoiceItem(
                $index + 1,
                $sellLine->relationLoaded('product') ? $sellLine->product->name : 'Not Found',
                $quantity,
                $subtotal,
                $discount,
                $tax,
                $sellLine->relationLoaded('line_tax') ? $sellLine->line_tax->amount : 0,
                $total,
                // $sellLine->XXXXXXXXXXXXXXXXXXXXX, // TODO get from product disocunt in POS (Description)...
            );

            // increment totals...
            self::$subtotal += $subtotal;
            self::$tax += $tax;
        }

        return $invoiceItems;
    }

    public static function applyInvoiceDiscount(Transaction $transaction)
    {
        $discountAmount = $transaction->discount_type === 'percentage'
        ? ($transaction->discount_amount / 100) * self::$subtotal
        : $transaction->discount_amount;

        self::$discountItems[] = new DiscountItem('الخصم على الفاتورة', $discountAmount);

        $subtotalAfterDiscount = self::$subtotal - $discountAmount;

        self::$tax = $subtotalAfterDiscount * (15 / 100);
    }

    public static function applyShippingCharges(float $shipping_charges)
    {
        $chargeSubtotal = $shipping_charges / 1.15;
        $chargeTax = $shipping_charges - $chargeSubtotal;

        self::$chargeItems[] = new ChargeItem(
            'SAA', // Shipping and handling
            'مصاريف الشحن',
            $chargeSubtotal
        );

        self::$tax += $chargeTax;
    }
}
