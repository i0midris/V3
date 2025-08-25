<?php

namespace Modules\Accounting\Utils;

use App\Business;
use App\Transaction;
use App\TransactionPayment;
use App\Utils\Util;
use App\Variation;
use DB;
use Modules\Accounting\Entities\AccountingAccountsTransaction;

class AccountingUtil extends Util
{
    public function balanceFormula($accounting_accounts_alias = 'accounting_accounts',
        $accounting_account_transaction_alias = 'AAT')
    {
        return "SUM( IF(
            ($accounting_accounts_alias.account_primary_type='asset' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='expense' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='income' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='equity' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='liability' AND $accounting_account_transaction_alias.type='credit'), 
            amount, -1*amount)) as balance";
    }

    public function getAccountingSettings($business_id)
    {
        $accounting_settings = Business::where('id', $business_id)
            ->value('accounting_settings');

        $accounting_settings = ! empty($accounting_settings) ? json_decode($accounting_settings, true) : [];

        return $accounting_settings;
    }

    public function getAgeingReport($business_id, $type, $group_by, $location_id = null)
    {
        $today = \Carbon::now()->format('Y-m-d');
        $query = Transaction::where('transactions.business_id', $business_id);

        if ($type == 'sell') {
            $query->where('transactions.type', 'sell')
                ->where('transactions.status', 'final');
        } elseif ($type == 'purchase') {
            $query->where('transactions.type', 'purchase')
                ->where('transactions.status', 'received');
        }

        if (! empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $dues = $query->whereNotNull('transactions.pay_term_number')
            ->whereIn('transactions.payment_status', ['partial', 'due'])
            ->join('contacts as c', 'c.id', '=', 'transactions.contact_id')
            ->select(
                DB::raw(
                    'DATEDIFF(
                            "'.$today.'", 
                            IF(
                                transactions.pay_term_type="days",
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                            )
                        ) as diff'
                ),
                DB::raw('SUM(transactions.final_total - 
                        (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) 
                        FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) )  
                        as total_due'),

                'c.name as contact_name',
                'transactions.contact_id',
                'transactions.invoice_no',
                'transactions.ref_no',
                'transactions.transaction_date',
                DB::raw('IF(
                        transactions.pay_term_type="days",
                        DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                        DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                    ) as due_date')
            )
            ->groupBy('transactions.id')
            ->get();

        $report_details = [];
        if ($group_by == 'contact') {
            foreach ($dues as $due) {
                if (! isset($report_details[$due->contact_id])) {
                    $report_details[$due->contact_id] = [
                        'name' => $due->contact_name,
                        '<1' => 0,
                        '1_30' => 0,
                        '31_60' => 0,
                        '61_90' => 0,
                        '>90' => 0,
                        'total_due' => 0,
                    ];
                }

                if ($due->diff < 1) {
                    $report_details[$due->contact_id]['<1'] += $due->total_due;
                } elseif ($due->diff >= 1 && $due->diff <= 30) {
                    $report_details[$due->contact_id]['1_30'] += $due->total_due;
                } elseif ($due->diff >= 31 && $due->diff <= 60) {
                    $report_details[$due->contact_id]['31_60'] += $due->total_due;
                } elseif ($due->diff >= 61 && $due->diff <= 90) {
                    $report_details[$due->contact_id]['61_90'] += $due->total_due;
                } elseif ($due->diff > 90) {
                    $report_details[$due->contact_id]['>90'] += $due->total_due;
                }

                $report_details[$due->contact_id]['total_due'] += $due->total_due;
            }
        } elseif ($group_by == 'due_date') {
            $report_details = [
                'current' => [],
                '1_30' => [],
                '31_60' => [],
                '61_90' => [],
                '>90' => [],
            ];
            foreach ($dues as $due) {
                $temp_array = [
                    'transaction_date' => $this->format_date($due->transaction_date),
                    'due_date' => $this->format_date($due->due_date),
                    'ref_no' => $due->ref_no,
                    'invoice_no' => $due->invoice_no,
                    'contact_name' => $due->contact_name,
                    'due' => $due->total_due,
                ];
                if ($due->diff < 1) {
                    $report_details['current'][] = $temp_array;
                } elseif ($due->diff >= 1 && $due->diff <= 30) {
                    $report_details['1_30'][] = $temp_array;
                } elseif ($due->diff >= 31 && $due->diff <= 60) {
                    $report_details['31_60'][] = $temp_array;
                } elseif ($due->diff >= 61 && $due->diff <= 90) {
                    $report_details['61_90'][] = $temp_array;
                } elseif ($due->diff > 90) {
                    $report_details['>90'][] = $temp_array;
                }
            }
        }

        return $report_details;
    }

    /**
     * Function to delete a mapping
     */
    public function deleteMap($transaction_id, $transaction_payment_id)
    {
        AccountingAccountsTransaction::where('transaction_id', $transaction_id)
            ->whereIn('map_type', ['payment_account', 'deposit_to'])
            ->where('transaction_payment_id', $transaction_payment_id)
            ->delete();
    }

    /**
     * Function to save a mapping
     */
    public function saveMap($type, $id, $user_id, $business_id, $deposit_to, $payment_account)
    {
        if ($type == 'sell') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();

            // $payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            // Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        } elseif (in_array($type, ['purchase_payment', 'sell_payment'])) {
            $transaction_payment = TransactionPayment::where('id', $id)->where('business_id', $business_id)
                ->firstorFail();

            // $payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => null,
                'transaction_payment_id' => $id,
                'amount' => $transaction_payment->amount,
                'type' => 'credit',
                'sub_type' => $type,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            // Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => null,
                'transaction_payment_id' => $id,
                'amount' => $transaction_payment->amount,
                'type' => 'debit',
                'sub_type' => $type,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        } elseif ($type == 'purchase') {
            $transaction = Transaction::where('business_id', $business_id)->where('id', $id)->firstorFail();

            // $payment_account will increase = sales = credit
            $payment_data = [
                'accounting_account_id' => $payment_account,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'credit',
                'sub_type' => $type,
                'map_type' => 'payment_account',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];

            // Deposit to will increase = debit
            $deposit_data = [
                'accounting_account_id' => $deposit_to,
                'transaction_id' => $id,
                'transaction_payment_id' => null,
                'amount' => $transaction->final_total,
                'type' => 'debit',
                'sub_type' => $type,
                'map_type' => 'deposit_to',
                'created_by' => $user_id,
                'operation_date' => \Carbon::now(),
            ];
        }

        AccountingAccountsTransaction::updateOrCreateMapTransaction($payment_data);
        AccountingAccountsTransaction::updateOrCreateMapTransaction($deposit_data);
    }

    public static function getAccountingAccountID($account_id, $business_id = null)
    {
        if ($business_id == null) {
            $business_id = request()->session()->get('user.business_id');
        }

        $data = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)->
        where('gl_code', $account_id)->
        first();
        if (isset($data->id)) {
            return $data->id;
        } else {
            return -1;
        }
    }

    public static function generateGlCodeForAccountingAccount($account_gl_code, $business_id)
    {

        for ($x = 1; $x <= 99; $x++) {
            $gl_code = $account_gl_code.sprintf('%02d', $x);
            $data = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)->
            where('gl_code', $gl_code)->
            first();
            if (! isset($data->id)) {
                return $gl_code;
            }
        }

        for ($x = 100; $x <= 999; $x++) {
            $gl_code = $account_gl_code.sprintf('%03d', $x);
            $data = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)->
            where('gl_code', $gl_code)->
            first();
            if (! isset($data->id)) {
                return $gl_code;
            }
        }

        for ($x = 1000; $x <= 9999; $x++) {
            $gl_code = $account_gl_code.sprintf('%04d', $x);
            $data = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)->
            where('gl_code', $gl_code)->
            first();
            if (! isset($data->id)) {
                return $gl_code;
            }
        }

        for ($x = 10000; $x <= 99999; $x++) {
            $gl_code = $account_gl_code.sprintf('%05d', $x);
            $data = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)->
            where('gl_code', $gl_code)->
            first();
            if (! isset($data->id)) {
                return $gl_code;
            }
        }

        throw new Error('Number of account reach 99999');
    }

    public static function checkTreeOfAccountsIsHere()
    {
        $business_id = request()->session()->get('user.business_id');

        $result = AccountingUtil::getAccountingAccountID(1103, $business_id);

        if ($result == -1) {
            return false;
        } else {
            return true;
        }
    }

    public static function getLinkedWithAccountingAccount($business_id, $link_table, $link_id)
    {
        $acc_linked = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
            ->where('link_table', $link_table)
            ->where('link_id', $link_id)
            ->first();

        if (isset($acc_linked->id)) {
            return $acc_linked;
        }

        return null;
    }

    public static function getLinkedWithAccountingAccTransMapping($business_id, $link_table, $link_id)
    {
        $acc_linked = \Modules\Accounting\Entities\AccountingAccTransMapping::where('business_id', $business_id)
            ->where('link_table', $link_table)
            ->where('link_id', $link_id)
            ->first();

        if (isset($acc_linked->id)) {
            return $acc_linked;
        }

        return null;
    }

    public static function createJournalEntry($type, $business_id, $location_id, $user_id, $operation_date, $link_table, $link_id, $recs, $note = '', $meta = [])
{
    try {
        DB::beginTransaction();

        // ✅ Utilities and settings
        $accounting_utils = new \Modules\Accounting\Utils\AccountingUtil;
        $util = new \App\Utils\Util;
        $accounting_settings = $accounting_utils->getAccountingSettings($business_id);

        // ✅ Determine reference number prefix
        $ref_types = [
            'journal_entry' => 'journal_entry_prefix',
            'expense' => 'expense_prefix',
            'receipt' => 'receipt_prefix',
            'supplier_refund' => 'refund_prefix',
            'customer_payment' => 'customer_payment_prefix',
        ];

        if (! isset($ref_types[$type])) {
            throw new \Exception("Invalid journal entry type: $type");
        }

        $ref_count = $util->setAndGetReferenceCount($type);
        $prefix = $accounting_settings[$ref_types[$type]] ?? '';
        $ref_no = $util->generateReferenceNumber($type, $ref_count, $business_id, $prefix);

        // ✅ Create the journal entry mapping
        $acc_trans_mapping = new \Modules\Accounting\Entities\AccountingAccTransMapping;
        $acc_trans_mapping->business_id = $business_id;
        $acc_trans_mapping->ref_no = $ref_no;
        $acc_trans_mapping->note = $note;
        $acc_trans_mapping->type = $type;
        $acc_trans_mapping->created_by = $user_id;
        $acc_trans_mapping->operation_date = $operation_date;
        $acc_trans_mapping->link_table = $link_table;
        $acc_trans_mapping->link_id = $link_id;
        $acc_trans_mapping->save();

        // ✅ Process each transaction record
        foreach ($recs as $key => $value) {
            $recs[$key]['acc_trans_mapping_id'] = $acc_trans_mapping->id;
            $recs[$key]['created_by'] = $user_id;
            $recs[$key]['operation_date'] = $operation_date ?? now();
            $recs[$key]['sub_type'] = $type;
            $recs[$key]['note'] = $value['note'] ?? '';
            $recs[$key]['location_id'] = $value['location_id'] ?? $location_id;

            // ✅ Currency data stored in each transaction
            if (!isset($recs[$key]['currency_code']) && isset($meta['currency_code'])) {
    $recs[$key]['currency_code'] = $meta['currency_code'];
}
if (!isset($recs[$key]['exchange_rate']) && isset($meta['exchange_rate'])) {
    $recs[$key]['exchange_rate'] = $meta['exchange_rate'];
}
if (!isset($recs[$key]['base_amount']) && isset($meta['base_amount'])) {
    $recs[$key]['base_amount'] = $meta['base_amount'];
}

        }

        // ✅ Insert journal entries
        \Modules\Accounting\Entities\AccountingAccountsTransaction::insert($recs);

        DB::commit();

        return [
            'success' => 1,
            'msg' => __('lang_v1.added_success'),
            'acc_trans_mapping' => $acc_trans_mapping,
        ];

    } catch (\Exception $e) {
        DB::rollBack();

        return [
            'success' => 0,
            'msg' => __('messages.something_went_wrong') . ': ' . $e->getMessage(),
        ];
    }
}
public static function deleteJournalEntry($link_table, $link_id)
{
    $ids = \Modules\Accounting\Entities\AccountingAccTransMapping::where('link_table', $link_table)
        ->where('link_id', $link_id)
        ->pluck('id');

    if ($ids->isNotEmpty()) {
        \Modules\Accounting\Entities\AccountingAccountsTransaction::whereIn('acc_trans_mapping_id', $ids)->delete();
        \Modules\Accounting\Entities\AccountingAccTransMapping::whereIn('id', $ids)->delete();
    }
}


    public static function create_update_opening_balance_journal_entry($to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note, $link_table = '', $link_id = '')
    {
        $mapping_transaction = AccountingUtil::check_opening_balance_journal_entry($to_account, $from_account, $business_id);

        if (! isset($mapping_transaction->id)) {
            AccountingUtil::create_opening_balance_journal_entry($to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note, $link_table, $link_id);
        } else {
            AccountingUtil::update_opening_balance_journal_entry($mapping_transaction->id, $to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note);
        }
    }

    public static function check_opening_balance_journal_entry($to_account, $from_account, $business_id)
    {
        $mapping_transaction = \Modules\Accounting\Entities\AccountingAccTransMapping::where('type', 'opening_balance')
            ->where('business_id', $business_id)->get();

        foreach ($mapping_transaction as $one) {
            foreach ($one->childs() as $child) {

                $acc = $child->account()->first();

                if (isset($acc->id) && $acc->gl_code != 3201) {
                    if ($child->sub_type == 'opening_balance' && ($child->accounting_account_id == $to_account || $child->accounting_account_id == $from_account)) {
                        return $one;
                    }
                }

            }
        }

        return null;
    }

    public static function create_opening_balance_journal_entry($to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note, $link_table = '', $link_id = '')
    {

        $accounting_utils = new \Modules\Accounting\Utils\AccountingUtil;

        $util = new \App\Utils\Util;

        $accounting_settings = $accounting_utils->getAccountingSettings($business_id);

        $ref_count = $util->setAndGetReferenceCount('accounting_opening_balance');
        $ref_no = '';
        if (empty($ref_no)) {
            $prefix = ! empty($accounting_settings['opening_balance_prefix']) ?
                $accounting_settings['opening_balance_prefix'] : '';

            // Generate reference number
            $ref_no = $util->generateReferenceNumber('accounting_opening_balance', $ref_count, $business_id, $prefix);
        }

        $acc_trans_mapping = new \Modules\Accounting\Entities\AccountingAccTransMapping;
        $acc_trans_mapping->business_id = $business_id;
        $acc_trans_mapping->ref_no = $ref_no;
        $acc_trans_mapping->note = $note;
        $acc_trans_mapping->type = 'opening_balance';
        $acc_trans_mapping->created_by = $user_id;
        $acc_trans_mapping->operation_date = $date;
        if ($link_table != '') {
            $acc_trans_mapping->link_table = $link_table;
        }
        if ($link_id != '') {
            $acc_trans_mapping->link_id = $link_id;
        }
        $acc_trans_mapping->save();

        $from_transaction_data = [
            'acc_trans_mapping_id' => $acc_trans_mapping->id,
            'amount' => $util->num_uf($amount),
            'type' => 'debit',
            'sub_type' => 'opening_balance',
            'accounting_account_id' => $from_account,
            'location_id' => $location_id ?? '',
            'created_by' => $user_id,
            'operation_date' => $date,
        ];

        $to_transaction_data = $from_transaction_data;
        $to_transaction_data['accounting_account_id'] = $to_account;
        $to_transaction_data['type'] = 'credit';

        \Modules\Accounting\Entities\AccountingAccountsTransaction::create($from_transaction_data);
        \Modules\Accounting\Entities\AccountingAccountsTransaction::create($to_transaction_data);

    }

    public static function update_opening_balance_journal_entry($id, $to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note)
    {
        $util = new \App\Utils\Util;

        $mapping_transaction = \Modules\Accounting\Entities\AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->firstOrFail();

        $debit_tansaction = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'debit')
            ->first();
        $credit_tansaction = \Modules\Accounting\Entities\AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'credit')
            ->first();

        $mapping_transaction->note = $note;
        $mapping_transaction->operation_date = $date;
        $mapping_transaction->created_by = $user_id;
        $mapping_transaction->save();

        $debit_tansaction->accounting_account_id = $from_account;
        $debit_tansaction->location_id = $location_id;
        $debit_tansaction->operation_date = $date;
        $debit_tansaction->amount = $util->num_uf($amount);
        $debit_tansaction->save();

        $credit_tansaction->accounting_account_id = $to_account;
        $credit_tansaction->location_id = $location_id;
        $credit_tansaction->operation_date = $date;
        $credit_tansaction->amount = $util->num_uf($amount);
        $credit_tansaction->save();
    }

    public static function r_s($number)
    {
        return str_replace(',', '', $number);
    }

    public static function addJournal($value, $business_id, $location_id, $user_id)
    {
        $purchase_line = $value['purchase_line'];

        $price = $purchase_line->purchase_price * abs($purchase_line->quantity);

        $transaction_date = $value['transaction_date'];

        $recs = [];

        // Retained Earnings Or Losses
        $_rec1['accounting_account_id'] = self::getAccountingAccountID(34, $business_id);
        $_rec1['amount'] = $price;
        $_rec1['type'] = 'debit';

        // stock
        $_rec2['accounting_account_id'] = self::getAccountingAccountID(1106, $business_id);
        $_rec2['amount'] = $price;
        $_rec2['type'] = 'credit';

        if ($purchase_line->quantity > 0) {
            $_rec1['type'] = 'credit';
            $_rec2['type'] = 'debit';
        } else {
            $_rec1['type'] = 'debit';
            $_rec2['type'] = 'credit';
        }

        $recs[] = $_rec1;
        $recs[] = $_rec2;

        if ($price != 0) {
            self::createJournalEntry(
                'journal_entry',
                $business_id,
                $location_id,
                $user_id,
                $transaction_date,
                'transactions',
                $purchase_line->transaction_id,
                $recs,
                $value['additional_notes']
            );
        }

    }

    public static function getAmountsAcounts($products)
    {
        $output = [];
        $output['customer'] = 0;
        $output['tax'] = 0;
        $output['discount'] = 0;
        $output['cost_of_goods'] = 0;
        $output['stock'] = 0;
        $output['revenue_of_products'] = 0;

        foreach ($products as $one) {
            $customer = 0;
            $tax = 0;
            $discount = 0;
            $cost_of_goods = 0;
            $stock = 0;
            $revenue_of_products = 0;

            $customer = self::r_s($one['unit_price_inc_tax']) * self::r_s($one['quantity']);

            $tax = self::r_s($one['item_tax']) * self::r_s($one['quantity']);

            $discount = self::r_s($one['line_discount_amount']);
            if ($one['line_discount_type'] == 'percentage') {
                $discount = self::r_s($one['unit_price']) * ($discount / 100);
            }
            $discount *= self::r_s($one['quantity']);

            $revenue_of_products = $customer - $tax + $discount;

            if ($one['enable_stock'] == 1) {
                $variation_product = Variation::find($one['variation_id']);
                $cost_of_goods = $variation_product->default_purchase_price * $one['quantity'];
            }

            $stock = $cost_of_goods;

            $output['customer'] += $customer;
            $output['tax'] += $tax;
            $output['discount'] += $discount;
            $output['cost_of_goods'] += $cost_of_goods;
            $output['stock'] += $stock;
            $output['revenue_of_products'] += $revenue_of_products;

        }

        return $output;
    }

    public static function getAmountsAcounts2($sell_lines)
{
    $output = [
        'customer' => 0,
        'tax' => 0,
        'discount' => 0,
        'cost_of_goods' => 0,
        'stock' => 0,
        'revenue_of_products' => 0,
    ];

    foreach ($sell_lines as $line) {
        // Use 'quantity' directly
        if (empty($line->quantity) || $line->quantity <= 0) {
            continue;
        }

        $qty = (float) $line->quantity;
        $unit_price_inc_tax = (float) $line->unit_price_inc_tax;
        $unit_price_ex_tax = (float) $line->unit_price;
        $item_tax_per_unit = (float) $line->item_tax;

        // 1. Customer total (gross)
        $customer_total = $unit_price_inc_tax * $qty;

        // 2. Tax total
        $total_tax = $item_tax_per_unit ;

        // 3. Discount
        $discount_per_unit = 0;
        if ($line->line_discount_type === 'percentage') {
            $discount_per_unit = $unit_price_ex_tax * ($line->line_discount_amount / 100);
        } else {
            $discount_per_unit = (float) $line->line_discount_amount;
        }
        $total_discount = $discount_per_unit * $qty;

        // 4. Revenue = (price before tax - discount) * qty
        $revenue = $unit_price_ex_tax * $qty;

        // 5. Cost of goods (if stock is enabled)
        $cogs = 0;
        if (!empty($line->product) && $line->product->enable_stock) {
            $purchase_price = optional($line->variations)->default_purchase_price ?? 0;
            $cogs = $purchase_price * $qty;
        }

        // 6. Stock = COGS
        $stock = $cogs;

        // Totals
        $output['customer'] += $customer_total;
        $output['tax'] += $total_tax;
        $output['discount'] += $total_discount;
        $output['revenue_of_products'] += $revenue;
        $output['cost_of_goods'] += $cogs;
        $output['stock'] += $stock;
    }

    // Round off values
    foreach ($output as $key => $value) {
        $output[$key] = round($value, 4);
    }

    return $output;
}


    public static function getAmountsAcounts3($products)
    {
        $output = [];
        $output['stock'] = 0;

        foreach ($products as $one) {
            $price = Variation::find($one['variation_id'])->default_purchase_price;

            $output['stock'] += $price * $one['quantity'];
        }

        return $output;
    }

