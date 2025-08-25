<?php

namespace Modules\Accounting\Http\Controllers;

use App\Account;
use App\Contact;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Utils\AccountingUtil;
use db;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'accounting_module',
                'label' => __('accounting::lang.accounting_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds cms menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil;

        $is_accounting_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'accounting_module');

        $commonUtil = new Util;
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);
        $menu = Menu::instance('admin-sidebar-menu');
        if (auth()->user()->can('accounting.access_accounting_module') && $is_accounting_enabled) {
            $menu->dropdown(
                __('accounting::lang.accounting'),
                function ($sub): void {
                    //                    $sub->url(
                    //                        action([\Modules\Accounting\Http\Controllers\AccountingController::class, 'dashboard']),
                    //                        __('accounting::lang.accounting'),
                    //                        ['icon' => '', 'active' => request()->segment(1) == 'accounting']
                    //                    );
                    if (auth()->user()->can('accounting.manage_accounts')) {
                        $sub->url(
                            action([\Modules\Accounting\Http\Controllers\CoaController::class, 'index']),
                            __('accounting::lang.chart_of_accounts'),
                            ['icon' => '', 'active' => request()->segment(2) == 'chart-of-accounts']
                        );
                        $sub->url(
                            action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'],
                                \Modules\Accounting\Utils\AccountingUtil::getAccountingAccountID(4101)
                            ),
                            __('accounting::lang.ledger'),
                            ['icon' => '', 'active' => request()->segment(2) == 'ledger']
                        );
                    }
                    if (auth()->user()->can('accounting.view_journal')) {
                        $sub->url(
                            action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'index']),
                            __('accounting::lang.journal_entry'),
                            ['icon' => '', 'active' => request()->segment(2) == 'journal-entry']
                        );
                    }
                    if (auth()->user()->can('accounting.view_reports')) {
                        $sub->url(
                            action([\Modules\Accounting\Http\Controllers\ReportController::class, 'index']),
                            __('accounting::lang.reports'),
                            ['icon' => '',  'active' => request()->segment(2) == 'reports']
                        );
                    }
                },
                ['icon' => 'fas fa-money-check fa', 'id' => 'accounting_mkamel']
            )->order(50);
        }
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'accounting.access_accounting_module',
                'label' => __('accounting::lang.access_accounting_module'),
                'default' => false,
            ],
            [
                'value' => 'accounting.manage_accounts',
                'label' => __('accounting::lang.manage_accounts'),
                'default' => false,
            ],
            [
                'value' => 'accounting.view_journal',
                'label' => __('accounting::lang.view_journal'),
                'default' => false,
            ],
            [
                'value' => 'accounting.add_journal',
                'label' => __('accounting::lang.add_journal'),
                'default' => false,
            ],
            [
                'value' => 'accounting.edit_journal',
                'label' => __('accounting::lang.edit_journal'),
                'default' => false,
            ],
            [
                'value' => 'accounting.delete_journal',
                'label' => __('accounting::lang.delete_journal'),
                'default' => false,
            ],
            [
                'value' => 'accounting.map_transactions',
                'label' => __('accounting::lang.map_transactions'),
                'default' => false,
            ],
            [
                'value' => 'accounting.view_transfer',
                'label' => __('accounting::lang.view_transfer'),
                'default' => false,
            ],
            [
                'value' => 'accounting.add_transfer',
                'label' => __('accounting::lang.add_transfer'),
                'default' => false,
            ],
            [
                'value' => 'accounting.edit_transfer',
                'label' => __('accounting::lang.edit_transfer'),
                'default' => false,
            ],
            [
                'value' => 'accounting.delete_transfer',
                'label' => __('accounting::lang.delete_transfer'),
                'default' => false,
            ],
            [
                'value' => 'accounting.manage_budget',
                'label' => __('accounting::lang.manage_budget'),
                'default' => false,
            ],
            [
                'value' => 'accounting.view_reports',
                'label' => __('accounting::lang.view_reports'),
                'default' => false,
            ],
        ];
    }

    public function MKamel_checkTreeAccountingDefined()
    {
        return AccountingUtil::checkTreeOfAccountsIsHere();
    }

    public function MKamel_store999($data)
    {
        // Request $request,Account $account

        $request = $data['request'];
        $account = $data['account'];

        $business_id = $request->session()->get('user.business_id');

        $user_id = $request->session()->get('user.id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $acc_c = AccountingAccount::find($request->from_account);

            $account_acc = [
                0 => [
                    'name' => $request->name,
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => $acc_c->id,
                    'detail_type_id' => $acc_c->gl_code,
                    'gl_code' => AccountingUtil::generateGlCodeForAccountingAccount($acc_c->gl_code, $business_id),
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'link_table' => 'accounts',
                    'link_id' => $account->id,
                ],
            ];

            AccountingAccount::insert($account_acc);

        }
    }

   public function MKamel_store000($data)
    {

        $request = $data['request'];
        $input = $data['input'];
        $output = $data['output'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $contact_is_here = AccountingAccount::where('business_id', $business_id)
                ->where('link_table', 'contacts')
                ->where('link_id', $output['data']->id)
                ->first();

            $contact_name = trim($input['first_name'] . ' ' . $input['middle_name'] . ' ' . $input['last_name']);

            if (! isset($contact_is_here->id)) {

                if ($input['type'] == 'customer' || $input['type'] == 'both') {
                    $parent_id = AccountingUtil::getAccountingAccountID(1103, $business_id);
                    $gl_code = AccountingUtil::generateGlCodeForAccountingAccount(1103, $business_id);

                    $default_accounts = [[
                        'name' => $contact_name,
                        'business_id' => $business_id,
                        'account_primary_type' => 'asset',
                        'account_sub_type_id' => 11,
                        'parent_account_id' => $parent_id,
                        'detail_type_id' => 1103,
                        'gl_code' => $gl_code,
                        'status' => 'active',
                        'created_by' => $request->session()->get('user.id'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'link_table' => 'contacts',
                        'link_id' => $output['data']->id,
                    ]];

                    AccountingAccount::insert($default_accounts);

                    if (!empty($output['opening_balance_transaction'])) {
                        $from_account = DB::table('accounting_accounts')->orderBy('id', 'desc')->first()->id;
                        $to_account = AccountingUtil::getAccountingAccountID('3201', $business_id);

                        AccountingUtil::create_update_opening_balance_journal_entry(
                            $to_account,
                            $from_account,
                            $output['opening_balance_transaction']->final_total,
                            $business_id,
                            $output['opening_balance_transaction']->location_id,
                            $request->session()->get('user.id'),
                            Carbon::now(),
                            '',
                            'transactions',
                            $output['opening_balance_transaction']->id
                        );
                    }

                } elseif ($input['type'] == 'supplier') {
                    $parent_id = AccountingUtil::getAccountingAccountID(2101, $business_id);
                    $gl_code = AccountingUtil::generateGlCodeForAccountingAccount(2101, $business_id);

                    $default_accounts = [[
                        'name' => $contact_name,
                        'business_id' => $business_id,
                        'account_primary_type' => 'liability',
                        'account_sub_type_id' => 21,
                        'parent_account_id' => $parent_id,
                        'detail_type_id' => 2101,
                        'gl_code' => $gl_code,
                        'status' => 'active',
                        'created_by' => $request->session()->get('user.id'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'link_table' => 'contacts',
                        'link_id' => $output['data']->id,
                    ]];

                    AccountingAccount::insert($default_accounts);

                    if (!empty($output['opening_balance_transaction'])) {
                        $from_account = AccountingUtil::getAccountingAccountID('3201', $business_id);
                        $to_account = DB::table('accounting_accounts')->orderBy('id', 'desc')->first()->id;

                        AccountingUtil::create_update_opening_balance_journal_entry(
                            $to_account,
                            $from_account,
                            $output['opening_balance_transaction']->final_total,
                            $business_id,
                            $output['opening_balance_transaction']->location_id,
                            $request->session()->get('user.id'),
                            Carbon::now(),
                            '',
                            'transactions',
                            $output['opening_balance_transaction']->id
                        );
                    }
                }
            } else {

                $contact_is_here->name = $contact_name;
                $contact_is_here->save();

                if (!empty($output['opening_balance_transaction'])) {
                    $amount = $output['opening_balance_transaction']->final_total;

                    if ($input['type'] == 'customer' || $input['type'] == 'both') {
                        $to_account = AccountingUtil::getAccountingAccountID('3201', $business_id);


                        AccountingUtil::create_update_opening_balance_journal_entry(
                            $to_account,
                            $contact_is_here->id,
                            $amount,
                            $business_id,
                            $output['opening_balance_transaction']->location_id,
                            $request->session()->get('user.id'),
                            Carbon::now(),
                            '',
                            'transactions',
                            $output['opening_balance_transaction']->id
                        );
                    } elseif ($input['type'] == 'supplier') {
                        $from_account = AccountingUtil::getAccountingAccountID('3201', $business_id);

                        AccountingUtil::create_update_opening_balance_journal_entry(
                            $contact_is_here->id,
                            $from_account,
                            $amount,
                            $business_id,
                            $output['opening_balance_transaction']->location_id,
                            $request->session()->get('user.id'),
                            Carbon::now(),
                            '',
                            'transactions',
                            $output['opening_balance_transaction']->id
                        );
                    }
                }
            }
        } else {
            \Log::warning('MKamel_store000: Tree of accounts not initialized for business', ['business_id' => $business_id]);
        }

    }

    public function MKamel_check111($data)
    {
        $request = $data['request'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $expense_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'expense_categories', $request->expense_category_id);

            if ($expense_linked == null) {
                return ['success' => 0,
                    'msg' => __('accounting::lang.expense_not_linked'),
                ];
            }

            $acc_id = ! empty($request->input('payment')) && $request->input('payment')[0]['account_id'] != null ? $request->input('payment')[0]['account_id'] : -1;

            $account_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $acc_id);

            if ($account_linked == null) {
                return ['success' => 0,
                    'msg' => __('accounting::lang.account_not_linked'),
                ];
            }

            return ['success' => 1,
                'expense_linked' => $expense_linked,
                'account_linked' => $account_linked,
            ];
        }

        return ['success' => 2,
        ];
    }

    public function MKamel_store111($data)
    {
        $request = $data['request'];

        $expense_linked = $data['expense_linked'];

        $expense = $data['expense'];

        $account_linked = $data['account_linked'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $recs = [];

            // expense
            $_rec1['accounting_account_id'] = $expense_linked->id;
            $_rec1['amount'] = $expense->total_before_tax;
            $_rec1['type'] = 'debit';

            $recs[] = $_rec1;

            if ($expense->tax_amount != 0) {
                // tax
                $_rec2['accounting_account_id'] = AccountingUtil::getAccountingAccountID(2105, $business_id);
                $_rec2['amount'] = $expense->tax_amount;
                $_rec2['type'] = 'debit';

                $recs[] = $_rec2;
            }

            // cash or banck account
            $_rec3['accounting_account_id'] = $account_linked->id;
            $_rec3['amount'] = $expense->final_total;
            $_rec3['type'] = 'credit';

            $recs[] = $_rec3;

            $note = trim((string)($expense->additional_notes ?? $request->input('additional_notes') ?? ''));

            AccountingUtil::createJournalEntry(
                'expense',
                $business_id,
                $expense->location_id,
                $expense->created_by,
                $expense->transaction_date,
                'transactions',
                $expense->id,
                $recs,
                $note
            );
        }
    }

    public function MKamel_store100($data)
    {

        $request = $data['request'];

        $user = $data['user'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $user_is_here = AccountingAccount::where('business_id', $business_id)
                ->where('link_table', 'users')
                ->where('link_id', $user->id)
                ->first();

            if (! isset($user_is_here->id)) {
                $default_accounts = [
                    0 => [
                        'name' => $user->user_full_name,
                        'business_id' => $business_id,
                        'account_primary_type' => 'liability',
                        'account_sub_type_id' => 21,
                        'parent_account_id' => AccountingUtil::getAccountingAccountID(2103, $business_id),
                        'detail_type_id' => 2103,
                        'gl_code' => AccountingUtil::generateGlCodeForAccountingAccount(2103, $business_id),
                        'status' => 'active',
                        'created_by' => request()->session()->get('user.id'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'link_table' => 'users',
                        'link_id' => $user->id,
                    ],
                ];

                AccountingAccount::insert($default_accounts);

            } else {

                $user_is_here->name = $user->user_full_name;

                $user_is_here->save();

            }
        }

    }

    public function MKamel_store110($data)
    {
        $request = $data['request'];

        $new_transaction_data = $data['new_transaction_data'];

        $edit_transaction_data = $data['edit_transaction_data'];

        $delete_transaction_data = $data['delete_transaction_data'];

        $location_id = $data['location_id'];

        $user_id = $data['user_id'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {

            // for new_transaction_data
            foreach ($new_transaction_data as $value) {
                AccountingUtil::addJournal($value, $business_id, $location_id, $user_id);
            }

            // for edit_transaction_data
            foreach ($edit_transaction_data as $value) {
                $purchase_line = $value['purchase_line'];

                $price = $purchase_line->purchase_price * abs($purchase_line->quantity);

                $acc_trans_mapping = AccountingUtil::getLinkedWithAccountingAccTransMapping($business_id, 'transactions', $purchase_line->transaction_id);

                if ($acc_trans_mapping != null) {
                    $acc_trans_mapping->operation_date = $value['transaction_date'];

                    $acc_trans_mapping->note = $value['additional_notes'];

                    foreach ($acc_trans_mapping->childs() as $child) {
                        $acc = $child->account()->first();

                        if ($purchase_line->quantity > 0) {
                            if ($acc->gl_code == 34) {
                                $child->type = 'credit';
                            } elseif ($acc->gl_code == 1106) {
                                $child->type = 'debit';
                            }
                        } else {
                            if ($acc->gl_code == 34) {
                                $child->type = 'debit';
                            } elseif ($acc->gl_code == 1106) {
                                $child->type = 'credit';
                            }
                        }

                        $child->amount = $price;
                        $child->save();
                    }

                    $acc_trans_mapping->save();
                } else {
                    AccountingUtil::addJournal($value, $business_id, $location_id, $user_id);
                }

            }

            // for delete_transaction_data
            foreach ($delete_transaction_data as $value) {
                $acc_trans_mapping = AccountingUtil::getLinkedWithAccountingAccTransMapping($business_id, 'transactions', $value);

                if ($acc_trans_mapping != null) {
                    foreach ($acc_trans_mapping->childs() as $child) {
                        $child->delete();
                    }

                    $acc_trans_mapping->delete();
                }
            }
        }
    }

    public function MKamel_check333($data)
    {
        $request = $data['request'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $supplier_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $request->contact_id);

            if ($supplier_linked == null) {
                return ['success' => 0,
                    'msg' => __('accounting::lang.supplier_not_linked'),
                ];

            }

            return [
                'success' => 1,
                'supplier_linked' => $supplier_linked,
            ];
        }

        return [
            'success' => 2,
        ];

    }

  public function MKamel_store333($data)
{
    $request = $data['request'];
    $transaction_data = $data['transaction_data'];
    $supplier_linked = $data['supplier_linked'];
    $user_id = $data['user_id'];
    $transaction = $data['transaction'];
    $business_id = $request->session()->get('user.business_id');

    \Log::info('MKamel_store333 triggered for Transaction ID: ' . $transaction->id);

    $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

    if ($tree_accs) {
        // 🛠 Fix: Fetch purchase lines and cast discount_percent to full float precision
        $purchase_lines = $transaction->purchase_lines->map(function ($line) {
            \Log::info('COA Raw Purchase Line', $line->toArray());

            $line->discount_percent = isset($line->discount_percent)
                ? (float) number_format((float) $line->discount_percent, 6, '.', '')
                : 0.0;

            return $line->toArray();
        })->toArray();

        $output_amounts = AccountingUtil::getAmountsAcounts4($purchase_lines);

        \Log::info('Output amounts for purchase transaction', $output_amounts);
 
        $recs = [];

        // Stock
        $recs[] = [
            'accounting_account_id' => AccountingUtil::getAccountingAccountID(1106, $business_id),
            'amount' => $output_amounts['stock'],
            'type' => 'debit',
        ];

        // Tax
        if ($output_amounts['tax'] != 0) {
            $recs[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(2105, $business_id),
                'amount' => $output_amounts['tax'],
                'type' => 'debit',
            ];
        }

        // Shipping Charges
        if (!empty($transaction_data['shipping_charges']) && $transaction_data['shipping_charges'] != 0) {
            $recs[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(5104, $business_id),
                'amount' => $transaction_data['shipping_charges'],
                'type' => 'debit',
            ];
        }

        // Discount
        if ($output_amounts['discount'] != 0) {
            $recs[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(5105, $business_id),
                'amount' => $output_amounts['discount'],
                'type' => 'credit',
            ];
        }

        // Supplier Payable
        $recs[] = [
            'accounting_account_id' => $supplier_linked->id,
            'amount' => $output_amounts['supplier'] + $transaction_data['shipping_charges'],
            'type' => 'credit',
        ];

        \Log::info('Generated journal records for transaction ID: ' . $transaction->id, $recs);

        AccountingUtil::createJournalEntry(
            'journal_entry',
            $business_id,
            $transaction_data['location_id'],
            $user_id,
            $transaction_data['transaction_date'],
            'transactions',
            $transaction->id,
            $recs
        );

        \Log::info('Journal entry successfully created for transaction ID: ' . $transaction->id);
    } else {
        \Log::warning('Chart of Accounts not defined. Skipping journal entry for transaction ID: ' . $transaction->id);
    }
}


    public function MKamel_check222($data)
    {
        $request = $data['request'];

        $purchase = $data['purchase'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $supplier_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $purchase->contact_id);

            if ($supplier_linked == null) {
                return ['success' => 0,
                    'msg' => __('accounting::lang.supplier_not_linked'),
                ];
            }

            return [
                'success' => 1,
                'supplier_linked' => $supplier_linked,
            ];
        }

        return [
            'success' => 2,
        ];
    } 

    public function MKamel_store222($data)
    {
        $request = $data['request'];

        $purchase = $data['purchase'];

        $return_quantities = $data['return_quantities'];

        $supplier_linked = $data['supplier_linked'];

        $return_transaction = $data['return_transaction'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $output_amounts = AccountingUtil::getAmountsAcounts5($return_quantities, $purchase->purchase_lines);

            $recs = [];

            // stock
            $_rec1['accounting_account_id'] = AccountingUtil::getAccountingAccountID(1106, $business_id);
            $_rec1['amount'] = $output_amounts['stock'];
            $_rec1['type'] = 'credit';

            $recs[] = $_rec1;

            if ($output_amounts['tax'] != 0) {
                // tax
                $_rec2['accounting_account_id'] = AccountingUtil::getAccountingAccountID(2105, $business_id);
                $_rec2['amount'] = $output_amounts['tax'];
                $_rec2['type'] = 'credit';

                $recs[] = $_rec2;
            }

            // supplier
            $_rec3['accounting_account_id'] = $supplier_linked->id;
            $_rec3['amount'] = $output_amounts['supplier'];
            $_rec3['type'] = 'debit';

            $recs[] = $_rec3;

            if ($output_amounts['discount'] != 0) {
                // discount
                $_rec4['accounting_account_id'] = AccountingUtil::getAccountingAccountID(5105, $business_id);
                $_rec4['amount'] = $output_amounts['discount'];
                $_rec4['type'] = 'debit';

                $recs[] = $_rec4;
            }

            AccountingUtil::deleteJournalEntry('transactions', $return_transaction->id);


            AccountingUtil::createJournalEntry(
                'journal_entry',
                $business_id,
                $return_transaction->location_id,
                $return_transaction->created_by,
                $return_transaction->transaction_date,
                'transactions',
                $return_transaction->id,
                $recs
            );
        }
    }

    public function MKamel_check444($data)
    {
        $request = $data['request'];

        $input = $data['input'];

        $business_id = $request->session()->get('user.business_id');

        if ($input['status'] == 'final' && $input['is_suspend'] == 0) {

            $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

            if ($tree_accs) {
                $customer_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $input['contact_id']);

                if ($customer_linked == null) {
                    return ['success' => 0,
                        'msg' => __('accounting::lang.customer_not_linked'),
                    ];
                }

                $accounts_linked = [];
                if ($input['is_credit_sale'] == 0) {
                    foreach ($input['payment'] as $payment_done) {
                        if ($payment_done['method'] != 'advance') {
                            $account_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $payment_done['account_id']);

                            if ($account_linked == null) {
                                return [
                                    'success' => 0,
                                    'msg' => __('accounting::lang.account_not_linked'),
                                ];
                            }

                            $accounts_linked[] = $account_linked;
                        }
                    }
                }

                return [
                    'success' => 1,
                    'msg' => __('accounting::lang.account_not_linked'),
                    'accounts_linked' => $accounts_linked,
                    'customer_linked' => $customer_linked,
                ];

            }
        }

        return [
            'success' => 2,
        ];
    }

    public function xMKamel_store444($data)
    {

        $request = $data['request'];

        $input = $data['input'];

        $user_id = $data['user_id'];

        $transaction = $data['transaction'];

        $customer_linked = $data['customer_linked'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($input['status'] == 'final' && $input['is_suspend'] == 0) {
            if ($tree_accs) {

                $output_amounts = AccountingUtil::getAmountsAcounts($input['products']);

                // all discount
                $all_discount = AccountingUtil::r_s($input['discount_amount']);
                if ($input['discount_type'] == 'percentage') {
                    $all_discount = $output_amounts['customer'] * ($all_discount / 100);
                }

                // redeemed
                $rp_redeemed_amount = AccountingUtil::r_s($input['rp_redeemed_amount']);

                $all_discount_and_redeemed_amount = $all_discount + $rp_redeemed_amount;

                if ($all_discount_and_redeemed_amount != 0) {
                    $new_customer_amount = $output_amounts['customer'] - $all_discount_and_redeemed_amount;

                    $new_revenue_of_products_amount = $output_amounts['revenue_of_products'] * $new_customer_amount / $output_amounts['customer'];

                    $output_amounts['tax'] = $new_customer_amount - $new_revenue_of_products_amount;

                    $output_amounts['customer'] = $new_customer_amount;

                    $output_amounts['revenue_of_products'] = $new_revenue_of_products_amount;

                    $output_amounts['discount'] += $all_discount_and_redeemed_amount;

                }

                $recs = [];

                // customer
                $_rec1['accounting_account_id'] = $customer_linked->id;
                $_rec1['amount'] = $output_amounts['customer'] + AccountingUtil::r_s($input['shipping_charges']);
                $_rec1['type'] = 'debit';

                $recs[] = $_rec1;

                if ($output_amounts['cost_of_goods'] != 0) {
                    // cost_of_goods
                    $_rec2['accounting_account_id'] = AccountingUtil::getAccountingAccountID(5101, $business_id);
                    $_rec2['amount'] = $output_amounts['cost_of_goods'];
                    $_rec2['type'] = 'debit';

                    $recs[] = $_rec2;
                }

                if ($output_amounts['discount'] != 0) {
                    // discount
                    $_rec3['accounting_account_id'] = AccountingUtil::getAccountingAccountID(4102, $business_id);
                    $_rec3['amount'] = $output_amounts['discount'];
                    $_rec3['type'] = 'debit';

                    $recs[] = $_rec3;
                }

                if ($output_amounts['stock'] != 0) {
                    // stock
                    $_rec4['accounting_account_id'] = AccountingUtil::getAccountingAccountID(1106, $business_id);
                    $_rec4['amount'] = $output_amounts['stock'];
                    $_rec4['type'] = 'credit';

                    $recs[] = $_rec4;
                }

                // revenue_of_products
                $_rec5['accounting_account_id'] = AccountingUtil::getAccountingAccountID(4101, $business_id);
                $_rec5['amount'] = $output_amounts['revenue_of_products'] + $all_discount_and_redeemed_amount;
                $_rec5['type'] = 'credit';

                $recs[] = $_rec5;

                if ($output_amounts['tax'] != 0) {
                    // tax
                    $_rec6['accounting_account_id'] = AccountingUtil::getAccountingAccountID(2105, $business_id);
                    $_rec6['amount'] = $output_amounts['tax'];
                    $_rec6['type'] = 'credit';

                    $recs[] = $_rec6;
                }

                if ($input['shipping_charges'] != 0) {
                    // shipping_charges
                    $_rec8['accounting_account_id'] = AccountingUtil::getAccountingAccountID(5104, $business_id);
                    $_rec8['amount'] = AccountingUtil::r_s($input['shipping_charges']);
                    $_rec8['type'] = 'credit';

                    $recs[] = $_rec8;
                }

                AccountingUtil::createJournalEntry(
                    'journal_entry',
                    $business_id,
                    $input['location_id'],
                    $user_id,
                    $input['transaction_date'],
                    'transactions',
                    $transaction->id,
                    $recs
                );

            }

        }
    }

    public function MKamel_store444($data)
{
    $request = $data['request'];
    $input = $data['input'];
    $user_id = $data['user_id'];
    $transaction = $data['transaction'];
    $customer_linked = $data['customer_linked'];
    $business_id = $request->session()->get('user.business_id');
    $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

    if ($input['status'] == 'final' && $input['is_suspend'] == 0) {
        if ($tree_accs) {
            $output_amounts = AccountingUtil::getAmountsAcounts($input['products']);

            // All discount
            $all_discount = AccountingUtil::r_s($input['discount_amount']);
            if ($input['discount_type'] == 'percentage') {
                $all_discount = $output_amounts['customer'] * ($all_discount / 100);
            }

            // Redeemed
            $rp_redeemed_amount = AccountingUtil::r_s($input['rp_redeemed_amount']);
            $all_discount_and_redeemed_amount = $all_discount + $rp_redeemed_amount;

            if ($all_discount_and_redeemed_amount != 0) {
                $new_customer_amount = $output_amounts['customer'] - $all_discount_and_redeemed_amount;
                $new_revenue_of_products_amount = $output_amounts['revenue_of_products'] * $new_customer_amount / $output_amounts['customer'];

                $output_amounts['tax'] = $new_customer_amount - $new_revenue_of_products_amount;
                $output_amounts['customer'] = $new_customer_amount;
                $output_amounts['revenue_of_products'] = $new_revenue_of_products_amount;
                $output_amounts['discount'] += $all_discount_and_redeemed_amount;
            }

            $currency_code = $input['currency_code'] ?? null;
            $exchange_rate = $input['exchange_rate'] ?? 1;

            $recs = [];

            // Helper closure to format record with currency
            $formatRec = function($account_id, $amount, $type) use ($currency_code, $exchange_rate) {
                return [
                    'accounting_account_id' => $account_id,
                    'amount' => $amount,
                    'type' => $type,
                    'currency_code' => $currency_code,
                    'base_amount' => $currency_code ? $amount / $exchange_rate : null,
                    'exchange_rate' => $currency_code ? $exchange_rate : null,
                ];
            };

            // Customer (debit)
            $customer_amount = $output_amounts['customer'] + AccountingUtil::r_s($input['shipping_charges']);
            $recs[] = $formatRec($customer_linked->id, $customer_amount, 'debit');

            $cost_of_goods = AccountingUtil::r_s($output_amounts['cost_of_goods']);

            // Cost of Goods (debit)
            if ($cost_of_goods != 0) {
                $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(5101, $business_id), $cost_of_goods, 'debit');
            }

            // Discount (debit)
            if ($output_amounts['discount'] != 0) {
                $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(4102, $business_id), $output_amounts['discount'], 'debit');
            }

            // Stock (credit)
            if ($output_amounts['stock'] != 0) {
                $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(1106, $business_id), $output_amounts['stock'], 'credit');
            }

            // Revenue of Products (credit)
            $revenue_amount = $output_amounts['revenue_of_products'] + $all_discount_and_redeemed_amount;
            $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(4101, $business_id), $revenue_amount, 'credit');

            // Tax (credit)
            if ($output_amounts['tax'] != 0) {
                $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(2105, $business_id), $output_amounts['tax'], 'credit');
            }

            // Shipping Charges (credit)
            if ($input['shipping_charges'] != 0) {
                $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(5104, $business_id), AccountingUtil::r_s($input['shipping_charges']), 'credit');
            }

            $agent_user_id = $transaction->commission_agent ?? ($input['commission_agent'] ?? null);

