<?php

namespace Modules\Accounting\Listeners;

use App\BusinessLocation;
use App\Transaction;

class MapPaymentTransaction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
   public function handle($event)
{
    // Support different event payload shapes
    $payment = $event->transactionPayment ?? $event->payment ?? null;

    if (!$payment || empty($payment->transaction_id)) {
        \Log::notice('MapPaymentTransaction: missing payment or transaction_id; skipping', [
            'event' => class_basename($event),
        ]);
        return;
    }

    // Try to load the parent transaction (it may already be deleted)
    $transaction = \App\Transaction::find($payment->transaction_id);

    // If we're handling a delete AND parent is gone, just remove the mapping and exit
    if (!$transaction) {
        if (!empty($event->isDeleted)) {
            (new \Modules\Accounting\Utils\AccountingUtil)->deleteMap(null, $payment->id);
        } else {
            \Log::notice('MapPaymentTransaction: parent transaction not found', [
                'payment_id'     => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'event'          => class_basename($event),
            ]);
        }
        return;
    }

    // Decide mapping type safely
    $type = match ($transaction->type) {
        'purchase' => 'purchase_payment',
        'sell'     => 'sell_payment',
        default    => null,
    };
    if (!$type) {
        return;
    }

    // Fetch location defaults defensively
    $business_location = \App\BusinessLocation::find($transaction->location_id);
    $map = [];
    if ($business_location && !empty($business_location->accounting_default_map)) {
        $map = json_decode($business_location->accounting_default_map, true) ?: [];
    }

    $deposit_to      = $map[$type]['deposit_to']      ?? null;
    $payment_account = $map[$type]['payment_account'] ?? null;

    $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil;

    // If payment is being deleted, delete the mapping and exit
    if (!empty($event->isDeleted)) {
        $accountingUtil->deleteMap(null, $payment->id);
        return;
    }

    // Create/update mapping only if both accounts are configured
    if ($deposit_to && $payment_account) {
        // Avoid depending solely on session in queued listeners
        $user_id     = optional(request()->session())->get('user.id') ?? auth()->id() ?? ($payment->created_by ?? null);
        $business_id = $transaction->business_id;

        $accountingUtil->saveMap($type, $payment->id, $user_id, $business_id, $deposit_to, $payment_account);
    } else {
        \Log::notice('MapPaymentTransaction: missing default map for type', [
            'type'            => $type,
            'location_id'     => $transaction->location_id,
            'deposit_to'      => $deposit_to,
            'payment_account' => $payment_account,
        ]);
    }
}

}
