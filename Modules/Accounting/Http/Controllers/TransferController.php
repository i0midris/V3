<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Utils\AccountingUtil;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $util;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $util, ModuleUtil $moduleUtil, AccountingUtil $accountingUtil)
    {
        $this->util = $util;
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
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
            ! (auth()->user()->can('accounting.view_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $transfers = AccountingAccTransMapping::where('accounting_acc_trans_mappings.business_id', $business_id)
                ->join('users as u', 'accounting_acc_trans_mappings.created_by', 'u.id')
                ->join('accounting_accounts_transactions as from_transaction', function ($join): void {
                    $join->on('from_transaction.acc_trans_mapping_id', '=', 'accounting_acc_trans_mappings.id')
                        ->where('from_transaction.type', 'debit');
                })
                ->join('accounting_accounts_transactions as to_transaction', function ($join): void {
                    $join->on('to_transaction.acc_trans_mapping_id', '=', 'accounting_acc_trans_mappings.id')
                        ->where('to_transaction.type', 'credit');
                })
                ->join('accounting_accounts as from_account',
                    'from_transaction.accounting_account_id', 'from_account.id')
                ->join('business_locations as location',
                    'from_transaction.location_id', 'location.id')
                ->join('accounting_accounts as to_account',
                    'to_transaction.accounting_account_id', 'to_account.id')
                ->where('accounting_acc_trans_mappings.type', 'transfer')
                ->select(['accounting_acc_trans_mappings.id',
                    'accounting_acc_trans_mappings.ref_no',
                    'accounting_acc_trans_mappings.operation_date',
                    'accounting_acc_trans_mappings.note',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) 
                            as added_by"),
                    'from_transaction.amount',
                    'from_account.name as from_account_name',
                    'to_account.name as to_account_name',
                    'location.name as location_name',
                ]);

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $transfers->whereDate('accounting_acc_trans_mappings.operation_date', '>=', $start)
                    ->whereDate('accounting_acc_trans_mappings.operation_date', '<=', $end);
            }

            if (! empty(request()->transfer_from)) {
                $transfers->where('from_account.id', request()->transfer_from);
            }

            if (! empty(request()->transfer_to)) {
                $transfers->where('to_account.id', request()->transfer_to);
            }

            return Datatables::of($transfers)
                ->addColumn(
                    'action', function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">'.
                                    __('messages.actions').
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                        if (auth()->user()->can('accounting.edit_transfer')) {
                            $html .= '<li>
                                <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\TransferController::class, 'edit'],
                                [$row->id]).'" class="btn-modal" data-container="#create_transfer_modal">
                                    <i class="fas fa-edit"></i>'.__('messages.edit').'
                                </a>
                            </li>';
                        }
                        if (auth()->user()->can('accounting.delete_transfer')) {
                            $html .= '<li>
                                    <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\TransferController::class, 'destroy'], [$row->id]).'" class="delete_transfer_button">
                                        <i class="fas fa-trash" aria-hidden="true"></i>'.__('messages.delete').'
                                    </a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    })
                ->editColumn('amount', function ($row) {
                    return $this->util->num_f($row->amount, true);
                })
                ->editColumn('operation_date', function ($row) {
                    return $this->util->format_date($row->operation_date, true);
                })
                ->filterColumn('added_by', function ($query, $keyword): void {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', 
                    COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::transfer.index');
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
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            return view('accounting::transfer.create');
        }
    }

    public function opening_balance_create1()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $opening_balance_acc = AccountingUtil::getAccountingAccountID('31', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('32', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('33', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('34', $business_id);

            $without_opening_balance_acc = AccountingUtil::getAccountingAccountID('1103', $business_id);
            $without_opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('2101', $business_id);

            $swap = true;

            return view('accounting::opening_balance.create')->with(compact('swap', 'opening_balance_acc', 'without_opening_balance_acc'));
        }
    }

    public function opening_balance_create2()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $opening_balance_acc = AccountingUtil::getAccountingAccountID('31', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('32', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('33', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('34', $business_id);

            $without_opening_balance_acc = AccountingUtil::getAccountingAccountID('1103', $business_id);
            $without_opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('2101', $business_id);

            $swap = false;

            return view('accounting::opening_balance.create')->with(compact('swap', 'opening_balance_acc', 'without_opening_balance_acc'));
        }
    }

    public function expense_create()
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        $cash = AccountingUtil::getAccountingAccountID('1101', $business_id);

        $bank = AccountingUtil::getAccountingAccountID('1102', $business_id);

        return view('accounting::expense.create')->with(compact('cash', 'bank'));

    }

    public function receipt_create()
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        $cash = AccountingUtil::getAccountingAccountID('1101', $business_id);

        $bank = AccountingUtil::getAccountingAccountID('1102', $business_id);

        return view('accounting::receipt.create')->with(compact('cash', 'bank'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $from_account = $request->get('from_account');
            $to_account = $request->get('to_account');
            $location_id = $request->get('location_id');
            $amount = $request->get('amount');
            $date = $this->util->uf_date($request->get('operation_date'), true);

            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);

            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('accounting_transfer');
            if (empty($ref_no)) {
                $prefix = ! empty($accounting_settings['transfer_prefix']) ?
                $accounting_settings['transfer_prefix'] : '';

                // Generate reference number
                $ref_no = $this->util->generateReferenceNumber('accounting_transfer', $ref_count, $business_id, $prefix);
            }

            $acc_trans_mapping = new AccountingAccTransMapping;
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = 'transfer';
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $date;
            $acc_trans_mapping->save();

            $from_transaction_data = [
                'acc_trans_mapping_id' => $acc_trans_mapping->id,
                'amount' => $this->util->num_uf($amount),
                'type' => 'debit',
                'sub_type' => 'transfer',
                'accounting_account_id' => $from_account,
                'location_id' => $location_id,
                'created_by' => $user_id,
                'operation_date' => $date,
            ];

            $to_transaction_data = $from_transaction_data;
            $to_transaction_data['accounting_account_id'] = $to_account;
            $to_transaction_data['location_id'] = $location_id;
            $to_transaction_data['type'] = 'credit';

            AccountingAccountsTransaction::create($from_transaction_data);
            AccountingAccountsTransaction::create($to_transaction_data);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function opening_balance_store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $from_account = $request->get('from_account');
            $to_account = $request->get('to_account');
            $location_id = $request->get('location_id');
            $amount = $request->get('amount');
            $date = $this->util->uf_date($request->get('operation_date'), true);
            $note = $request->get('note');

            AccountingUtil::create_update_opening_balance_journal_entry($to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function expense_store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $from_account = $request->get('from_account');
            $to_account = $request->get('to_account');
            $to_account2 = $request->get('to_account2');
            $location_id = $request->get('location_id');
            $amount = $request->get('amount');
            $amount2 = $request->get('amount2');
            $date = $this->util->uf_date($request->get('operation_date'), true);

            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);

            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('accounting_expense');
            if (empty($ref_no)) {
                $prefix = ! empty($accounting_settings['expense_prefix']) ?
                    $accounting_settings['expense_prefix'] : '';

                // Generate reference number
                $ref_no = $this->util->generateReferenceNumber('accounting_expense', $ref_count, $business_id, $prefix);
            }

            $acc_trans_mapping = new AccountingAccTransMapping;
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = 'expense';
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $date;
            $acc_trans_mapping->save();

            $from_transaction_data = [
                'acc_trans_mapping_id' => $acc_trans_mapping->id,
                'amount' => $this->util->num_uf($amount2 != '' ? $amount2 + $amount : $amount),
                'type' => 'debit',
                'sub_type' => 'expense',
                'accounting_account_id' => $from_account,
                'location_id' => $location_id,
                'created_by' => $user_id,
                'operation_date' => $date,
            ];

            $to_transaction_data = $from_transaction_data;
            $to_transaction_data['accounting_account_id'] = $to_account;
            $to_transaction_data['location_id'] = $location_id;
            $to_transaction_data['amount'] = $this->util->num_uf($amount);
            $to_transaction_data['type'] = 'credit';

            AccountingAccountsTransaction::create($to_transaction_data);

            if ($amount2 != '') {
                $to_transaction_data2 = $to_transaction_data;
                $to_transaction_data2['accounting_account_id'] = $to_account2;
                $to_transaction_data2['amount'] = $this->util->num_uf($amount2);

                AccountingAccountsTransaction::create($to_transaction_data2);
            }

            AccountingAccountsTransaction::create($from_transaction_data);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $acc_trans_mapping = AccountingAccTransMapping::where('id', $acc_trans_mapping->id)
            ->where('business_id', $business_id)->firstOrFail();

        foreach ($acc_trans_mapping->childs() as $one) {
            $one->type = $one->type == 'credit' ? 'debit' : 'credit';

            $one->save();
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    public function receipt_store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.add_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $from_account = $request->get('from_account');
            $to_account = $request->get('to_account');
            $location_id = $request->get('location_id');
            $amount = $request->get('amount');
            $date = $this->util->uf_date($request->get('operation_date'), true);

            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);

            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('accounting_receipt');
            if (empty($ref_no)) {
                $prefix = ! empty($accounting_settings['receipt_prefix']) ?
                    $accounting_settings['receipt_prefix'] : '';

                // Generate reference number
                $ref_no = $this->util->generateReferenceNumber('accounting_receipt', $ref_count, $business_id, $prefix);
            }

            $acc_trans_mapping = new AccountingAccTransMapping;
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = 'receipt';
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $date;
            $acc_trans_mapping->save();

            $from_transaction_data = [
                'acc_trans_mapping_id' => $acc_trans_mapping->id,
                'amount' => $this->util->num_uf($amount),
                'type' => 'debit',
                'sub_type' => 'receipt',
                'accounting_account_id' => $from_account,
                'location_id' => $location_id,
                'created_by' => $user_id,
                'operation_date' => $date,
            ];

            $to_transaction_data = $from_transaction_data;
            $to_transaction_data['accounting_account_id'] = $to_account;
            $to_transaction_data['location_id'] = $location_id;
            $to_transaction_data['type'] = 'credit';

            AccountingAccountsTransaction::create($from_transaction_data);
            AccountingAccountsTransaction::create($to_transaction_data);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $acc_trans_mapping = AccountingAccTransMapping::where('id', $acc_trans_mapping->id)
            ->where('business_id', $business_id)->firstOrFail();

        foreach ($acc_trans_mapping->childs() as $one) {
            $one->type = $one->type == 'credit' ? 'debit' : 'credit';

            $one->save();
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return view('accounting::show');
    }

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
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $mapping_transaction = AccountingAccTransMapping::where('id', $id)
                ->where('business_id', $business_id)->firstOrFail();

            $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'debit')
                ->first();
            $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'credit')
                ->first();

            $locations_array = BusinessLocation::forDropdown($business_id, false);

            $accounts_array = AccountingAccount::forDropdown($business_id, false);

            return view('accounting::transfer.edit')->with(compact('mapping_transaction',
                'debit_tansaction', 'credit_tansaction',
                'locations_array', 'accounts_array'));
        }
    }

    public function opening_balance_edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $mapping_transaction = AccountingAccTransMapping::where('id', $id)
                ->where('business_id', $business_id)->firstOrFail();

            $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'debit')
                ->first();
            $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'credit')
                ->first();

            $locations_array = BusinessLocation::forDropdown($business_id, false);

            $opening_balance_acc = AccountingUtil::getAccountingAccountID('31', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('32', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('33', $business_id);
            $opening_balance_acc .= ','.AccountingUtil::getAccountingAccountID('34', $business_id);

            return view('accounting::opening_balance.edit')->with(compact('mapping_transaction',
                'debit_tansaction', 'credit_tansaction', 'opening_balance_acc',
                'locations_array'));
        }
    }

    public function expense_edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        $mapping_transaction = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->firstOrFail();

        $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'credit')
            ->first();
        $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'debit')
            ->first();
        $credit_tansaction2 = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'debit')
            ->skip(1)
            ->first();

        $locations_array = BusinessLocation::forDropdown($business_id, false);

        $cash = AccountingUtil::getAccountingAccountID('1101', $business_id);

        $bank = AccountingUtil::getAccountingAccountID('1102', $business_id);

        return view('accounting::expense.edit')->with(compact('mapping_transaction',
            'debit_tansaction', 'credit_tansaction', 'credit_tansaction2',
            'locations_array', 'cash', 'bank'));

    }

    public function receipt_edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        $mapping_transaction = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->firstOrFail();

        $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'credit')
            ->first();
        $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
            ->where('type', 'debit')
            ->first();

        $locations_array = BusinessLocation::forDropdown($business_id, false);

        $cash = AccountingUtil::getAccountingAccountID('1101', $business_id);

        $bank = AccountingUtil::getAccountingAccountID('1102', $business_id);

        return view('accounting::receipt.edit')->with(compact('mapping_transaction',
            'debit_tansaction', 'credit_tansaction',
            'locations_array', 'cash', 'bank'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $mapping_transaction = AccountingAccTransMapping::where('id', $id)
                ->where('business_id', $business_id)->firstOrFail();

            $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'debit')
                ->first();
            $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'credit')
                ->first();

            DB::beginTransaction();
            $from_account = $request->get('from_account');
            $location_id = $request->get('location_id');
            $to_account = $request->get('to_account');
            $amount = $request->get('amount');
            $date = $this->util->uf_date($request->get('operation_date'), true);

            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('accounting_transfer');
            if (empty($ref_no)) {
                // Generate reference number
                $ref_no = $this->util->generateReferenceNumber('accounting_transfer', $ref_count);
            }

            $mapping_transaction->ref_no = $ref_no;
            $mapping_transaction->note = $request->get('note');
            $mapping_transaction->operation_date = $date;
            $mapping_transaction->save();

            $debit_tansaction->accounting_account_id = $from_account;
            $debit_tansaction->location_id = $location_id;
            $debit_tansaction->operation_date = $date;
            $debit_tansaction->amount = $this->util->num_uf($amount);
            $debit_tansaction->save();

            $credit_tansaction->accounting_account_id = $to_account;
            $credit_tansaction->location_id = $location_id;
            $credit_tansaction->operation_date = $date;
            $credit_tansaction->amount = $this->util->num_uf($amount);
            $credit_tansaction->save();

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function opening_balance_update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {

            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $from_account = $request->get('from_account');
            $location_id = $request->get('location_id');
            $to_account = $request->get('to_account');
            $amount = $request->get('amount');
            $date = $this->util->uf_date($request->get('operation_date'), true);
            $note = $request->get('note');

            AccountingUtil::update_opening_balance_journal_entry($id, $to_account, $from_account, $amount, $business_id, $location_id, $user_id, $date, $note);

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function expense_update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $mapping_transaction = AccountingAccTransMapping::where('id', $id)
                ->where('business_id', $business_id)->firstOrFail();

            $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'credit')
                ->first();
            $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'debit')
                ->first();
            $credit_tansaction2 = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'debit')
                ->skip(1)
                ->first();

            DB::beginTransaction();
            $from_account = $request->get('from_account');
            $location_id = $request->get('location_id');
            $to_account = $request->get('to_account');
            $to_account2 = $request->get('to_account2');
            $amount = $request->get('amount');
            $amount2 = $request->get('amount2');
            $date = $this->util->uf_date($request->get('operation_date'), true);

            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('accounting_transfer');
            if (empty($ref_no)) {
                // Generate reference number
                $ref_no = $this->util->generateReferenceNumber('accounting_transfer', $ref_count);
            }

            $mapping_transaction->ref_no = $ref_no;
            $mapping_transaction->note = $request->get('note');
            $mapping_transaction->operation_date = $date;
            $mapping_transaction->save();

            $credit_tansaction->accounting_account_id = $to_account;
            $credit_tansaction->location_id = $location_id;
            $credit_tansaction->operation_date = $date;
            $credit_tansaction->amount = $this->util->num_uf($amount);
            $credit_tansaction->save();

            if ($amount2 != '') {
                if (isset($credit_tansaction2->id)) {
                    $credit_tansaction2->accounting_account_id = $to_account2;
                    $credit_tansaction2->location_id = $location_id;
                    $credit_tansaction2->operation_date = $date;
                    $credit_tansaction2->amount = $this->util->num_uf($amount2);
                    $credit_tansaction2->save();
                } else {
                    $to_transaction_data = [
                        'acc_trans_mapping_id' => $credit_tansaction->acc_trans_mapping_id,
                        'amount' => $this->util->num_uf($amount2),
                        'type' => 'debit',
                        'sub_type' => 'expense',
                        'accounting_account_id' => $to_account2,
                        'location_id' => $location_id,
                        'created_by' => $credit_tansaction->created_by,
                        'operation_date' => $date,
                    ];

                    AccountingAccountsTransaction::create($to_transaction_data);
                }
            } else {
                if (isset($credit_tansaction2->id)) {
                    $credit_tansaction2->delete();
                }

            }

            $debit_tansaction->accounting_account_id = $from_account;
            $debit_tansaction->location_id = $location_id;
            $debit_tansaction->operation_date = $date;
            $debit_tansaction->amount = $this->util->num_uf($amount2 != '' ? $amount2 + $amount : $amount);
            $debit_tansaction->save();

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    public function receipt_update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.edit_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $mapping_transaction = AccountingAccTransMapping::where('id', $id)
                ->where('business_id', $business_id)->firstOrFail();

            $debit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'credit')
                ->first();
            $credit_tansaction = AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)
                ->where('type', 'debit')
                ->first();

            DB::beginTransaction();
            $from_account = $request->get('from_account');
            $location_id = $request->get('location_id');
            $to_account = $request->get('to_account');
            $amount = $request->get('amount');
            $date = $this->util->uf_date($request->get('operation_date'), true);

            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('accounting_transfer');
            if (empty($ref_no)) {
                // Generate reference number
                $ref_no = $this->util->generateReferenceNumber('accounting_transfer', $ref_count);
            }

            $mapping_transaction->ref_no = $ref_no;
            $mapping_transaction->note = $request->get('note');
            $mapping_transaction->operation_date = $date;
            $mapping_transaction->save();

            $debit_tansaction->accounting_account_id = $from_account;
            $debit_tansaction->location_id = $location_id;
            $debit_tansaction->operation_date = $date;
            $debit_tansaction->amount = $this->util->num_uf($amount);
            $debit_tansaction->save();

            $credit_tansaction->accounting_account_id = $to_account;
            $credit_tansaction->location_id = $location_id;
            $credit_tansaction->operation_date = $date;
            $credit_tansaction->amount = $this->util->num_uf($amount);
            $credit_tansaction->save();

            DB::commit();

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('journal-entry.index')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.delete_transfer'))) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');

        $acc_trans_mapping = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->firstOrFail();

        if (! empty($acc_trans_mapping)) {
            $acc_trans_mapping->delete();
            AccountingAccountsTransaction::where('acc_trans_mapping_id', $id)->delete();
        }

        return ['success' => 1,
            'msg' => __('lang_v1.deleted_success'),
        ];
    }
}