// Use the stored snapshot; do NOT recalc here
$commission_amount = isset($transaction->commission_amount)
    ? (float) $transaction->commission_amount
    : 0.0;

            // ---------- NEW: build a readable note + pass currency meta ----------
            $pos_note = $input['additional_notes'] ?? $input['sale_note'] ?? null;

            $note = "";
            if ($pos_note) {
                $note .= "  " . mb_strimwidth($pos_note, 0, 250, '…');
            }

            $meta = [
                'currency_code' => $currency_code,
                'exchange_rate' => $currency_code ? $exchange_rate : null,
            ];
            // --------------------------------------------------------------------

            // Save Journal Entry with note + currency metadata
            AccountingUtil::createJournalEntry(
                'journal_entry',
                $business_id,
                $input['location_id'],
                $user_id,
                $input['transaction_date'],
                'transactions',
                $transaction->id,
                $recs,
                $note,     
                $meta      
            );
                        // ------- Commission JE (USE SNAPSHOTTED AMOUNT) -------
            $agent_user_id     = $transaction->commission_agent ?? ($input['commission_agent'] ?? null);
            $commission_amount = (float) ($transaction->commission_amount ?? 0);

            if (!empty($agent_user_id) && $commission_amount > 0) {
                // If your MKamel_store_commission has the basic signature:
                $this->MKamel_store_commission(
                    $business_id,
                    $agent_user_id,
                    AccountingUtil::r_s($commission_amount),
                    $transaction,
                    $input,
                    $user_id
                );
            }    
        }
    }
}
public function MKamel_store_commission(
    $business_id,
    $agent_user_id,
    $commission_amount,
    $transaction,
    $input,
    $user_id,
    $currency_code = null,     // optional: pass from MKamel_store444
    $exchange_rate = null      // optional: pass from MKamel_store444
) {
    // 1) Basic guards
    $amount = AccountingUtil::r_s($commission_amount);
    if (empty($agent_user_id) || $amount <= 0) {
        \Log::info("MKamel_store_commission skipped: agent={$agent_user_id}, amount={$amount}");
        return;
    }

    // 2) Resolve agent’s linked accounting account
    $agent_account = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
        ->where('status', 'active')
        ->where('link_table', 'users')
        ->where('link_id', $agent_user_id)
        ->first();

    if (!$agent_account) {
        \Log::warning("MKamel_store_commission: No linked account for agent user_id={$agent_user_id}");
        return;
    }

    // 3) Resolve Commission Expense GL (5103)
    $commission_expense_acct = AccountingUtil::getAccountingAccountID(5103, $business_id);
    if (!$commission_expense_acct) {
        \Log::error("MKamel_store_commission: Missing GL 5103 for business {$business_id}");
        return;
    }

    // 4) Currency metadata (optional)
    // If not explicitly passed, try to inherit from transaction
    if ($currency_code === null && !empty($transaction->currency_code)) {
        $currency_code = $transaction->currency_code;
        $exchange_rate = $transaction->exchange_rate ?: 1;
    }
    $exchange_rate = ($exchange_rate && $exchange_rate > 0) ? $exchange_rate : 1;

    $withCurrency = function(array $rec) use ($currency_code, $exchange_rate) {
        if ($currency_code) {
            $rec['currency_code'] = $currency_code;
            $rec['base_amount']   = $rec['amount'] / $exchange_rate; // company base currency
            $rec['exchange_rate'] = $exchange_rate;
        }
        return $rec;
    };

    // 5) Build JE lines: Debit Commission Expense, Credit Agent (payable/clearing)
    $recs = [
        $withCurrency([
            'accounting_account_id' => $commission_expense_acct,
            'amount'                => $amount,
            'type'                  => 'debit',
            'location_id'           => $input['location_id'] ?? null,
            'note'                  => '',
        ]),
        $withCurrency([
            'accounting_account_id' => $agent_account->id,
            'amount'                => $amount,
            'type'                  => 'credit',
            'location_id'           => $input['location_id'] ?? null,
            'note'                  => '',
        ]),
    ];

    // 6) Meta (helpful for downstream reporting)
    $meta = [
        'entry_type'               => 'commission',
        'commission_agent_user_id' => (int) $agent_user_id,
        'currency_code'            => $currency_code,
        'exchange_rate'            => $currency_code ? $exchange_rate : null,
    ];

    // 7) Create the JE
    AccountingUtil::createJournalEntry(
        'journal_entry',
        $business_id,
        $input['location_id'] ?? null,
        $user_id,
        $input['transaction_date'] ?? now(),
        'transactions',
        $transaction->id,
        $recs,
        "Commission for Agent ID {$agent_user_id}",
        $meta
    );

    \Log::info("MKamel_store_commission: JE created for tx={$transaction->id}, agent={$agent_user_id}, amount={$amount}");
}



    public function MKamel_store_payment444($data)
{
    $request = $data['request'];
    $input = $data['input'];
    $user_id = $data['user_id'];
    $transaction = $data['transaction'];
    $customer_linked = $data['customer_linked'];
    $accounts_linked = $data['accounts_linked'];
    $business_id = $request->session()->get('user.business_id');

    $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

    if ($input['status'] == 'final' && $input['is_suspend'] == 0 && $tree_accs) {
        if (!empty($accounts_linked)) {
            foreach ($transaction->payment_lines as $one_payment) {
                if ($one_payment->method != 'advance') {
                    $acc_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $one_payment->account_id);

                    if (!empty($acc_linked->id)) {
                        $recs = [];

                        $currency_code = $input['currency_code'] ?? null;
                        $exchange_rate = $input['exchange_rate'] ?? 1;

                        // Debit: Payment Account
                        $recs[] = [
                            'accounting_account_id' => $acc_linked->id,
                            'amount' => $one_payment->amount,
                            'type' => 'debit',
                            'currency_code' => $currency_code,
                            'exchange_rate' => $currency_code ? $exchange_rate : null,
    'base_amount' => $currency_code ? $one_payment->amount / $exchange_rate : null,
                        ];

                        // Credit: Customer Account
                        $recs[] = [
                            'accounting_account_id' => $customer_linked->id,
                            'amount' => $one_payment->amount,
                            'type' => 'credit',
                            'currency_code' => $currency_code,
                            'exchange_rate' => $currency_code ? $exchange_rate : null,
    'base_amount' => $currency_code ? $one_payment->amount / $exchange_rate : null,
                        ];

                        AccountingUtil::createJournalEntry(
                            'receipt',
                            $business_id,
                            $input['location_id'],
                            $user_id,
                            $one_payment->paid_on,
                            'transaction_payments',
                            $one_payment->id,
                            $recs
                        );
                    }
                }
            }
        }
    }
}