public static function getAmountsAcounts4($purchases)
{
    $util = new \App\Utils\Util;

    $output = [
        'stock' => 0,
        'tax' => 0,
        'discount' => 0,
        'supplier' => 0,
    ];

    foreach ($purchases as $one) {
        \Log::info('COA Raw Purchase Line', $one);

        $pp_without_discount       = $util->raw_num_uf($one['pp_without_discount']);
        $purchase_price            = $util->raw_num_uf($one['purchase_price']);            // unit price EX tax (after discount)
        $purchase_price_inc_tax    = $util->raw_num_uf($one['purchase_price_inc_tax']);    // unit price INC tax (after discount)
        $qty                       = $util->raw_num_uf($one['quantity']);

        // Supplier (actual price with tax) — OK to keep
        $supplier = $purchase_price_inc_tax * $qty;

        $stock = $pp_without_discount * $qty; 

        // ✅ TAX AFTER DISCOUNT:
        // recompute per-unit tax from (inc - ex) on the discounted base
        $tax_per_unit = max($purchase_price_inc_tax - $purchase_price, 0);
        $tax = $tax_per_unit * $qty;

        // ✅ Discount (preserve your logic & precision)
        $discount = 0;
        $discount_percent = isset($one['discount_percent']) ? $one['discount_percent'] : 0;
        $discount_percent = (float) number_format((float) $discount_percent, 6, '.', '');

        if ($discount_percent > 0) {
            $discount_per_unit = $pp_without_discount * ($discount_percent / 100);
            $discount = $discount_per_unit * $qty;
        } else {
            $discount_per_unit = max($pp_without_discount - $purchase_price, 0);
            $discount = $discount_per_unit * $qty;
        }

        \Log::info('COA Discount Calc', [
            'pp_without_discount' => $pp_without_discount,
            'purchase_price' => $purchase_price,
            'discount_percent' => $discount_percent,
            'qty' => $qty,
            'discount' => $discount,
        ]);

        $output['stock']    += $stock;
        $output['tax']      += $tax;         // now tax-after-discount
        $output['discount'] += $discount;
        $output['supplier'] += $supplier;    // still excludes shipping; add that in JE builder
    }

    // Final rounding
    $output['discount'] = round($output['discount'], 2);
    $output['stock']    = round($output['stock'], 2);
    $output['tax']      = round($output['tax'], 2);
    $output['supplier'] = round($output['supplier'], 2);

    return $output;
}





    public static function getAmountsAcounts5($return_quantities, $purchase_lines)
    {
        $output = [];
        $output['stock'] = 0;
        $output['tax'] = 0;
        $output['discount'] = 0;
        $output['supplier'] = 0;

        foreach ($return_quantities as $id => $quantity) {
            if ($quantity > 0) {
                $one_stock = 0;
                $one_tax = 0;
                $one_discount = 0;
                $one_supplier = 0;

                foreach ($purchase_lines as $purchase_line) {
                    if ($purchase_line->id == $id) {
                        $one_supplier = $purchase_line->purchase_price_inc_tax;

                        $one_stock += $purchase_line->pp_without_discount;

                        if ($purchase_line->discount_percent > 0) {
                            $one_discount += $purchase_line->pp_without_discount - $purchase_line->purchase_price;
                        }

                        if ($purchase_line->item_tax > 0) {
                            $one_tax += $purchase_line->item_tax;
                        }

                        $one_stock *= $quantity;
                        $one_discount *= $quantity;
                        $one_tax *= $quantity;
                        $one_supplier *= $quantity;

                        $output['stock'] += $one_stock;
                        $output['discount'] += $one_discount;
                        $output['tax'] += $one_tax;
                        $output['supplier'] += $one_supplier;

                    }
                }
            }

        }

        return $output;
    }
    

    public static function getLcationID($business_id)
    {

        $user = \Illuminate\Support\Facades\Auth::user();

        $permitted_locations = $user->permitted_locations($business_id);

        $query = \App\BusinessLocation::where('business_id', $business_id);

        if ($permitted_locations != 'all') {
            $query->whereIn('id', $permitted_locations);
        }
        $business_location = $query->Active()->first();

        return $business_location->id;
    }
}
