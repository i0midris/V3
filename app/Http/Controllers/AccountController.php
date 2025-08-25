<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\AccountType;
use App\BusinessLocation;
use App\Media;
use App\TransactionPayment;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    protected $commonUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        if (request()->ajax()) {
            $accounts = Account::leftjoin('accounting_accounts as AA', function ($join): void {
                $join->on('AA.link_id', '=', 'accounts.id')
                    ->where('AA.link_table', 'accounts');
            })
                ->leftjoin('accounting_accounts_transactions as AAT', function ($join): void {
                    $join->on('AAT.accounting_account_id', '=', 'AA.id');
                })
                ->leftjoin('account_types as ats', 'accounts.account_type_id', '=', 'ats.id')
                ->leftjoin('account_types as pat', 'ats.parent_account_type_id', '=', 'pat.id')
                ->leftJoin('users AS u', 'accounts.created_by', '=', 'u.id')
                ->where('accounts.business_id', $business_id)
                ->select([
                    'accounts.name',
                    'accounts.account_number',
                    'accounts.note',
                    'accounts.id',
                    'accounts.account_type_id',
                    'ats.name as account_type_name',
                    'pat.name as parent_account_type_name',
                    'accounts.account_details',
                    'is_closed',
                    // ✅ Correct balance calculation using accounting_accounts_transactions
                    DB::raw("COALESCE(SUM(
                        CASE 
                            WHEN AAT.type = 'debit' THEN AAT.amount 
                            WHEN AAT.type = 'credit' THEN -AAT.amount 
                            ELSE 0 
                        END
                    ), 0) AS balance"),
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                ])
                ->groupBy('accounts.id');

            // ✅ Check account permissions based on location
            $permitted_locations = auth()->user()->permitted_locations();
            $account_ids = [];

            if ($permitted_locations != 'all') {
                $locations = BusinessLocation::where('business_id', $business_id)
                    ->whereIn('id', $permitted_locations)
                    ->get();

                foreach ($locations as $location) {
                    if (! empty($location->default_payment_accounts)) {
                        $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                        foreach ($default_payment_accounts as $account) {
                            if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                                $account_ids[] = $account['account'];
                            }
                        }
                    }
                }

                $account_ids = array_unique($account_ids);
            }

            if (! $this->moduleUtil->is_admin(auth()->user(), $business_id) && $permitted_locations != 'all') {
                $accounts->whereIn('accounts.id', $account_ids);
            }

            // ✅ Filter based on open or closed accounts
            $is_closed = request()->input('account_status') == 'closed' ? 1 : 0;
            $accounts->where('is_closed', $is_closed)->groupBy('accounts.id');

            return DataTables::of($accounts)
                ->addColumn(
                    'action', 
                    '@if((new App\Utils\ModuleUtil)->getModuleData("MKamel_checkTreeAccountingDefined") == true)
                        <button data-href="{{action(\'App\Http\Controllers\AccountController@edit\',[$id])}}" 
                            data-container=".account_model" 
                            class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal">
                            <i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")
                        </button>
                    @endif
                    <a href="{{action(\'App\Http\Controllers\AccountController@show\',[$id])}}" 
                        class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-warning btn-xs">
                        <i class="fa fa-book"></i> @lang("account.account_book")
                    </a>&nbsp;
                    @if($is_closed == 0)
                    <button data-href="{{action(\'App\Http\Controllers\AccountController@getFundTransfer\',[$id])}}" 
                        class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info btn-modal" 
                        data-container=".view_modal">
                        <i class="fa fa-exchange"></i> @lang("account.fund_transfer")
                    </button>
                    
                    {{-- 
                   <button data-href="{{action(\'App\Http\Controllers\AccountController@getDeposit\',[$id])}}" 
                        class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success btn-modal" 
                        data-container=".view_modal">
                        <i class="fas fa-money-bill-alt"></i> @lang("account.deposit")
                    </button>
                    --}}

                    <button data-url="{{action(\'App\Http\Controllers\AccountController@close\',[$id])}}" 
                        class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error close_account">
                        <i class="fa fa-power-off"></i> @lang("messages.close")
                    </button>
                    @elseif($is_closed == 1)
                        <button data-url="{{action(\'App\Http\Controllers\AccountController@activate\',[$id])}}" 
                            class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success activate_account">
                            <i class="fa fa-power-off"></i> @lang("messages.activate")
                        </button>
                    @endif'
                )
                ->editColumn('balance', function ($row) {
                    return '<span class="balance" data-orig-value="'.$row->balance.'">'.$this->commonUtil->num_f($row->balance, true).'</span>';
                })
                ->editColumn('account_details', function ($row) {
                    $html = '';

                    // ✅ Ensure account_details is a string before decoding
                    if (! empty($row->account_details)) {
                        $details = is_array($row->account_details) ? $row->account_details : json_decode($row->account_details, true);

                        if (is_array($details)) { // ✅ Ensure it's a valid array
                            foreach ($details as $detail) {
                                if (! empty($detail['label']) && ! empty($detail['value'])) {
                                    $html .= '<strong>'.e($detail['label']).'</strong>: '.e($detail['value']).'<br>';
                                }
                            }
                        }
                    }

                    return $html;

                })
                ->rawColumns(['action', 'balance', 'name', 'account_details'])
                ->make(true);
        }

        // ✅ Fetch not linked payments
        $not_linked_payments = TransactionPayment::leftjoin(
            'transactions as T',
            'transaction_payments.transaction_id',
            '=',
            'T.id'
        )
            ->whereNull('transaction_payments.parent_id')
            ->where('method', '!=', 'advance')
            ->where('transaction_payments.business_id', $business_id)
            ->whereNull('account_id')
            ->count();

        // ✅ Fetch account types
        $account_types = AccountType::where('business_id', $business_id)
            ->whereNull('parent_account_type_id')
            ->with(['sub_types'])
            ->get();

        return view('account.index')
            ->with(compact('not_linked_payments', 'account_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        $account_types = AccountType::where('business_id', $business_id)
            ->whereNull('parent_account_type_id')
            ->with(['sub_types'])
            ->get();

        return view('account.create')
            ->with(compact('account_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'account_number', 'note', 'account_type_id', 'account_details']);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $input['business_id'] = $business_id;
                $input['created_by'] = $user_id;

                $account = Account::create($input);

                (new \App\Utils\ModuleUtil)->getModuleData('MKamel_store999', ['request' => $request, 'account' => $account]);

                // Opening Balance
                $opening_bal = $request->input('opening_balance');

                if (! empty($opening_bal)) {
                    $ob_transaction_data = [
                        'amount' => $this->commonUtil->num_uf($opening_bal),
                        'account_id' => $account->id,
                        'type' => 'credit',
                        'sub_type' => 'opening_balance',
                        'operation_date' => \Carbon::now(),
                        'created_by' => $user_id,
                    ];

                    AccountTransaction::createAccountTransaction($ob_transaction_data);
                }

                $output = ['success' => true,
                    'msg' => __('account.account_created_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show($id)
{
    if (!auth()->user()->can('account.access')) {
        abort(403, 'Unauthorized action.');
    }

    $business_id = request()->session()->get('user.business_id');

    // Try by link_id, fallback to id
    $account = AccountingAccount::where('business_id', $business_id)
        ->where('link_table', 'accounts')
        ->where('link_id', $id)
        ->first() ??
        AccountingAccount::where('business_id', $business_id)
            ->where('id', $id)
            ->first();

    if (!$account) {
        return response()->json(['error' => 'Account not found for ID: ' . $id], 404);
    }

    $accounting_account_id = $account->id;

    if (request()->ajax()) {
        // Subquery to get the first opposite-side account per acc_trans_mapping_id
        $oppositeSub = DB::table('accounting_accounts_transactions as aat')
            ->select(
                'aat.acc_trans_mapping_id',
                'aa.name as payee_name',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY aat.acc_trans_mapping_id ORDER BY aat.id ASC) as rn')
            )
            ->join('accounting_accounts as aa', 'aat.accounting_account_id', '=', 'aa.id')
            ->where('aat.accounting_account_id', '!=', $accounting_account_id);

        // Main transaction query
        $transactions = DB::table('accounting_accounts_transactions as at')
            ->select([
                'at.acc_trans_mapping_id',
                'at.operation_date',
                'at.sub_type',
                'at.type',
                'at.amount',
                'at.currency_code',
                'at.exchange_rate',
                'at.base_amount',
                'at.note as trans_note',
                'atm.ref_no',
                'atm.note',
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                't.invoice_no',
                'opposite.payee_name',
            ])
            ->join('accounting_acc_trans_mappings as atm', 'at.acc_trans_mapping_id', '=', 'atm.id')
            ->leftJoin('transactions as t', 'at.transaction_id', '=', 't.id')
            ->leftJoin('users as u', 'at.created_by', '=', 'u.id')
            ->joinSub($oppositeSub, 'opposite', function ($join) {
                $join->on('at.acc_trans_mapping_id', '=', 'opposite.acc_trans_mapping_id')
                     ->where('opposite.rn', '=', 1);
            })
            ->where('at.accounting_account_id', $accounting_account_id)
            ->orderBy('at.operation_date', 'asc');

        // Apply filters
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $start_date = Carbon::parse(request('start_date'))->startOfDay();
            $end_date = Carbon::parse(request('end_date'))->endOfDay();
            $transactions->whereBetween('at.operation_date', [$start_date, $end_date]);
        }

        if (request()->filled('transaction_type') && in_array(request('transaction_type'), ['debit', 'credit'])) {
            $transactions->where('at.type', request('transaction_type'));
        }

        if (request()->filled('currency_code')) {
            if (request()->currency_code === '__NULL__') {
                $transactions->whereNull('at.currency_code');
            } else {
                $transactions->where('at.currency_code', request('currency_code'));
            }
        }

        $data = $transactions->get();

        // Running balance calculation
        $running_balance = 0;
        foreach ($data as $transaction) {
            $running_balance += ($transaction->type === 'debit') ? $transaction->amount : -$transaction->amount;
            $transaction->balance = $running_balance;
        }

        $total_debit = $data->where('type', 'debit')->sum('amount');
        $total_credit = $data->where('type', 'credit')->sum('amount');
        $total_balance = $total_debit - $total_credit;

        return DataTables::of($data)
            ->editColumn('type', fn($row) => trans("accounting::lang.{$row->type}") ?? $row->type)
            ->editColumn('sub_type', fn($row) => trans("accounting::lang.{$row->sub_type}") ?? $row->sub_type)
            ->editColumn('payee_name', function ($row) {
                // Translation map from lang file
                $translations = trans('accounting::lang');
            
                $translatedName = $translations[$row->payee_name] ?? $row->payee_name;
            
                if ($row->type === 'debit') {
                    return 'استلمت من: ' . $translatedName;
                } elseif ($row->type === 'credit') {
                    return 'دفعت الى: ' . $translatedName;
                }
            
                return $translatedName;
            })
            
            ->editColumn('balance', fn($row) => "<span amount='{$row->balance}' class='movement_balance_row'>{$this->commonUtil->num_f($row->balance, true)}</span>")
            ->editColumn('operation_date', fn($row) => $this->commonUtil->format_date($row->operation_date, true))
            ->editColumn('note', fn($row) => $row->trans_note ?: $row->note)
            ->addColumn('debit', fn($row) => $row->type === 'debit' ? "<span class='debit' data-orig-value='{$row->amount}'>{$this->commonUtil->num_f($row->amount, true)}</span>" : '')
            ->addColumn('credit', fn($row) => $row->type === 'credit' ? "<span class='credit' data-orig-value='{$row->amount}'>{$this->commonUtil->num_f($row->amount, true)}</span>" : '')
            ->with([
                'total_debit' => $total_debit,
                'total_credit' => $total_credit,
                'total_balance' => $total_balance,
            ])
            ->rawColumns(['debit', 'credit', 'balance'])
            ->make(true);
    }

    return view('account.show', compact('account'));
}

    

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $account = Account::where('business_id', $business_id)
                ->find($id);

            $account_types = AccountType::where('business_id', $business_id)
                ->whereNull('parent_account_type_id')
                ->with(['sub_types'])
                ->get();

            return view('account.edit')
                ->with(compact('account', 'account_types'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'account_number', 'note', 'account_type_id', 'account_details']);

                $business_id = request()->session()->get('user.business_id');
                $account = Account::where('business_id', $business_id)
                    ->findOrFail($id);
                $account->name = $input['name'];
                $account->account_number = $input['account_number'];
                $account->note = $input['note'];
                $account->account_type_id = $input['account_type_id'];
                $account->account_details = $input['account_details'];
                $account->save();

                $accounting_account = \Modules\Accounting\Entities\AccountingAccount::where('business_id', $business_id)
    ->where('link_table', 'accounts')
    ->where('link_id', $account->id)
    ->first();

if ($accounting_account) {
    $accounting_account->name = $account->name;
    $accounting_account->save();
}

                $output = ['success' => true,
                    'msg' => __('account.account_updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroyAccountTransaction($id)
    {
        if (! auth()->user()->can('delete_account_transaction')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $account_transaction = AccountTransaction::findOrFail($id);

                if (in_array($account_transaction->sub_type, ['fund_transfer', 'deposit'])) {
                    // Delete transfer transaction for fund transfer
                    if (! empty($account_transaction->transfer_transaction_id)) {
                        $transfer_transaction = AccountTransaction::findOrFail($account_transaction->transfer_transaction_id);
                        $transfer_transaction->delete();
                    }
                    $account_transaction->delete();
                }

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Closes the specified account.
     *
     * @return Response
     */
    public function close($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');

                $account = Account::where('business_id', $business_id)
                    ->findOrFail($id);
                $account->is_closed = 1;
                $account->save();

                $output = ['success' => true,
                    'msg' => __('account.account_closed_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Shows form to transfer fund.
     *
     * @param  int  $id
     * @return Response
     */
    public function getFundTransfer($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');

            $from_account = Account::where('business_id', $business_id)
                ->NotClosed()
                ->find($id);

            $to_accounts = Account::where('business_id', $business_id)
                ->NotClosed()
                ->pluck('name', 'id');

            return view('account.transfer')
                ->with(compact('from_account', 'to_accounts'));
        }
    }

    /**
     * Transfers fund from one account to another.
     *
     * @return Response
     */
    public function postFundTransfer(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }
    
        DB::beginTransaction();
    
        try {
            $business_id = session()->get('user.business_id');
            $user_id = session()->get('user.id');
    
            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $operation_date = $this->commonUtil->uf_date($request->input('operation_date'), true);
            $note = $request->input('note');
            $from_account_id = $request->input('from_account');
            $to_account_id = $request->input('to_account');
    
            // Fetch accounting accounts linked to the selected accounts
            $from_account = AccountingAccount::where('business_id', $business_id)
                ->where('link_id', $from_account_id)
                ->where('link_table', 'accounts')
                ->firstOrFail();
    
            $to_account = AccountingAccount::where('business_id', $business_id)
                ->where('link_id', $to_account_id)
                ->where('link_table', 'accounts')
                ->firstOrFail();
    
            if ($from_account->id === $to_account->id) {
                throw new \Exception(__('account.transfer_same_account_error'));
            }
    
            if (!empty($amount)) {
                // ✅ Generate per-business serial ref_no BEFORE insert
                $latest_ref = DB::table('accounting_acc_trans_mappings')
                    ->where('business_id', $business_id)
                    ->where('type', 'fund_transfer')
                    ->where('ref_no', 'LIKE', 'FT-%')
                    ->orderBy('id', 'desc')
                    ->value('ref_no');
    
                if (preg_match('/FT-(\d+)/', $latest_ref, $matches)) {
                    $next_serial = (int)$matches[1] + 1;
                } else {
                    $next_serial = 1;
                }
    
                $ref_no = 'FT-' . str_pad($next_serial, 6, '0', STR_PAD_LEFT);
    
                // ✅ Create a transaction mapping entry
                $mapping_id = DB::table('accounting_acc_trans_mappings')->insertGetId([
                    'business_id'    => $business_id,
                    'ref_no'         => $ref_no,
                    'type'           => 'fund_transfer',
                    'operation_date' => $operation_date,
                    'note'           => $note,
                    'created_by'     => $user_id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
    
                // ✅ Credit from the source account
                $credit_data = [
                    'accounting_account_id' => $from_account->id,
                    'operation_date'        => $operation_date,
                    'type'                  => 'credit',
                    'sub_type'              => 'fund_transfer',
                    'amount'                => $amount,
                    'created_by'            => $user_id,
                    'note'                  => $note,
                    'acc_trans_mapping_id'  => $mapping_id,
                ];
                $credit_transaction = AccountingAccountsTransaction::create($credit_data);
    
                // ✅ Debit to the destination account
                $debit_data = [
                    'accounting_account_id' => $to_account->id,
                    'operation_date'        => $operation_date,
                    'type'                  => 'debit',
                    'sub_type'              => 'fund_transfer',
                    'amount'                => $amount,
                    'created_by'            => $user_id,
                    'note'                  => $note,
                    'acc_trans_mapping_id'  => $mapping_id,
                ];
                $debit_transaction = AccountingAccountsTransaction::create($debit_data);
    
                // ✅ Optional document upload
                if ($request->hasFile('document')) {
                    Media::uploadMedia($business_id, $credit_transaction, $request, 'document');
                }
            }
    
            DB::commit();
    
            return redirect()
                ->action([AccountController::class, 'index'])
                ->with('status', [
                    'success' => true,
                    'msg' => __('account.fund_transfered_success'),
                ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            \Log::error("Fund Transfer Error: {$e->getMessage()} in {$e->getFile()} at line {$e->getLine()}");
    
            return redirect()
                ->action([AccountController::class, 'index'])
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
    }
    

    /**
     * Shows deposit form.
     *
     * @param  int  $id
     * @return Response
     */
    public function getDeposit($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');

            $account = Account::where('business_id', $business_id)
                ->NotClosed()
                ->find($id);

            $from_accounts = Account::where('business_id', $business_id)
                ->NotClosed()
                ->pluck('name', 'id');

            return view('account.deposit')
                ->with(compact('account', 'account', 'from_accounts'));
        }
    }

    /**
     * Deposits amount.
     *
     * @return json
     */
    public function postDeposit(Request $request)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction(); // Start transaction for safety

        try {
            $business_id = session()->get('user.business_id');

            // Convert amount to float
            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $account_id = $request->input('account_id'); // This is `link_id` from `accounts`
            $from_account = $request->input('from_account'); // If transferring from another account
            $note = $request->input('note');
            $operation_date = $this->commonUtil->uf_date($request->input('operation_date'), true);

            // ✅ Step 1: Fetch Accounting Account Using `link_id` But Only If `link_table = 'accounts'`
            $account = AccountingAccount::where('business_id', $business_id)
                ->where('link_id', $account_id) // ✅ Fetch using `link_id`
                ->where('link_table', 'accounts') // ✅ Ensure link_table is 'accounts'
                ->firstOrFail();

            $accounting_account_id = $account->id; // ✅ Store ID, NOT link_id

            if (! empty($amount)) {
                // ✅ Step 2: Create a Credit Transaction (Deposit)
                $credit_data = [
                    'accounting_account_id' => $accounting_account_id, // ✅ Store ID, NOT link_id
                    'operation_date' => $operation_date,
                    'type' => 'credit',
                    'sub_type' => 'deposit',
                    'amount' => $amount,
                    'created_by' => session()->get('user.id'),
                    'note' => $note,
                ];
                $credit_transaction = AccountingAccountsTransaction::create($credit_data);

                // ✅ Step 3: If transferring from another account, fetch its `id` using `link_id` & ensure `link_table = 'accounts'`
                if (! empty($from_account)) {
                    // Find the `AccountingAccount` for `from_account` using `link_id`
                    $fromAccountingAccount = AccountingAccount::where('business_id', $business_id)
                        ->where('link_id', $from_account) // ✅ Fetch using `link_id`
                        ->where('link_table', 'accounts') // ✅ Ensure link_table is 'accounts'
                        ->firstOrFail();

                    $debit_data = $credit_data;
                    $debit_data['type'] = 'debit';
                    $debit_data['accounting_account_id'] = $fromAccountingAccount->id; // ✅ Store ID, NOT link_id
                    $debit_data['transfer_transaction_id'] = $credit_transaction->id;

                    $debit_transaction = AccountingAccountsTransaction::create($debit_data);

                    // ✅ Step 4: Link Transactions
                    $credit_transaction->transfer_transaction_id = $debit_transaction->id;
                    $credit_transaction->save();
                }
            }

            DB::commit(); // Commit transaction if successful

            return response()->json([
                'success' => true,
                'msg' => __('account.deposited_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if an error occurs
            \Log::error("Deposit Error: {$e->getMessage()} in {$e->getFile()} at line {$e->getLine()}");

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ], 500);
        }
    }

    /**
     * Calculates account current balance.
     *
     * @param  int  $id
     * @return json
     */
    public function getAccountBalance($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        // ✅ Ensure correct grouping for OR conditions
        $account = AccountingAccount::where('business_id', $business_id)
            ->where(function ($query) use ($id): void {
                $query->where(function ($q) use ($id): void {
                    $q->where('link_id', $id)->where('link_table', 'accounts');
                })->orWhere('id', $id);
            })
            ->first();

        if (! $account) {
            return response()->json(['error' => __('messages.account_not_found')], 404);
        }

        // ✅ Optimized balance calculation
        $balance = AccountingAccountsTransaction::where('accounting_account_id', $account->id)
            ->selectRaw("
            COALESCE(SUM(
                CASE 
                    WHEN type = 'debit' THEN amount 
                    WHEN type = 'credit' THEN -amount 
                    ELSE 0 
                END
            ), 0) AS balance
        ")
            ->first();

        return response()->json(['balance' => $balance->balance]);
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function cashFlow()
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $accounts = AccountTransaction::join(
                'accounts as A',
                'account_transactions.account_id',
                '=',
                'A.id'
            )
                ->leftjoin(
                    'transaction_payments as TP',
                    'account_transactions.transaction_payment_id',
                    '=',
                    'TP.id'
                )
                ->leftjoin(
                    'transaction_payments as child_payments',
                    'TP.id',
                    '=',
                    'child_payments.parent_id'
                )
                ->leftjoin(
                    'transactions as child_sells',
                    'child_sells.id',
                    '=',
                    'child_payments.transaction_id'
                )
                ->leftJoin('users AS u', 'account_transactions.created_by', '=', 'u.id')
                ->leftJoin('contacts AS c', 'TP.payment_for', '=', 'c.id')
                ->where('A.business_id', $business_id)
                ->with(['transaction', 'transaction.contact', 'transfer_transaction', 'transaction.transaction_for'])
                ->select(['account_transactions.type', 'account_transactions.amount', 'operation_date',
                    'account_transactions.sub_type', 'transfer_transaction_id',
                    'account_transactions.transaction_id',
                    'account_transactions.id',
                    'A.name as account_name',
                    'TP.payment_ref_no as payment_ref_no',
                    'TP.is_return',
                    'TP.is_advance',
                    'TP.method',
                    'TP.transaction_no',
                    'TP.card_transaction_number',
                    'TP.card_number',
                    'TP.card_type',
                    'TP.card_holder_name',
                    'TP.card_month',
                    'TP.card_year',
                    'TP.card_security',
                    'TP.cheque_number',
                    'TP.bank_account_number',
                    'account_transactions.account_id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                    'c.name as payment_for_contact',
                    'c.type as payment_for_type',
                    'c.supplier_business_name as payment_for_business_name',
                    DB::raw('SUM(child_payments.amount) total_recovered'),
                    DB::raw("GROUP_CONCAT(child_sells.invoice_no SEPARATOR ', ') as child_sells"),
                ])
                ->groupBy('account_transactions.id')
                ->orderBy('account_transactions.operation_date', 'asc');
            if (! empty(request()->input('type'))) {
                $accounts->where('account_transactions.type', request()->input('type'));
            }

            $permitted_locations = auth()->user()->permitted_locations();
            $account_ids = [];
            if ($permitted_locations != 'all') {
                $locations = BusinessLocation::where('business_id', $business_id)
                    ->whereIn('id', $permitted_locations)
                    ->get();

                foreach ($locations as $location) {
                    if (! empty($location->default_payment_accounts)) {
                        $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                        foreach ($default_payment_accounts as $account) {
                            if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                                $account_ids[] = $account['account'];
                            }
                        }
                    }
                }

                $account_ids = array_unique($account_ids);
            }

            if ($permitted_locations != 'all') {
                $accounts->whereIn('A.id', $account_ids);
            }

            $location_id = request()->input('location_id');
            if (! empty($location_id)) {
                $location = BusinessLocation::find($location_id);
                if (! empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    $account_ids = [];
                    foreach ($default_payment_accounts as $account) {
                        if (! empty($account['is_enabled']) && ! empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }

                    $accounts->whereIn('A.id', $account_ids);
                }
            }

            if (! empty(request()->input('account_id'))) {
                $accounts->where('A.id', request()->input('account_id'));
            }

            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');

            if (! empty($start_date) && ! empty($end_date)) {
                $accounts->whereBetween(DB::raw('date(operation_date)'), [$start_date, $end_date]);
            }

            if (request()->has('only_payment_recovered')) {
                // payment date is today and transaction date is less than today
                $accounts->leftJoin('transactions AS t', 'TP.transaction_id', '=', 't.id')
                    ->whereDate('operation_date', '=', \Carbon::now()->format('Y-m-d'))
                    ->where(function ($q): void {
                        $q->whereDate('t.transaction_date', '<',
                            \Carbon::now()->format('Y-m-d'))
                            ->orWhere('TP.is_advance', 1);
                    });
            }

            $payment_types = $this->commonUtil->payment_types(null, true, $business_id);

            return DataTables::of($accounts)
                ->editColumn('method', function ($row) use ($payment_types) {
                    if (! empty($row->method) && isset($payment_types[$row->method])) {
                        return $payment_types[$row->method];
                    } else {
                        return '';
                    }
                })
                ->addColumn('payment_details', function ($row) {
                    $arr = [];
                    if (! empty($row->transaction_no)) {
                        $arr[] = '<b>'.__('lang_v1.transaction_no').'</b>: '.$row->transaction_no;
                    }

                    if ($row->method == 'card' && ! empty($row->card_transaction_number)) {
                        $arr[] = '<b>'.__('lang_v1.card_transaction_no').'</b>: '.$row->card_transaction_number;
                    }

                    if ($row->method == 'card' && ! empty($row->card_number)) {
                        $arr[] = '<b>'.__('lang_v1.card_no').'</b>: '.$row->card_number;
                    }
                    if ($row->method == 'card' && ! empty($row->card_type)) {
                        $arr[] = '<b>'.__('lang_v1.card_type').'</b>: '.$row->card_type;
                    }
                    if ($row->method == 'card' && ! empty($row->card_holder_name)) {
                        $arr[] = '<b>'.__('lang_v1.card_holder_name').'</b>: '.$row->card_holder_name;
                    }
                    if ($row->method == 'card' && ! empty($row->card_month)) {
                        $arr[] = '<b>'.__('lang_v1.month').'</b>: '.$row->card_month;
                    }
                    if ($row->method == 'card' && ! empty($row->card_year)) {
                        $arr[] = '<b>'.__('lang_v1.year').'</b>: '.$row->card_year;
                    }
                    if ($row->method == 'card' && ! empty($row->card_security)) {
                        $arr[] = '<b>'.__('lang_v1.security_code').'</b>: '.$row->card_security;
                    }
                    if (! empty($row->cheque_number)) {
                        $arr[] = '<b>'.__('lang_v1.cheque_no').'</b>: '.$row->cheque_number;
                    }
                    if (! empty($row->bank_account_number)) {
                        $arr[] = '<b>'.__('lang_v1.card_no').'</b>: '.$row->bank_account_number;
                    }

                    return implode(', ', $arr);
                })
                ->addColumn('debit', '@if($type == "debit")<span class="debit" data-orig-value="{{$amount}}">@format_currency($amount)</span>@endif')
                ->addColumn('credit', '@if($type == "credit")<span class="debit" data-orig-value="{{$amount}}">@format_currency($amount)</span>@endif')
                ->addColumn('balance', function ($row) {
                    $balance = AccountTransaction::where('account_id',
                        $row->account_id)
                        ->where('operation_date', '<=', $row->operation_date)
                        ->whereNull('deleted_at')
                        ->select(DB::raw("SUM(IF(type='credit', amount, -1 * amount)) as balance"))
                        ->first()->balance;

                    return '<span class="balance" data-orig-value="'.$balance.'">'.$this->commonUtil->num_f($balance, true).'</span>';
                })
                ->addColumn('total_balance', function ($row) use ($business_id, $account_ids, $permitted_locations) {
                    $query = AccountTransaction::join(
                        'accounts as A',
                        'account_transactions.account_id',
                        '=',
                        'A.id'
                    )
                        ->where('A.business_id', $business_id)
                        ->where('operation_date', '<=', $row->operation_date)
                        ->whereNull('account_transactions.deleted_at')
                        ->select(DB::raw("SUM(IF(type='credit', amount, -1 * amount)) as balance"));

                    if (! empty(request()->input('type'))) {
                        $query->where('type', request()->input('type'));
                    }
                    if ($permitted_locations != 'all' || ! empty(request()->input('location_id'))) {
                        $query->whereIn('A.id', $account_ids);
                    }

                    if (! empty(request()->input('account_id'))) {
                        $query->where('A.id', request()->input('account_id'));
                    }

                    $balance = $query->first()->balance;

                    return '<span class="total_balance" data-orig-value="'.$balance.'">'.$this->commonUtil->num_f($balance, true).'</span>';
                })
                ->editColumn('operation_date', function ($row) {
                    return $this->commonUtil->format_date($row->operation_date, true);
                })
                ->editColumn('sub_type', function ($row) {
                    return $this->__getPaymentDetails($row);
                })
                ->removeColumn('id')
                ->rawColumns(['credit', 'debit', 'balance', 'sub_type', 'total_balance', 'payment_details'])
                ->make(true);
        }
        $accounts = Account::forDropdown($business_id, false);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('account.cash_flow')
            ->with(compact('accounts', 'business_locations'));
    }

    public function __getPaymentDetails($row)
    {
        $details = '';
        if (! empty($row->sub_type)) {
            $details = __('account.'.$row->sub_type);
            if (in_array($row->sub_type, ['fund_transfer', 'deposit']) && ! empty($row->transfer_transaction)) {
                if ($row->type == 'credit') {
                    $details .= ' ( '.__('account.from').': '.$row->transfer_transaction->account->name.')';
                } else {
                    $details .= ' ( '.__('account.to').': '.$row->transfer_transaction->account->name.')';
                }
            }
        } else {
            if (! empty($row->transaction->type)) {
                if ($row->transaction->type == 'purchase') {
                    $details = __('lang_v1.purchase').'<br><b>'.__('purchase.supplier').':</b> '.$row->transaction->contact->full_name_with_business.'<br><b>'.
                    __('purchase.ref_no').':</b> <a href="#" data-href="'.action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->transaction->id]).'" class="btn-modal" data-container=".view_modal">'.$row->transaction->ref_no.'</a>';
                } elseif ($row->transaction->type == 'expense') {
                    $details = __('lang_v1.expense').'<br><b>'.__('purchase.ref_no').':</b>'.$row->transaction->ref_no;
                } elseif ($row->transaction->type == 'sell') {
                    $is_return = $row->is_return == 1 ? ' ('.__('lang_v1.change_return').')' : '';
                    $details = __('sale.sale').$is_return.'<br><b>'.__('contact.customer').':</b> '.$row->transaction->contact->full_name_with_business.'<br><b>'.
                    __('sale.invoice_no').':</b> <a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction->id]).'" class="btn-modal" data-container=".view_modal">'.$row->transaction->invoice_no.'</a>';
                }
            } else {
                // for contact payment which is not advance
                if ($row->is_advance != 1) {
                    if ($row->payment_for_type == 'supplier') {
                        $details .= '<b>'.__('purchase.supplier').':</b> ';
                    } elseif ($row->payment_for_type == 'customer') {
                        $details .= '<b>'.__('contact.customer').':</b> ';
                    } else {
                        $details .= '<b>'.__('account.payment_for').':</b> ';
                    }

                    if (! empty($row->payment_for_business_name)) {
                        $details .= $row->payment_for_business_name.', ';
                    }
                    if (! empty($row->payment_for_contact)) {
                        $details .= $row->payment_for_contact;
                    }
                }
            }
        }

        if (! empty($row->payment_ref_no)) {
            if (! empty($details)) {
                $details .= '<br/>';
            }

            $details .= '<b>'.__('lang_v1.pay_reference_no').':</b> '.$row->payment_ref_no;
        }
        if (! empty($row->transaction->contact) && $row->transaction->type == 'expense') {
            if (! empty($details)) {
                $details .= '<br/>';
            }

            $details .= '<b>';
            $details .= __('lang_v1.expense_for_contact');
            $details .= ':</b> '.$row->transaction->contact->full_name_with_business;
        }

        if (! empty($row->transaction->transaction_for)) {
            if (! empty($details)) {
                $details .= '<br/>';
            }

            $details .= '<b>'.__('expense.expense_for').':</b> '.$row->transaction->transaction_for->user_full_name;
        }

        if ($row->is_advance == 1) {
            $total_advance = $row->amount - $row->total_recovered;
            $details .= '<br>';

            if ($total_advance > 0) {
                $details .= '<b>'.__('lang_v1.advance_payment').'</b>: '.$this->commonUtil->num_f($total_advance, true).'<br>';
            }

            if (! empty($row->child_sells)) {
                $details .= '<b>'.__('lang_v1.payments_recovered_for').'</b>: '.$row->child_sells.'<br>';
            }

            if ($row->payment_for_type == 'supplier') {
                $details .= '<b>'.__('purchase.supplier').':</b> ';
            } elseif ($row->payment_for_type == 'customer') {
                $details .= '<b>'.__('contact.customer').':</b> ';
            } else {
                $details .= '<b>'.__('account.payment_for').':</b> ';
            }

            if (! empty($row->payment_for_business_name)) {
                $details .= $row->payment_for_business_name.', ';
            }
            if (! empty($row->payment_for_contact)) {
                $details .= $row->payment_for_contact;
            }
        }

        if (! empty($row->added_by)) {
            $details .= '<br><b>'.__('lang_v1.added_by').':</b> '.$row->added_by;
        }

        return $details;
    }

    /**
     * activate the specified account.
     *
     * @return Response
     */
    public function activate($id)
    {
        if (! auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');

                $account = Account::where('business_id', $business_id)
                    ->findOrFail($id);

                $account->is_closed = 0;
                $account->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Edit the specified resource from storage.
     *
     * @return Response
     */
    public function editAccountTransaction($id)
    {
        if (! auth()->user()->can('edit_account_transaction')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $account_transaction = AccountTransaction::with(['account', 'transfer_transaction'])->findOrFail($id);

        $accounts = Account::where('business_id', $business_id)
            ->NotClosed()
            ->pluck('name', 'id');

        return view('account.edit_account_transaction')
            ->with(compact('accounts', 'account_transaction'));
    }

    public function updateAccountTransaction(Request $request, $id)
    {
        if (! auth()->user()->can('edit_account_transaction')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $account_transaction = AccountTransaction::with(['transfer_transaction'])->findOrFail($id);

            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $note = $request->input('note');

            $account_transaction->amount = $this->commonUtil->num_uf($request->input('amount'));
            $account_transaction->operation_date = $this->commonUtil->uf_date($request->input('operation_date'), true);
            $account_transaction->note = $request->input('note');

            if ($request->input('account_id')) {
                $account_transaction->account_id = $request->input('account_id');
            }

            $account_transaction->save();

            if (! empty($account_transaction->transfer_transaction)) {
                $transfer_transaction = $account_transaction->transfer_transaction;

                $transfer_transaction->amount = $amount;
                $transfer_transaction->operation_date = $account_transaction->operation_date;
                $transfer_transaction->note = $account_transaction->note;

                if ($account_transaction->sub_type == 'deposit') {
                    $transfer_transaction->account_id = $request->input('from_account');
                }
                if ($account_transaction->sub_type == 'fund_transfer') {
                    $transfer_transaction->account_id = $request->input('to_account');
                }

                $transfer_transaction->save();
            }

            DB::commit();

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