public function MKamel_update444($data)
{
    $request = $data['request'];
    $input = $data['input'];
    $user_id = $data['user_id'];
    $transaction = $data['transaction'];
    $customer_linked = $data['customer_linked'];
    $business_id = $request->session()->get('user.business_id');

    $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

    if ($input['status'] == 'final' && $input['is_suspend'] == 0 && $tree_accs) {
        // 🔁 Step 1: Delete previous journal entry
        AccountingUtil::deleteJournalEntry('transactions', $transaction->id);

        // 🔁 Step 2: Recalculate same as MKamel_store444
        $output_amounts = AccountingUtil::getAmountsAcounts($input['products']);

        // All discount
        $all_discount = AccountingUtil::r_s($input['discount_amount']);
        if ($input['discount_type'] == 'percentage') {
            $all_discount = $output_amounts['customer'] * ($all_discount / 100);
        }

        // Redeemed
        $rp_redeemed_amount = AccountingUtil::r_s($input['rp_redeemed_amount']);
        $all_discount_and_redeemed_amount = $all_discount + $rp_redeemed_amount;

        if ($all_discount_and_redeemed_amount != 0) {
            $new_customer_amount = $output_amounts['customer'] - $all_discount_and_redeemed_amount;
            $new_revenue_of_products_amount = $output_amounts['revenue_of_products'] * $new_customer_amount / $output_amounts['customer'];

            $output_amounts['tax'] = $new_customer_amount - $new_revenue_of_products_amount;
            $output_amounts['customer'] = $new_customer_amount;
            $output_amounts['revenue_of_products'] = $new_revenue_of_products_amount;
            $output_amounts['discount'] += $all_discount_and_redeemed_amount;
        }

        $currency_code = $input['currency_code'] ?? null;
        $exchange_rate = $input['exchange_rate'] ?? 1;

        $recs = [];

        $formatRec = function($account_id, $amount, $type) use ($currency_code, $exchange_rate) {
            return [
                'accounting_account_id' => $account_id,
                'amount' => $amount,
                'type' => $type,
                'currency_code' => $currency_code,
                'base_amount' => $currency_code ? $amount * $exchange_rate : null,
                'exchange_rate' => $currency_code ? $exchange_rate : null,
            ];
        };

        $customer_amount = $output_amounts['customer'] + AccountingUtil::r_s($input['shipping_charges']);
        $recs[] = $formatRec($customer_linked->id, $customer_amount, 'debit');

        if ($output_amounts['cost_of_goods'] != 0) {
            $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(5101, $business_id), $output_amounts['cost_of_goods'], 'debit');
        }

        if ($output_amounts['discount'] != 0) {
            $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(4102, $business_id), $output_amounts['discount'], 'debit');
        }

        if ($output_amounts['stock'] != 0) {
            $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(1106, $business_id), $output_amounts['stock'], 'credit');
        }

        $revenue_amount = $output_amounts['revenue_of_products'] + $all_discount_and_redeemed_amount;
        $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(4101, $business_id), $revenue_amount, 'credit');

        if ($output_amounts['tax'] != 0) {
            $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(2105, $business_id), $output_amounts['tax'], 'credit');
        }

        if ($input['shipping_charges'] != 0) {
            $recs[] = $formatRec(AccountingUtil::getAccountingAccountID(5104, $business_id), AccountingUtil::r_s($input['shipping_charges']), 'credit');
        }
