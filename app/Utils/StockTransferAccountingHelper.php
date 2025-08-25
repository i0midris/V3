<?php

namespace App\Utils;

use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;

class StockTransferAccountingHelper
{
    /**
     * Syncs accounting journal entries for a stock transfer.
     *
     * @param  \App\Transaction  $sell_transfer
     * @param  \App\Transaction  $purchase_transfer
     * @param  int  $user_id
     * @return void
     *
     * @throws \Exception
     */
    public function syncJournalEntry($sell_transfer, $purchase_transfer, $user_id)
    {
        $business_id = $purchase_transfer->business_id;
        $location_id = $purchase_transfer->location_id;
        $amount = $purchase_transfer->final_total;
        $operation_date = $purchase_transfer->transaction_date;

        // 🧹 Clean existing mapping
        $mapping = AccountingAccTransMapping::where('business_id', $business_id)
            ->where('link_table', 'transactions')
            ->where('link_id', $purchase_transfer->id)
            ->first();

        if ($mapping) {
            AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->delete();
            $mapping->delete();
        }

        // 🧾 Create new mapping
        $mapping = new AccountingAccTransMapping;
        $mapping->business_id = $business_id;
        $mapping->ref_no = $purchase_transfer->ref_no;
        $mapping->note = 'قيد تلقائي لنقل المخزون';
        $mapping->type = 'stock_transfer';
        $mapping->link_table = 'transactions';
        $mapping->link_id = $purchase_transfer->id;
        $mapping->created_by = $user_id;
        $mapping->operation_date = $operation_date;
        $mapping->save();

        // 🔄 Inventory accounts per location (GL codes like 1101, 1102, etc.)
        $gl_code = '1106'; // Unified stock account for all transfers

        $from_account = AccountingAccount::where('gl_code', $gl_code)
            ->where('business_id', $business_id)
            ->first();

        $to_account = $from_account; // Same account for both debit and credit

        if (! $from_account || ! $to_account) {
            throw new \Exception('Missing inventory GL accounts for the source or destination location.');
        }

        // 🧮 Insert accounting transactions
        AccountingAccountsTransaction::create([
            'amount' => $amount,
            'accounting_account_id' => $to_account->id,
            'transaction_id' => $purchase_transfer->id,
            'type' => 'debit',
            'sub_type' => 'stock_transfer',
            'map_type' => 'stock_transfer',
            'operation_date' => $operation_date,
            'created_by' => $user_id,
            'acc_trans_mapping_id' => $mapping->id,
            'note' => 'مستلم المخزون (مدين)',
            'location_id' => $to_account->location_id ?? $location_id,
        ]);

        AccountingAccountsTransaction::create([
            'amount' => $amount,
            'accounting_account_id' => $from_account->id,
            'transaction_id' => $purchase_transfer->id,
            'type' => 'credit',
            'sub_type' => 'stock_transfer',
            'map_type' => 'stock_transfer',
            'operation_date' => $operation_date,
            'created_by' => $user_id,
            'acc_trans_mapping_id' => $mapping->id,
            'note' => 'مرسل المخزون (دائن)',
            'location_id' => $from_account->location_id ?? $sell_transfer->location_id,
        ]);
    }
}
