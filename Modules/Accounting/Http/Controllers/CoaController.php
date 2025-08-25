<?php

namespace Modules\Accounting\Http\Controllers;

use App\Account;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\ExpenseCategory;
use App\InvoiceLayout;
use App\User;
use App\Utils\ModuleUtil;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccountType;
use Modules\Accounting\Utils\AccountingUtil;
use Yajra\DataTables\Facades\DataTables;

class CoaController extends Controller
{
    protected $accountingUtil;

    protected $moduleUtil;


    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(AccountingUtil $accountingUtil, ModuleUtil $moduleUtil)
    {
        $this->accountingUtil = $accountingUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        $account_types = AccountingAccountType::accounting_primary_type();

        foreach ($account_types as $k => $v) {
            $account_types[$k] = $v['label'];
        }

        if (request()->ajax()) {
            $balance_formula = $this->accountingUtil->balanceFormula('AA');

            $query = AccountingAccount::where('business_id', $business_id)
                ->whereNull('parent_account_id')
                ->with(['child_accounts' => function ($query) use ($balance_formula): void {
                    $query->select([DB::raw("(SELECT $balance_formula from accounting_accounts_transactions AS AAT
                                        JOIN accounting_accounts AS AA ON AAT.accounting_account_id = AA.id
                                        WHERE AAT.accounting_account_id = accounting_accounts.id) AS balance"), 'accounting_accounts.*']);
                },
                    'child_accounts.detail_type', 'detail_type', 'account_sub_type',
                    'child_accounts.account_sub_type', ])
                ->select([DB::raw("(SELECT $balance_formula
                                    FROM accounting_accounts_transactions AS AAT 
                                    JOIN accounting_accounts AS AA ON AAT.accounting_account_id = AA.id
                                    WHERE AAT.accounting_account_id = accounting_accounts.id) AS balance"),
                    'accounting_accounts.*', ]);

            if (! empty(request()->input('account_type'))) {
                $query->where('accounting_accounts.account_primary_type', request()->input('account_type'));
            }
            if (! empty(request()->input('status'))) {
                $query->where('accounting_accounts.status', request()->input('status'));
            }

            $accounts = $query->get();

            $account_exist = AccountingAccount::where('business_id', $business_id)->exists();

            if (request()->input('view_type') == 'table') {
                return view('accounting::chart_of_accounts.accounts_table')
                    ->with(compact('accounts', 'account_exist'));
            } else {
                $account_sub_types = AccountingAccountType::where('account_type', 'sub_type')
                    ->where(function ($q) use ($business_id): void {
                        $q->whereNull('business_id')
                            ->orWhere('business_id', $business_id);
                    })
                    ->get();

                return view('accounting::chart_of_accounts.accounts_tree')
                    ->with(compact('accounts', 'account_exist', 'account_types', 'account_sub_types'));
            }
        }

        return view('accounting::chart_of_accounts.index')->with(compact('account_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $account_types = AccountingAccountType::accounting_primary_type();

            return view('accounting::chart_of_accounts.create')->with(compact('account_types'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function createDefaultAccounts()
    {
        // check no accounts
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');

        if (AccountingAccount::where('business_id', $business_id)->doesntExist()) {

            $default_accounts = [
                0 => [
                    'name' => 'Cash And Equivalents',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'detail_type_id' => 1101,
                    'gl_code' => 1101,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Cash In Bank',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'detail_type_id' => 1102,
                    'gl_code' => 1102,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Accounts Receivable',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'detail_type_id' => 1103,
                    'gl_code' => 1103,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Prepaid Expenses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'detail_type_id' => 1104,
                    'gl_code' => 1104,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                4 => [
                    'name' => 'Staff Advances',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'detail_type_id' => 1105,
                    'gl_code' => 1105,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                5 => [
                    'name' => 'Inventory',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'detail_type_id' => 1106,
                    'gl_code' => 1106,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
            ];

            AccountingAccount::insert($default_accounts);

            $WalkInCustomer = $this->getWalkInCustomer($business_id);

            $default_accounts = [
                0 => [
                    'name' => 'Walk In Customer',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1103, $business_id),
                    'detail_type_id' => 1103,
                    'gl_code' => 110301,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                    'link_table' => $WalkInCustomer != null ? 'contacts' : '',
                    'link_id' => $WalkInCustomer != null ? $WalkInCustomer->id : '',
                ],
            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Cash On Hand',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1101, $business_id),
                    'detail_type_id' => 1101,
                    'gl_code' => 110101,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Petty Cash',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1101, $business_id),
                    'detail_type_id' => 1101,
                    'gl_code' => 110102,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Issued Capital',
                    'business_id' => $business_id,
                    'account_primary_type' => 'equity',
                    'account_sub_type_id' => 3,
                    'detail_type_id' => 31,
                    'gl_code' => 31,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Other Equity',
                    'business_id' => $business_id,
                    'account_primary_type' => 'equity',
                    'account_sub_type_id' => 3,
                    'detail_type_id' => 32,
                    'gl_code' => 32,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Reserve',
                    'business_id' => $business_id,
                    'account_primary_type' => 'equity',
                    'account_sub_type_id' => 3,
                    'detail_type_id' => 33,
                    'gl_code' => 33,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Retained Earnings Or Losses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'equity',
                    'account_sub_type_id' => 3,
                    'detail_type_id' => 34,
                    'gl_code' => 34,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Opening Balance',
                    'business_id' => $business_id,
                    'account_primary_type' => 'equity',
                    'account_sub_type_id' => 3,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(32, $business_id),
                    'detail_type_id' => 32,
                    'gl_code' => 3201,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Property Plant And Equipmen',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'detail_type_id' => 1201,
                    'gl_code' => 1201,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Intangible Assets',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'detail_type_id' => 1202,
                    'gl_code' => 1202,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Investment Property',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'detail_type_id' => 1203,
                    'gl_code' => 1203,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Accounts Payable',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2101,
                    'gl_code' => 2101,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Accrued Expenses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2102,
                    'gl_code' => 2102,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Accrued Salaries',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2103,
                    'gl_code' => 2103,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Short Term Loans',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2104,
                    'gl_code' => 2104,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                4 => [
                    'name' => 'VAT Payable',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2105,
                    'gl_code' => 2105,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                5 => [
                    'name' => 'Accrued Taxes',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2106,
                    'gl_code' => 2106,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                6 => [
                    'name' => 'Unearned Revenues',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2107,
                    'gl_code' => 2107,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                7 => [
                    'name' => 'General Organization For Social Insurance Payable',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2108,
                    'gl_code' => 2108,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                8 => [
                    'name' => 'Accumulated Depreciation',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'detail_type_id' => 2109,
                    'gl_code' => 2109,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Long Term Loans',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 22,
                    'detail_type_id' => 2201,
                    'gl_code' => 2201,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'End Of Services Provision',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 22,
                    'detail_type_id' => 2202,
                    'gl_code' => 2202,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Revenue Of Products And Services Sales',
                    'business_id' => $business_id,
                    'account_primary_type' => 'income',
                    'account_sub_type_id' => 41,
                    'detail_type_id' => 4101,
                    'gl_code' => 4101,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Sales Discount',
                    'business_id' => $business_id,
                    'account_primary_type' => 'income',
                    'account_sub_type_id' => 41,
                    'detail_type_id' => 4102,
                    'gl_code' => 4102,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Sales Returns',
                    'business_id' => $business_id,
                    'account_primary_type' => 'income',
                    'account_sub_type_id' => 41,
                    'detail_type_id' => 4103,
                    'gl_code' => 4103,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Other Income',
                    'business_id' => $business_id,
                    'account_primary_type' => 'income',
                    'account_sub_type_id' => 42,
                    'detail_type_id' => 4201,
                    'gl_code' => 4201,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Salaries And Administrative Fees',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5201,
                    'gl_code' => 5201,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Medical Insurance And Treatment',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5202,
                    'gl_code' => 5202,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Marketing And Advertising',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5203,
                    'gl_code' => 5203,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Rental Expenses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5204,
                    'gl_code' => 5204,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                4 => [
                    'name' => 'Commissions And Incentives',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5205,
                    'gl_code' => 5205,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                5 => [
                    'name' => 'Travel Expenses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5206,
                    'gl_code' => 5206,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                6 => [
                    'name' => 'Social Insurance Expense',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5207,
                    'gl_code' => 5207,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                7 => [
                    'name' => 'Government Fees',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5208,
                    'gl_code' => 5208,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                8 => [
                    'name' => 'Fees And Subscriptions',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5209,
                    'gl_code' => 5209,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                9 => [
                    'name' => 'Utilities Expenses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5210,
                    'gl_code' => 5210,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                10 => [
                    'name' => 'Stationery And Prints',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5211,
                    'gl_code' => 5211,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                11 => [
                    'name' => 'Hospitality And Cleanliness',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5212,
                    'gl_code' => 5212,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                12 => [
                    'name' => 'Bank Commissions',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5213,
                    'gl_code' => 5213,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                13 => [
                    'name' => 'Other Expenses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5214,
                    'gl_code' => 5214,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                14 => [
                    'name' => 'Depreciation',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5215,
                    'gl_code' => 5215,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                15 => [
                    'name' => 'Transportation Expense',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'detail_type_id' => 5216,
                    'gl_code' => 5216,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Cost Of Goods Sold',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 51,
                    'detail_type_id' => 5101,
                    'gl_code' => 5101,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Salaries And Wages',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 51,
                    'detail_type_id' => 5102,
                    'gl_code' => 5102,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Sales Commissions',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 51,
                    'detail_type_id' => 5103,
                    'gl_code' => 5103,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Shipping And Custom Fees',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 51,
                    'detail_type_id' => 5104,
                    'gl_code' => 5104,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                4 => [
                    'name' => 'Discount On Purchases',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 51,
                    'detail_type_id' => 5105,
                    'gl_code' => 5105,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                5 => [
                    'name' => 'Returns Purchases',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 51,
                    'detail_type_id' => 5106,
                    'gl_code' => 5106,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Zakat',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 53,
                    'detail_type_id' => 5301,
                    'gl_code' => 5301,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'TAX',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 53,
                    'detail_type_id' => 5302,
                    'gl_code' => 5302,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Change In Currency Value Gains Or Losses',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 53,
                    'detail_type_id' => 5303,
                    'gl_code' => 5303,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Interest',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 53,
                    'detail_type_id' => 5304,
                    'gl_code' => 5304,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Bank Current Account Bank Name',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1102, $business_id),
                    'detail_type_id' => 1102,
                    'gl_code' => 110201,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Bank Demo',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1102, $business_id),
                    'detail_type_id' => 1102,
                    'gl_code' => 110202,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Prepaid Medical Insurance',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1104, $business_id),
                    'detail_type_id' => 1104,
                    'gl_code' => 110401,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Prepaid Rent',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 11,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1104, $business_id),
                    'detail_type_id' => 1104,
                    'gl_code' => 110402,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Lands',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1201, $business_id),
                    'detail_type_id' => 1201,
                    'gl_code' => 120101,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Buildings',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1201, $business_id),
                    'detail_type_id' => 1201,
                    'gl_code' => 120102,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Equipment',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1201, $business_id),
                    'detail_type_id' => 1201,
                    'gl_code' => 120103,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                3 => [
                    'name' => 'Computers Printers',
                    'business_id' => $business_id,
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 12,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(1201, $business_id),
                    'detail_type_id' => 1201,
                    'gl_code' => 120104,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Buildings Accumulated Depreciation',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(2109, $business_id),
                    'detail_type_id' => 2109,
                    'gl_code' => 210901,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Equipment Accumulated Depreciation',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(2109, $business_id),
                    'detail_type_id' => 2109,
                    'gl_code' => 210902,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Computers Printers Accumulated Depreciation',
                    'business_id' => $business_id,
                    'account_primary_type' => 'liability',
                    'account_sub_type_id' => 21,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(2109, $business_id),
                    'detail_type_id' => 2109,
                    'gl_code' => 210903,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);

            $default_accounts = [
                0 => [
                    'name' => 'Buildings Depreciation Expense',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(5215, $business_id),
                    'detail_type_id' => 5215,
                    'gl_code' => 521501,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                1 => [
                    'name' => 'Equipment Depreciation Expense',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(5215, $business_id),
                    'detail_type_id' => 5215,
                    'gl_code' => 521502,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],
                2 => [
                    'name' => 'Computers Printers Depreciation Expense',
                    'business_id' => $business_id,
                    'account_primary_type' => 'expenses',
                    'account_sub_type_id' => 52,
                    'parent_account_id' => AccountingUtil::getAccountingAccountID(5215, $business_id),
                    'detail_type_id' => 5215,
                    'gl_code' => 521503,
                    'status' => 'active',
                    'created_by' => $user_id,
                    'created_at' => \Carbon::now(),
                    'updated_at' => \Carbon::now(),
                ],

            ];

            AccountingAccount::insert($default_accounts);
        }

        Business::where('id', $business_id)
            ->update(
                ['accounting_settings' => '{"journal_entry_prefix":"JE",
                "transfer_prefix":"TP",
                "expense_prefix":"EP",
                "opening_balance_prefix":"SB",
                "receipt_prefix":"RP"}']
            );

        // redirect back
        $output = ['success' => 1,
            'msg' => __('lang_v1.added_success'),
        ];

        return redirect()->back()->with('status', $output);
    }

    public function getWalkInCustomer($business_id)
    {
        $contact = Contact::where('business_id', $business_id)
            ->where('is_default', 1)->first();

        if (isset($contact->id)) {
            return $contact;
        } else {
            return null;
        }
    }

    public function getAccountDetailsType()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $account_type_id = request()->input('account_type_id');
            $detail_types_obj = AccountingAccountType::where('parent_id', $account_type_id)
                ->where(function ($q) use ($business_id): void {
                    $q->whereNull('business_id')
                        ->orWhere('business_id', $business_id);
                })
                ->where('account_type', 'detail_type')
                ->get();

            $parent_accounts = AccountingAccount::where('business_id', $business_id)
                ->where('account_sub_type_id', $account_type_id)
                ->whereNull('parent_account_id')
                ->select('name as text', 'id')
                ->get();
            $parent_accounts->prepend([
                'id' => 'null',
                'text' => __('messages.please_select'),
            ]);

            $detail_types = [[
                'id' => 'null',
                'text' => __('messages.please_select'),
                'description' => '',
            ]];

            foreach ($detail_types_obj as $detail_type) {
                $detail_types[] = [
                    'id' => $detail_type->id,
                    'text' => $detail_type->account_type_name,
                    'description' => $detail_type->account_type_description,
                ];
            }

            return [
                'detail_types' => $detail_types,
                'parent_accounts' => $parent_accounts,
            ];
        }
    }

    public function getAccountSubTypes()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $account_primary_type = request()->input('account_primary_type');
            $sub_types_obj = AccountingAccountType::where('account_primary_type', $account_primary_type)
                ->where(function ($q) use ($business_id): void {
                    $q->whereNull('business_id')
                        ->orWhere('business_id', $business_id);
                })
                ->where('account_type', 'sub_type')
                ->get();

            $sub_types = [[
                'id' => 'null',
                'text' => __('messages.please_select'),
                'show_balance' => 0,
            ]];

            foreach ($sub_types_obj as $st) {
                $sub_types[] = [
                    'id' => $st->id,
                    'text' => $st->account_type_name,
                    'show_balance' => $st->show_balance,
                ];
            }

            return [
                'sub_types' => $sub_types,
            ];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->only(['name', 'account_primary_type', 'account_sub_type_id', 'detail_type_id',
                'parent_account_id', 'description', 'gl_code', ]);

            $account_type = AccountingAccountType::find($input['account_sub_type_id']);

            $input['parent_account_id'] = ! empty($input['parent_account_id'])
            && $input['parent_account_id'] !== 'null' ? $input['parent_account_id'] : null;
            $input['created_by'] = auth()->user()->id;
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['status'] = 'active';

            $account = AccountingAccount::create($input);

            if ($account_type->show_balance == 1 && ! empty($request->input('balance'))) {
                // Opening balance
                $data = [
                    'amount' => $this->accountingUtil->num_uf($request->input('balance')),
                    'accounting_account_id' => $account->id,
                    'created_by' => auth()->user()->id,
                    'operation_date' => ! empty($request->input('balance_as_of')) ?
                    $this->accountingUtil->uf_date($request->input('balance_as_of')) :
                    \Carbon::today()->format('Y-m-d'),
                ];

                // Opening balance
                $data['type'] = in_array($input['account_primary_type'], ['asset', 'expenses']) ? 'debit' : 'credit';
                $data['sub_type'] = 'opening_balance';
                AccountingAccountsTransaction::createTransaction($data);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $account = AccountingAccount::where('business_id', $business_id)
                ->with(['detail_type'])
                ->find($id);

            $account_types = AccountingAccountType::accounting_primary_type();
            $account_sub_types = AccountingAccountType::where('account_primary_type', $account->account_primary_type)
                ->where('account_type', 'sub_type')
                ->where(function ($q) use ($business_id): void {
                    $q->whereNull('business_id')
                        ->orWhere('business_id', $business_id);
                })
                ->get();
            $account_detail_types = AccountingAccountType::where('parent_id', $account->account_sub_type_id)
                ->where('account_type', 'detail_type')
                ->where(function ($q) use ($business_id): void {
                    $q->whereNull('business_id')
                        ->orWhere('business_id', $business_id);
                })
                ->get();

            $parent_accounts = AccountingAccount::where('business_id', $business_id)
                ->where('account_sub_type_id', $account->account_sub_type_id)
                ->whereNull('parent_account_id')
                ->get();

            return view('accounting::chart_of_accounts.edit')->with(compact('account_types', 'account',
                'account_sub_types', 'account_detail_types', 'parent_accounts'));
        }
    }

    public function edit_link($id, $link_table)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $account = AccountingAccount::where('business_id', $business_id)
                ->with(['detail_type'])
                ->find($id);

            $link_id = null;

            if ($link_table == 'contacts') {
                $link_id = $this->getAllContacts($business_id, $account);
            }
            if ($link_table == 'accounts') {
                $link_id = $this->getAllAccounts($business_id);
            }
            if ($link_table == 'expense_categories') {
                $link_id = $this->getAllExpenseCategories($business_id);
            }
            if ($link_table == 'users') {
                $link_id = $this->getAllUsers($business_id);
            }

            return view('accounting::chart_of_accounts.edit_link')->with(compact('account', 'link_id', 'link_table'));
        }
    }

    public function getAllContacts($business_id, $account)
    {
        $contacts = [];

        if ($account->parent->gl_code == 1103) {
            $contacts = Contact::customersDropdown($business_id, false);
        } elseif ($account->parent->gl_code == 2101) {
            $contacts = Contact::suppliersDropdown($business_id, false);
        }

        foreach ($contacts as $key => $value) {
            if (AccountingUtil::getLinkedWithAccountingAccount($business_id, 'contacts', $key) != null) {
                unset($contacts[$key]);
            }
        }

        return $contacts;
    }

    public function getAllExpenseCategories($business_id)
    {

        $categories = ExpenseCategory::where('business_id', $business_id)->pluck('name', 'id');

        foreach ($categories as $key => $value) {
            if (AccountingUtil::getLinkedWithAccountingAccount($business_id, 'expense_categories', $key) != null) {
                unset($categories[$key]);
            }
        }

        return $categories;
    }

    public function getAllAccounts($business_id)
    {
        $accounts = [];

        $accounts = Account::forDropdown($business_id, false);

        foreach ($accounts as $key => $value) {
            if (AccountingUtil::getLinkedWithAccountingAccount($business_id, 'accounts', $key) != null) {
                unset($accounts[$key]);
            }
        }

        return $accounts;
    }

    public function getAllUsers($business_id)
    {
        $users = [];

        $users = User::forDropdown($business_id, false);

        foreach ($users as $key => $value) {
            if (AccountingUtil::getLinkedWithAccountingAccount($business_id, 'users', $key) != null) {
                unset($users[$key]);
            }
        }

        return $users;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->only(['name', 'account_primary_type', 'account_sub_type_id', 'detail_type_id',
                'parent_account_id', 'description', 'gl_code', ]);

            $input['parent_account_id'] = ! empty($input['parent_account_id'])
            && $input['parent_account_id'] !== 'null' ? $input['parent_account_id'] : null;

            $account = AccountingAccount::find($id);
            $account->update($input);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }

        return redirect()->back();
    }

    public function update_link(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->only(['link_id', 'link_table']);

            if (! empty($input['link_id'])) {
                $account = AccountingAccount::find($id);
                $account->link_table = $input['link_table'];
                $account->link_id = $input['link_id'];
                $account->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function activateDeactivate($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $account = AccountingAccount::where('business_id', $business_id)
                ->find($id);

            $account->status = $account->status == 'active' ? 'inactive' : 'active';
            $account->save();

            $msg = $account->status == 'active' ? __('accounting::lang.activated_successfully') :
            __('accounting::lang.deactivated_successfully');
            $output = ['success' => 1,
                'msg' => $msg,
            ];

            return $output;
        }
    }

    /**
     * Displays the ledger of the account
     *
     * @param  int  $account_id
     * @return Response
     */
    public function ledger($account_id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.manage_accounts'))) {
            abort(403, 'Unauthorized action.');
        }

        $account = AccountingAccount::where('business_id', $business_id)
            ->with(['account_sub_type', 'detail_type'])
            ->findorFail($account_id);

        if (request()->ajax()) {
            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');
            $location_id = request()->input('location_id');

            // $before_bal_query = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            //                     ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
            //         ->select([
            //             DB::raw('SUM(IF(accounting_accounts_transactions.type="credit", accounting_accounts_transactions.amount, -1 * accounting_accounts_transactions.amount)) as prev_bal')])
            //         ->where('accounting_accounts_transactions.operation_date', '<', $start_date);
            // $bal_before_start_date = $before_bal_query->first()->prev_bal;

            $transactions = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
                ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
                ->leftjoin('transactions as T', 'accounting_accounts_transactions.transaction_id', '=', 'T.id')
                ->leftjoin('users AS U', 'accounting_accounts_transactions.created_by', 'U.id')
                ->select('accounting_accounts_transactions.operation_date',
                    'accounting_accounts_transactions.sub_type',
                    'accounting_accounts_transactions.type',
                    'ATM.ref_no', 'ATM.note',
                    'accounting_accounts_transactions.note as trans_note',
                    'accounting_accounts_transactions.amount',
                    'accounting_accounts_transactions.acc_trans_mapping_id',
                    'accounting_accounts_transactions.location_id',
                    DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as added_by"),
                    'T.invoice_no'
                );
            if (! empty($start_date) && ! empty($end_date)) {
                $transactions->whereDate('accounting_accounts_transactions.operation_date', '>=', $start_date)
                    ->whereDate('accounting_accounts_transactions.operation_date', '<=', $end_date);
            }

            if (! empty($location_id)) {
                $transactions->join(
                    'business_locations AS bl',
                    'accounting_accounts_transactions.location_id',
                    '=',
                    'bl.id'
                );
                $transactions->where('accounting_accounts_transactions.location_id', $location_id);
            }

            $transactions->orderBy('accounting_accounts_transactions.operation_date', 'asc');

            return DataTables::of($transactions)
                ->editColumn('location_name', function ($row) {
                    $loc = $row->location()->first();

                    return isset($loc->id) ? $loc->name : '';
                })
                ->editColumn('balance', function ($row) {
                    return "<span amount='".$row->amount."' type='".$row->type."' class='movement_balance_row'></span>";
                })
                ->editColumn('operation_date', function ($row) {
                    return $this->accountingUtil->format_date($row->operation_date, true);
                })
                ->editColumn('note', function ($row) {
                    return isset($row->trans_note) && $row->trans_note != '' ? $row->trans_note : $row->note;
                })
                ->editColumn('ref_no', function ($row) {
                    $description = '';

                    if ($row->sub_type == 'journal_entry') {
                        $description = '<b>'.__('accounting::lang.journal_entry').'</b>';
                        $description .= '<br>'.__('purchase.ref_no').': '.$row->ref_no;
                        if (isset($row->accounting_acc_trans_mapping->link_table) && $row->accounting_acc_trans_mapping->link_table == 'transactions') {
                            $transaction = \App\Transaction::where('id', $row->accounting_acc_trans_mapping->link_id)->first();
                            if (isset($transaction->id) && $transaction->type == 'purchase') {
                                $description .= '<br>'.__('accounting::lang.Purchases Invoice').': '.$transaction->ref_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'purchase_return') {
                                $description .= '<br>'.__('lang_v1.purchase_return').': '.$transaction->ref_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'sell') {
                                $description .= '<br>'.__('accounting::lang.Sells Invoice').': '.$transaction->invoice_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'sell_return') {
                                $description .= '<br>'.__('lang_v1.sell_return').': '.$transaction->return_parent_sell->invoice_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'purchase_transfer') {
                                $description .= '<br>'.__('accounting::lang.purchase_transfer').': '.$transaction->ref_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'stock_adjustment' && $transaction->adjustment_type == 'normal') {
                                $description .= '<br>'.__('accounting::lang.stock_adjustment_normal').': '.$transaction->ref_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'stock_adjustment' && $transaction->adjustment_type == 'abnormal') {
                                $description .= '<br>'.__('accounting::lang.stock_adjustment_abnormal').': '.$transaction->ref_no;
                            } elseif (isset($transaction->id) && $transaction->type == 'opening_stock') {
                                $description .= '<br>'.__('accounting::lang.Supply Bonds');
                            }
                        }
                    }
                    if ($row->sub_type == 'expense') {
                        $description = '<b>'.__('expense.add_expense').'</b>';
                        $description .= '<br>'.__('purchase.ref_no').': '.$row->ref_no;
                        if (isset($row->accounting_acc_trans_mapping->link_table) && $row->accounting_acc_trans_mapping->link_table == 'transaction_payments') {
                            $transaction_payment = \App\TransactionPayment::where('id', $row->accounting_acc_trans_mapping->link_id)->first();
                            if ($transaction_payment->transaction->type == 'purchase') {
                                $description .= '<br>'.__('accounting::lang.payment_purchases_invoice').': '.$transaction_payment->transaction->ref_no;
                                $description .= '<br>'.__('accounting::lang.payment').': '.$transaction_payment->payment_ref_no;
                            }

                            if ($transaction_payment->transaction->type == 'sell_return') {
                                $description .= '<br>'.__('accounting::lang.payment_return_sells_invoice').': '.$transaction_payment->transaction->invoice_no;
                                $description .= '<br>'.__('accounting::lang.payment').': '.$transaction_payment->payment_ref_no;
                            }
                        }

                    }

                    if ($row->sub_type == 'receipt') {
                        $description = '<b>'.__('expense.add_receipt').'</b>';
                        $description .= '<br>'.__('purchase.ref_no').': '.$row->ref_no;
                        if (isset($row->accounting_acc_trans_mapping->link_table) && $row->accounting_acc_trans_mapping->link_table == 'transaction_payments') {
                            $transaction_payment = \App\TransactionPayment::where('id', $row->accounting_acc_trans_mapping->link_id)->first();
                            if ($transaction_payment->transaction->type == 'purchase_return') {
                                $description .= '<br>'.__('accounting::lang.payment_return_purchases_invoice').': '.$transaction_payment->transaction->ref_no;
                                $description .= '<br>'.__('accounting::lang.payment').': '.$transaction_payment->payment_ref_no;
                            }

                            if ($transaction_payment->transaction->type == 'sell') {
                                $description .= '<br>'.__('accounting::lang.payment_sells_invoice').': '.$transaction_payment->transaction->invoice_no;
                                $description .= '<br>'.__('accounting::lang.payment').': '.$transaction_payment->payment_ref_no;
                            }
                        }
                    }
                    if ($row->sub_type == 'project_invoice') {
                        $description = '<b>'.__('invoice.project_invoice').'</b>';
                        $description .= '<br>'.__('invoice.invoice_no_prefix').': '.$row->invoice_no;
                        if (isset($row->accounting_acc_trans_mapping->link_table) && $row->accounting_acc_trans_mapping->link_table == 'transaction_payments') {
                            $transaction_payment = \App\TransactionPayment::where('id', $row->accounting_acc_trans_mapping->link_id)->first();
                            if ($transaction_payment->transaction->type == 'sell') {
                                $description .= '<br>'.__('accounting::lang.payment_return_purchases_invoice').': '.$transaction_payment->transaction->ref_no;
                                $description .= '<br>'.__('accounting::lang.payment').': '.$transaction_payment->payment_ref_no;
                            }

                            if ($transaction_payment->transaction->type == 'sellx') {
                                $description .= '<br>'.__('accounting::lang.payment_sells_invoice').': '.$transaction_payment->transaction->invoice_no;
                                $description .= '<br>'.__('accounting::lang.payment').': '.$transaction_payment->payment_ref_no;
                            }
                        }
                    }

                    if ($row->sub_type == 'opening_balance') {
                        $description = '<b>'.__('accounting::lang.opening_balance').'</b>';
                        $description .= '<br>'.__('purchase.ref_no').': '.$row->ref_no;
                    }

                    if ($row->sub_type == 'sell') {
                        $description = '<b>'.__('sale.sale').'</b>';
                        $description .= '<br>'.__('sale.invoice_no').': '.$row->invoice_no;
                    }

                    return $description;
                })
                ->addColumn('debit', function ($row) {
                    if ($row->type == 'debit') {
                        return '<span class="debit" data-orig-value="'.$row->amount.'">'.$this->accountingUtil->num_f($row->amount, true).'</span>';
                    }

                    return '';
                })
                ->addColumn('credit', function ($row) {
                    if ($row->type == 'credit') {
                        return '<span class="credit"  data-orig-value="'.$row->amount.'">'.$this->accountingUtil->num_f($row->amount, true).'</span>';
                    }

                    return '';
                })
                    // ->addColumn('balance', function ($row) use ($bal_before_start_date, $start_date) {
                    //     //TODO:: Need to fix same balance showing for transactions having same operation date
                    //     $current_bal = AccountingAccountsTransaction::where('accounting_account_id',
                    //                         $row->account_id)
                    //                     ->where('operation_date', '>=', $start_date)
                    //                     ->where('operation_date', '<=', $row->operation_date)
                    //                     ->select(DB::raw("SUM(IF(type='credit', amount, -1 * amount)) as balance"))
                    //                     ->first()->balance;
                    //     $bal = $bal_before_start_date + $current_bal;
                    //     return '<span class="balance" data-orig-value="' . $bal . '">' . $this->accountingUtil->num_f($bal, true) . '</span>';
                    // })
                ->editColumn('action', function ($row) {
                    $action = '';

                    return $action;
                })
                ->filterColumn('added_by', function ($query, $keyword): void {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['ref_no', 'credit', 'debit', 'balance', 'action'])
                ->make(true);
        }

        $current_bal = AccountingAccount::leftjoin('accounting_accounts_transactions as AAT',
            'AAT.accounting_account_id', '=', 'accounting_accounts.id')
            ->where('business_id', $business_id)
            ->where('accounting_accounts.id', $account->id)
            ->select([DB::raw($this->accountingUtil->balanceFormula())]);
        $current_bal = $current_bal->first()->balance;

        $business_locations = BusinessLocation::forDropdown($business_id);

        $user = Auth::user();

        $permitted_locations = $user->permitted_locations($business_id);

        $query = BusinessLocation::where('business_id', $business_id);

        if ($permitted_locations != 'all') {
            $query->whereIn('id', $permitted_locations);
        }
        $business_location = $query->Active()->first();

        $invoice_layout = null;
        if (isset($business_location->id)) {
            $invoice_layout = InvoiceLayout::where('id', $business_location->invoice_layout_id)->first();
        }

        return view('accounting::chart_of_accounts.ledger')
            ->with(compact('account', 'current_bal', 'business_locations', 'business_location', 'invoice_layout'));
    }

    public function getDiscountOnPurchases()
    {
        $business_id = request()->session()->get('user.business_id');

        $location_id = request()->input('location_id');
        $account_id = AccountingUtil::getAccountingAccountID(5105, $business_id);

        $account = AccountingAccount::where('business_id', $business_id)
            ->with(['account_sub_type', 'detail_type'])
            ->findorFail($account_id);

        $transactions = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
            ->leftjoin('transactions as T', 'accounting_accounts_transactions.transaction_id', '=', 'T.id')
            ->leftjoin('users AS U', 'accounting_accounts_transactions.created_by', 'U.id')
            ->select('accounting_accounts_transactions.operation_date',
                'accounting_accounts_transactions.sub_type',
                'accounting_accounts_transactions.type',
                'ATM.ref_no', 'ATM.note',
                'accounting_accounts_transactions.amount',
                'accounting_accounts_transactions.acc_trans_mapping_id',
                'accounting_accounts_transactions.location_id',
                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as added_by"),
                'T.invoice_no'
            );

        if (! empty($location_id)) {
            $transactions->join(
                'business_locations AS bl',
                'accounting_accounts_transactions.location_id',
                '=',
                'bl.id'
            );
            $transactions->where('accounting_accounts_transactions.location_id', $location_id);
        }

        $transactions->orderBy('accounting_accounts_transactions.operation_date', 'asc');

        $data = $transactions->get();

        $debit = 0;
        $credit = 0;

        foreach ($data as $row) {
            if ($row->sub_type == 'journal_entry') {

                if ($row->type == 'debit') {
                    $debit += $row->amount;
                } elseif ($row->type == 'credit') {
                    $credit += $row->amount;
                }

            }
        }

        return [
            'debit' => $debit,
            'credit' => $credit,
        ];
    }

    public function getSalesDiscount()
    {
        $business_id = request()->session()->get('user.business_id');

        $location_id = request()->input('location_id');
        $account_id = AccountingUtil::getAccountingAccountID(4102, $business_id);

        $account = AccountingAccount::where('business_id', $business_id)
            ->with(['account_sub_type', 'detail_type'])
            ->findorFail($account_id);

        $transactions = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
            ->leftjoin('transactions as T', 'accounting_accounts_transactions.transaction_id', '=', 'T.id')
            ->leftjoin('users AS U', 'accounting_accounts_transactions.created_by', 'U.id')
            ->select('accounting_accounts_transactions.operation_date',
                'accounting_accounts_transactions.sub_type',
                'accounting_accounts_transactions.type',
                'ATM.ref_no', 'ATM.note',
                'accounting_accounts_transactions.amount',
                'accounting_accounts_transactions.acc_trans_mapping_id',
                'accounting_accounts_transactions.location_id',
                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as added_by"),
                'T.invoice_no'
            );

        if (! empty($location_id)) {
            $transactions->join(
                'business_locations AS bl',
                'accounting_accounts_transactions.location_id',
                '=',
                'bl.id'
            );
            $transactions->where('accounting_accounts_transactions.location_id', $location_id);
        }

        $transactions->orderBy('accounting_accounts_transactions.operation_date', 'asc');

        $data = $transactions->get();

        $debit = 0;
        $credit = 0;

        foreach ($data as $row) {
            if ($row->sub_type == 'journal_entry') {

                if ($row->type == 'debit') {
                    $debit += $row->amount;
                } elseif ($row->type == 'credit') {
                    $credit += $row->amount;
                }

            }
        }

        return [
            'debit' => $debit,
            'credit' => $credit,
        ];
    }

    public function getAllBlanace()
    {
        $business_id = request()->session()->get('user.business_id');

        $location_id = request()->input('location_id');
        $account_id = request()->input('account_id');

        $account = AccountingAccount::where('business_id', $business_id)
            ->with(['account_sub_type', 'detail_type'])
            ->findorFail($account_id);

        $transactions = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
            ->leftjoin('transactions as T', 'accounting_accounts_transactions.transaction_id', '=', 'T.id')
            ->leftjoin('users AS U', 'accounting_accounts_transactions.created_by', 'U.id')
            ->select('accounting_accounts_transactions.operation_date',
                'accounting_accounts_transactions.sub_type',
                'accounting_accounts_transactions.type',
                'ATM.ref_no', 'ATM.note',
                'accounting_accounts_transactions.amount',
                'accounting_accounts_transactions.acc_trans_mapping_id',
                'accounting_accounts_transactions.location_id',
                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as added_by"),
                'T.invoice_no'
            );

        if (! empty($location_id)) {
            $transactions->join(
                'business_locations AS bl',
                'accounting_accounts_transactions.location_id',
                '=',
                'bl.id'
            );
            $transactions->where('accounting_accounts_transactions.location_id', $location_id);
        }

        $transactions->orderBy('accounting_accounts_transactions.operation_date', 'asc');

        $data = $transactions->get();

        $amount = 0;

        foreach ($data as $row) {

            if ($row->type == 'debit') {
                $amount += $row->amount;
            } elseif ($row->type == 'credit') {
                $amount -= $row->amount;
            }

        }

        return $amount;
    }

    public function getOpeningBalance()
    {
        $amount = 0;

        $last_operation_date = '';

        $account_id = request()->account_id;

        $location_id = request()->location_id;

        $operation_date = request()->operation_date;

        $business_id = request()->session()->get('user.business_id');

        $account = AccountingAccount::where('business_id', $business_id)
            ->with(['account_sub_type', 'detail_type'])
            ->findorFail($account_id);

        $transactions = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
            ->leftjoin('transactions as T', 'accounting_accounts_transactions.transaction_id', '=', 'T.id')
            ->leftjoin('users AS U', 'accounting_accounts_transactions.created_by', 'U.id')
            ->select('accounting_accounts_transactions.operation_date',
                'accounting_accounts_transactions.sub_type',
                'accounting_accounts_transactions.type',
                'ATM.ref_no', 'ATM.note',
                'accounting_accounts_transactions.amount',
                'accounting_accounts_transactions.acc_trans_mapping_id',
                'accounting_accounts_transactions.location_id',
                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as added_by"),
                'T.invoice_no'
            );

        $transactions->whereDate('accounting_accounts_transactions.operation_date', '<', $operation_date);

        if (! empty($location_id)) {
            $transactions->join(
                'business_locations AS bl',
                'accounting_accounts_transactions.location_id',
                '=',
                'bl.id'
            );
            $transactions->where('accounting_accounts_transactions.location_id', $location_id);
        }

        $transactions->orderBy('accounting_accounts_transactions.operation_date', 'asc');

        $data = $transactions->get();

        foreach ($data as $row) {
            $last_operation_date = $row->operation_date;

            if ($row->type == 'debit') {
                $amount += $row->amount;
            } elseif ($row->type == 'credit') {
                $amount -= $row->amount;
            }
        }

        return [
            'amount' => $amount,
            'operation_date' => $this->accountingUtil->format_date($last_operation_date, true),
        ];
    }

    public function getClosingBalance()
    {
        $amount = 0;

        $last_operation_date = '';

        $account_id = request()->account_id;

        $location_id = request()->location_id;

        $business_id = request()->session()->get('user.business_id');

        $account = AccountingAccount::where('business_id', $business_id)
            ->with(['account_sub_type', 'detail_type'])
            ->findorFail($account_id);

        $transactions = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            ->leftjoin('accounting_acc_trans_mappings as ATM', 'accounting_accounts_transactions.acc_trans_mapping_id', '=', 'ATM.id')
            ->leftjoin('transactions as T', 'accounting_accounts_transactions.transaction_id', '=', 'T.id')
            ->leftjoin('users AS U', 'accounting_accounts_transactions.created_by', 'U.id')
            ->select('accounting_accounts_transactions.operation_date',
                'accounting_accounts_transactions.sub_type',
                'accounting_accounts_transactions.type',
                'ATM.ref_no', 'ATM.note',
                'accounting_accounts_transactions.amount',
                'accounting_accounts_transactions.acc_trans_mapping_id',
                'accounting_accounts_transactions.location_id',
                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as added_by"),
                'T.invoice_no'
            );

        if (! empty($location_id)) {
            $transactions->join(
                'business_locations AS bl',
                'accounting_accounts_transactions.location_id',
                '=',
                'bl.id'
            );
            $transactions->where('accounting_accounts_transactions.location_id', $location_id);
        }

        $transactions->orderBy('accounting_accounts_transactions.operation_date', 'asc');

        $data = $transactions->get();

        foreach ($data as $row) {

            if ($row->type == 'debit') {
                $amount += $row->amount;
            } elseif ($row->type == 'credit') {
                $amount -= $row->amount;
            }
        }

        return $amount;

    }
}