$transaction_date = $input['transaction_date'] ?? $transaction->transaction_date ?? now();

        $result = AccountingUtil::createJournalEntry(
    'journal_entry',
    $business_id,
    $input['location_id'],
    $user_id,
    $transaction_date,
    'transactions',
    $transaction->id,
    $recs
);
$agent_user_id     = $transaction->commission_agent ?? ($input['commission_agent'] ?? null);
        $commission_amount = (float) ($transaction->commission_amount ?? 0);

        if (!empty($agent_user_id) && $commission_amount > 0) {
            $this->MKamel_store_commission(
                $business_id,
                $agent_user_id,
                AccountingUtil::r_s($commission_amount),
                $transaction,
                $input,
                $user_id,
                $currency_code,
                $exchange_rate
            );
        }
if ($result['success'] == 0) {
    \Log::error('MKamel_update444 failed to create journal entry: ' . $result['msg']);
}

    }
}


public function MKamel_update_payment444($data)
{
    $request = $data['request'];
    $input = $data['input'];
    $user_id = $data['user_id'];
    $transaction = $data['transaction'];
    $customer_linked = $data['customer_linked'];
    $accounts_linked = $data['accounts_linked'];
    $business_id = $request->session()->get('user.business_id');

    $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

    if ($input['status'] == 'final' && $input['is_suspend'] == 0 && $tree_accs) {
        if (!empty($accounts_linked)) {
            foreach ($transaction->payment_lines as $one_payment) {
                if ($one_payment->method != 'advance') {
                    // 🧹 Delete old journal entry
                    AccountingUtil::deleteJournalEntry('transaction_payments', $one_payment->id);

                    // 🔗 Get linked accounting account
                    $acc_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $one_payment->account_id);

                    if (!empty($acc_linked->id)) {
                        $recs = [];

                        $currency_code = $input['currency_code'] ?? null;
                        $exchange_rate = $input['exchange_rate'] ?? 1;

                        $recs[] = [
                            'accounting_account_id' => $acc_linked->id,
                            'amount' => $one_payment->amount,
                            'type' => 'debit',
                            'currency_code' => $currency_code,
                            'exchange_rate' => $currency_code ? $exchange_rate : null,
                            'base_amount' => $currency_code ? $one_payment->amount * $exchange_rate : null,
                        ];

                        $recs[] = [
                            'accounting_account_id' => $customer_linked->id,
                            'amount' => $one_payment->amount,
                            'type' => 'credit',
                            'currency_code' => $currency_code,
                            'exchange_rate' => $currency_code ? $exchange_rate : null,
                            'base_amount' => $currency_code ? $one_payment->amount * $exchange_rate : null,
                        ];

                        // 🔁 Recreate journal entry
                        AccountingUtil::createJournalEntry(
                            'receipt',
                            $business_id,
                            $input['location_id'],
                            $user_id,
                            $one_payment->paid_on,
                            'transaction_payments',
                            $one_payment->id,
                            $recs
                        );
                    }
                }
            }
        }
    }
}


    public function MKamel_check555($data)
    {

        $request = $data['request'];

        $input = $data['input'];

        $business_id = $request->session()->get('user.business_id');

        $sell = Transaction::where('business_id', $business_id)
            ->with(['sell_lines', 'sell_lines.sub_unit'])
            ->findOrFail($input['transaction_id']);

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $customer_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $sell->contact_id);

            if ($customer_linked == null) {
                return [
                    'success' => 0,
                    'msg' => __('accounting::lang.customer_not_linked'),
                ];

            }

            return [
                'success' => 1,
            ];
        }

        return [
            'success' => 2,
        ];
    }

    public function MKamel_store555($data)
    {
        $request = $data['request'];
        $input = $data['input'];
        $customer_linked = $data['customer_linked'];
        $user_id = $data['user_id'];
        $sell_return = $data['sell_return'];
        $business_id = $request->session()->get('user.business_id');
    
        if (!\Modules\Accounting\Utils\AccountingUtil::checkTreeOfAccountsIsHere()) {
            return;
        }
    
        $sell = \App\Transaction::where('business_id', $business_id)
            ->with(['sell_lines', 'sell_lines.sub_unit'])
            ->findOrFail($input['transaction_id']);

        $sell_return->load(['sell_lines', 'sell_lines.sub_unit']);
    
    
        $amounts = \Modules\Accounting\Utils\AccountingUtil::getAmountsAcounts2($sell_return->sell_lines);
/*
        \Log::info('Sell Return Lines', $sell_return->sell_lines->toArray());
        \Log::info('Amounts', $amounts);
*/    
        $entries = [];
    
        $parent_transaction = \App\Transaction::find($sell_return->return_parent_id);

        $currency_code = $sell_return->currency_code
    ?? $parent_transaction->currency_code
    ?? null;

$exchange_rate = (float) ($sell_return->exchange_rate
    ?? $parent_transaction->exchange_rate
    ?? 1);

if ($exchange_rate <= 0) {
    $exchange_rate = 1;
}
        $original_total = $parent_transaction->total_before_tax + $parent_transaction->tax_amount;
$discount_amount_from_sale = $parent_transaction->discount_amount;
$discount_type = $parent_transaction->discount_type;

$discount_multiplier = 1;

if ($original_total > 0) {
    if ($discount_type === 'percentage') {
        $discount_multiplier = (100 - $discount_amount_from_sale) / 100;
    } elseif ($discount_type === 'fixed') {
        $discount_multiplier = ($original_total - $discount_amount_from_sale) / $original_total;
    }
}

$amounts['tax'] = round($amounts['tax'] * $discount_multiplier, 4);
$amounts['revenue_of_products'] = round($amounts['revenue_of_products'] * $discount_multiplier, 4);
        $mirror_customer = strtolower($parent_transaction->payment_status ?? '') === 'paid';
    
        if (empty($input['refund_account_id'])) {
            throw new \Exception('Refund account is required.');
        }
    
        $refund_account_id = $input['refund_account_id'];
    
        // Core Amounts
        $tax_account_id = AccountingUtil::getAccountingAccountID(2105, $business_id); // Tax Payable
        $tax_amount = round($amounts['tax'], 4);
        $discount_amount = round($amounts['discount'] ?? 0, 4);
        $customer_total = round($amounts['customer'], 4);
    
        // Adjust net amount to exclude discount
        $net_amount = $customer_total - $discount_amount;
        $cost_of_goods = $amounts['cost_of_goods'] ;
        $revenue_of_products = $amounts['revenue_of_products'] + $discount_amount;
    
        // Mirror customer = PAID, so we reverse + redistribute
        if ($mirror_customer) {
            // 1. Mirror Customer: debit + credit (net)
            $entries[] = [
                'accounting_account_id' => $customer_linked->id,
                'amount' => $net_amount,
                'type' => 'credit',
            ];
            $entries[] = [
                'accounting_account_id' => $customer_linked->id,
                'amount' => $net_amount,
                'type' => 'debit',
            ];
    
            // 2. Credit refund account (net)
            $entries[] = [
                'accounting_account_id' => $refund_account_id,
                'amount' => $net_amount,
                'type' => 'credit',
            ];
    
           
        } else {
            // Unpaid: just credit customer for full (prepaid) value
            $entries[] = [
                'accounting_account_id' => $customer_linked->id,
                'amount' => $net_amount,
                'type' => 'credit',
            ];
        }
/*    
        // Log details
        \Log::info('Sell Return Breakdown', [
            'customer' => $customer_total,
            'discount' => $discount_amount,
            'tax' => $tax_amount,
            'net' => $net_amount,
        ]);
*/    
        // Product Revenue (debit)
        if (!empty($amounts['revenue_of_products'])) {
            $entries[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(4101, $business_id),
                'amount' => $revenue_of_products,
                'type' => 'debit',
            ];
        }
    
        // COGS (credit)
        if (!empty($amounts['cost_of_goods'])) {
            $entries[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(5101, $business_id),
                'amount' => $cost_of_goods,
                'type' => 'credit',
            ];
        }
    
        // Discount (credit)
        if (!empty($discount_amount)) {
            $entries[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(4102, $business_id),
                'amount' => $discount_amount,
                'type' => 'credit',
            ];
        }
    
        // Stock (debit)
        if (!empty($amounts['stock'])) {
            $entries[] = [
                'accounting_account_id' => AccountingUtil::getAccountingAccountID(1106, $business_id),
                'amount' => $amounts['stock'],
                'type' => 'debit',
            ];
        }
    
        // Tax (debit)
        if (!empty($tax_amount)) {
            $entries[] = [
                'accounting_account_id' => $tax_account_id,
                'amount' => $tax_amount,
                'type' => 'debit',
            ];
        }
    
        // Journal entry mapping
        $mapping = \Modules\Accounting\Entities\AccountingAccTransMapping::where('business_id', $business_id)
            ->where('type', 'journal_entry')
            ->where('link_table', 'transactions')
            ->where('link_id', $sell_return->id)
            ->first();

// ---------- Commission reversal (pro-rata to NET amount incl. tax, excl. parent shipping) ----------
$parent            = \App\Transaction::find($sell_return->return_parent_id);
$agent_user_id     = $parent->commission_agent ?? null;
$parent_commission = (float) ($parent->commission_amount ?? 0);

// currency meta (reuse what you computed earlier)
$currency_code = $currency_code ?? ($sell_return->currency_code ?? $parent->currency_code ?? null);
$exchange_rate = isset($exchange_rate) ? (float)$exchange_rate : (float)($sell_return->exchange_rate ?? $parent->exchange_rate ?? 1);
if ($exchange_rate <= 0) { $exchange_rate = 1; }

if ($agent_user_id && $parent_commission > 0) {
    $agent_account = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
        ->where('status', 'active')
        ->where('link_table', 'users')
        ->where('link_id', $agent_user_id)
        ->first();

    if ($agent_account) {
        $commission_expense_acct = \Modules\Accounting\Utils\AccountingUtil::getAccountingAccountID(5103, $business_id);

        // Parent NET (incl. tax) but EXCLUDING shipping (discounts already applied to final_total)
        $parent_net = max(
            0.0,
            (float)$parent->final_total - (float)($parent->shipping_charges ?? 0)
        );

        // Return NET (incl. tax): you already computed $net_amount above
        $return_net = max(0.0, (float)$net_amount);

        // Pro-rata ratio and commission reversal
        $ratio = $parent_net > 0 ? min(1, $return_net / $parent_net) : 0.0;
        $commission_reverse = round($parent_commission * $ratio, 4);

        if ($commission_reverse > 0) {
            $push = function(array $rec) use (&$entries, $currency_code, $exchange_rate) {
                if (!empty($currency_code)) {
                    $rec['currency_code'] = $currency_code;
                    $rec['base_amount']   = $rec['amount'] / $exchange_rate;
                    $rec['exchange_rate'] = $exchange_rate;
                }
                $entries[] = $rec;
            };

            // Reverse original sale posting:
            // Sale:  DR 5103 Commission Expense,  CR Agent
            // Return: CR 5103 Commission Expense,  DR Agent
            $push([
                'accounting_account_id' => $commission_expense_acct,
                'amount'                => $commission_reverse,
                'type'                  => 'credit',
            ]);
            $push([
                'accounting_account_id' => $agent_account->id,
                'amount'                => $commission_reverse,
                'type'                  => 'debit',
            ]);
/*
            \Log::info('Commission reversal on return (NET-based)', [
                'parent_tx'           => $parent->id,
                'sell_return_tx'      => $sell_return->id,
                'parent_commission'   => $parent_commission,
                'parent_net'          => $parent_net,
                'return_net'          => $return_net,
                'ratio'               => $ratio,
                'commission_reverse'  => $commission_reverse,
            ]);
*/            
        }
    }
}
// ---------- /Commission reversal ----------


    
        if ($mapping) {
            \Modules\Accounting\Entities\AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->delete();
    
            $mapping->operation_date = $sell_return->transaction_date;
            $mapping->note = 'Updated sell return #' . $sell_return->invoice_no;
            $mapping->updated_at = now();
            $mapping->save();
    
            foreach ($entries as $entry) {
                $entry['acc_trans_mapping_id'] = $mapping->id;
                $entry['location_id'] = $sell_return->location_id;
                $entry['operation_date'] = $sell_return->transaction_date;
                $entry['created_by'] = $user_id;
                $entry['sub_type'] = 'journal_entry';
                $entry['note'] = 'Sell return #' . $sell_return->invoice_no;
                $entry['transaction_id'] = $sell_return->id;
                $entry['link_table'] = 'transactions';
                $entry['link_id'] = $sell_return->id;
                $entry['created_at'] = now();
                $entry['updated_at'] = now();
    
                \Modules\Accounting\Entities\AccountingAccountsTransaction::create($entry);
            }
        } else {
            AccountingUtil::createJournalEntry(
                'journal_entry',
                $business_id,
                $sell_return->location_id,
                $user_id,
                $sell_return->transaction_date,
                'transactions',
                $sell_return->id,
                $entries,
                'Sell return #' . $sell_return->invoice_no
            );
        }
    }
    


    public function MKamel_store666($data)
    {
        $request = $data['request'];

        $products = $data['products'];

        $stock_adjustment = $data['stock_adjustment'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $output_amounts = AccountingUtil::getAmountsAcounts3($products);

            $recs = [];

            // stock
            $_rec1['accounting_account_id'] = AccountingUtil::getAccountingAccountID(1106, $business_id);
            $_rec1['amount'] = $output_amounts['stock'];
            $_rec1['type'] = 'credit';

            $recs[] = $_rec1;

            // Retained Earnings Or Losses
            $_rec2['accounting_account_id'] = AccountingUtil::getAccountingAccountID(34, $business_id);
            $_rec2['amount'] = $output_amounts['stock'];
            $_rec2['type'] = 'debit';

            $recs[] = $_rec2;

            AccountingUtil::createJournalEntry(
                'journal_entry',
                $business_id,
                $stock_adjustment->location_id,
                $stock_adjustment->created_by,
                $stock_adjustment->transaction_date,
                'transactions',
                $stock_adjustment->id,
                $recs,
                $request->input('additional_notes')
            );
        }
    }

    public function MKamel_store777($data)
    {
        $request = $data['request'];

        $products = $data['products'];

        $purchase_transfer = $data['purchase_transfer'];

        $status = $data['status'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {

            if ($status == 'completed') {

                $output_amounts = AccountingUtil::getAmountsAcounts3($products);

                $recs = [];

                // stock
                $_rec1['accounting_account_id'] = AccountingUtil::getAccountingAccountID(1106, $business_id);
                $_rec1['amount'] = $output_amounts['stock'];
                $_rec1['location_id'] = Transaction::find($purchase_transfer->transfer_parent_id)->location_id;
                $_rec1['type'] = 'credit';

                $recs[] = $_rec1;

                // stock
                $_rec2['accounting_account_id'] = AccountingUtil::getAccountingAccountID(1106, $business_id);
                $_rec2['amount'] = $output_amounts['stock'];
                $_rec2['location_id'] = $purchase_transfer->location_id;
                $_rec2['type'] = 'debit';

                $recs[] = $_rec2;

                AccountingUtil::createJournalEntry(
                    'journal_entry',
                    $business_id,
                    $purchase_transfer->location_id,
                    $purchase_transfer->created_by,
                    $purchase_transfer->transaction_date,
                    'transactions',
                    $purchase_transfer->id,
                    $recs,
                    $request->input('additional_notes')
                );
            }

        }
    }

    public function MKamel_check888($data)
    {
        $request = $data['request'];
        $transaction = $data['transaction'];
        $business_id = $request->session()->get('user.business_id');
        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs && $request->input('method') != 'advance') {
            // For all types except expense/expense_refund, require contact
            $requires_contact = !in_array($transaction->type, ['expense', 'expense_refund']);

            $contact_linked = $requires_contact 
                ? AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $transaction->contact_id)
                : null;

            if ($requires_contact && $contact_linked === null) {
                return [
                    'success' => 0,
                    'msg' => __('accounting::lang.supplier_not_linked'),
                ];
            }

            $account_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $request->input('account_id'));

            if ($account_linked === null) {
                return [
                    'success' => 0,
                    'msg' => __('accounting::lang.account_not_linked'),
                ];
            }

            return [
                'success' => 1,
                'contact_linked' => $contact_linked,
                'account_linked' => $account_linked,
            ];
        }

        return ['success' => 2];
    }



    public function MKamel_store888($data)
    {
        $request = $data['request'];
        $contact_linked = $data['contact_linked'] ?? null;
        $account_linked = $data['account_linked'];
        $transaction = $data['transaction'];
        $tp = $data['tp'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs && $request->input('method') != 'advance') {
            $recs = [];

            switch ($transaction->type) {
                case 'purchase':

        $currency_code = $tp->currency_code ?? null;

        if (!empty($currency_code)) {
            $exchange_rate = $tp->exchange_rate ?? 1;
            $base_amount = $tp->base_amount ?? ($tp->amount * $exchange_rate);
        } else {
            $exchange_rate = null;
            $base_amount = null;
        }

        $recs[] = [
            'accounting_account_id' => $contact_linked->id,
            'amount' => $tp->amount,
            'base_amount' => $base_amount,
            'currency_code' => $currency_code,
            'exchange_rate' => $exchange_rate,
            'type' => 'debit'
        ];

        $recs[] = [
            'accounting_account_id' => $account_linked->id,
            'amount' => $tp->amount,
            'base_amount' => $base_amount,
            'currency_code' => $currency_code,
            'exchange_rate' => $exchange_rate,
            'type' => 'credit'
        ];

        AccountingUtil::createJournalEntry(
            'expense',
            $business_id,
            $transaction->location_id,
            $tp->created_by,
            $tp->paid_on,
            'transaction_payments',
            $tp->id,
            $recs
        );
        break;


                case 'purchase_return':
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'credit'
                    ];
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'debit'
                    ];

                    AccountingUtil::createJournalEntry(
                        'receipt',
                        $business_id,
                        $transaction->location_id,
                        $tp->created_by,
                        $tp->paid_on,
                        'transaction_payments',
                        $tp->id,
                        $recs
                    );
                    break;

                case 'sell':
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'debit'
                    ];
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'credit'
                    ];

                    AccountingUtil::createJournalEntry(
                        'receipt',
                        $business_id,
                        $transaction->location_id,
                        $tp->created_by,
                        $tp->paid_on,
                        'transaction_payments',
                        $tp->id,
                        $recs
                    );
                    break;

                case 'sell_return':
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'credit'
                    ];
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'debit'
                    ];

                    AccountingUtil::createJournalEntry(
                        'expense',
                        $business_id,
                        $transaction->location_id,
                        $tp->created_by,
                        $tp->paid_on,
                        'transaction_payments',
                        $tp->id,
                        $recs
                    );
                    break;

                    case 'expense':
                        $expense_account = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'expense_categories', $transaction->expense_category_id);
                    
                        if (! $expense_account) {
                            return [
                                'success' => 0,
                                'msg' => __('accounting::lang.expense_category_not_linked')
                            ];
                        }
                    
                        $recs[] = [
                            'accounting_account_id' => $expense_account->id, 
                            'amount' => $tp->amount,
                            'type' => 'debit'
                        ];
                        $recs[] = [
                            'accounting_account_id' => $account_linked->id,
                            'amount' => $tp->amount,
                            'type' => 'credit'
                        ];
                    
                        AccountingUtil::createJournalEntry(
                            'expense',
                            $business_id,
                            $transaction->location_id,
                            $tp->created_by,
                            $tp->paid_on,
                            'transaction_payments',
                            $tp->id,
                            $recs
                        );
                        break;
                    

                        case 'expense_refund':
                            $expense_account = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'expense_categories', $transaction->expense_category_id);
                        
                            if (! $expense_account) {
                                return [
                                    'success' => 0,
                                    'msg' => __('accounting::lang.expense_category_not_linked')
                                ];
                            }
                        
                            $recs = [];
                        
                            $recs[] = [
                                'accounting_account_id' => $expense_account->id,
                                'amount' => $tp->amount,
                                'type' => 'credit'
                            ];
                        
                            $recs[] = [
                                'accounting_account_id' => $account_linked->id,
                                'amount' => $tp->amount,
                                'type' => 'debit'
                            ];
                        
                            AccountingUtil::createJournalEntry(
                                'receipt',
                                $business_id,
                                $transaction->location_id,
                                $tp->created_by,
                                $tp->paid_on,
                                'transaction_payments',
                                $tp->id,
                                $recs
                            );
                            break;
                        
                default:
                    break;
            }
        }
    }



    public function MKamel_check_2_888($data)
    {
        $request = $data['request'];

        $business_id = $request->session()->get('user.business_id');

        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $contact_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $request->input('contact_id'));

            if ($contact_linked == null) {
                return ['success' => 0,
                    'msg' => __('accounting::lang.supplier_not_linked'),
                ];

            }

            $account_linked = AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $request->input('account_id'));

            if ($account_linked == null) {
                return ['success' => 0,
                    'msg' => __('accounting::lang.account_not_linked'),
                ];

            }

            return ['success' => 1,
                'contact_linked' => $contact_linked,
                'account_linked' => $account_linked,
            ];
        }

        return ['success' => 2,
        ];
    }

    public function MKamel_store_2_888($data)
    {
        $request = $data['request'];
        $contact_linked = $data['contact_linked'];
        $account_linked = $data['account_linked'];
        $tp = $data['tp'];
        $business_id = $request->session()->get('user.business_id');
        $tree_accs = AccountingUtil::checkTreeOfAccountsIsHere();

        if ($tree_accs) {
            $contact = Contact::where('business_id', $business_id)
                ->findOrFail($request->input('contact_id'));

            $recs = [];
            $entry_type = '';

            $currency_code = $tp->currency_code;
            $exchange_rate = $tp->exchange_rate;
            $base_amount = $tp->base_amount;

            if ($contact->type == 'supplier') {
                if ($tp->amount > 0) {
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'debit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'credit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $entry_type = 'expense';
                } else {
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => abs($tp->amount),
                        'type' => 'credit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => abs($tp->amount),
                        'type' => 'debit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $entry_type = 'supplier_refund';
                }
            }

            elseif ($contact->type == 'customer') {
                if ($tp->amount > 0) {
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'debit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => $tp->amount,
                        'type' => 'credit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $entry_type = 'receipt';
                } else {
                    $recs[] = [
                        'accounting_account_id' => $contact_linked->id,
                        'amount' => abs($tp->amount),
                        'type' => 'debit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $recs[] = [
                        'accounting_account_id' => $account_linked->id,
                        'amount' => abs($tp->amount),
                        'type' => 'credit',
                        'currency_code' => $currency_code,
                        'exchange_rate' => $exchange_rate,
                        'base_amount' => $base_amount,
                    ];
                    $entry_type = 'customer_payment';
                }
            }

            AccountingUtil::createJournalEntry(
                $entry_type,
                $business_id,
                AccountingUtil::getLcationID($business_id),
                $tp->created_by,
                $tp->paid_on,
                null,
                null,
                $recs,
                $tp->payment_ref_no,
                [
                    'currency_code' => $currency_code,
                    'exchange_rate' => $exchange_rate,
                    'base_amount' => $base_amount,
                ]
            );
        }
    }
}
