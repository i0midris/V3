<?php

namespace Modules\Accounting\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\InvoiceLayout;
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

class JournalEntryController extends Controller
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
    public function getData()
    {

        // $business_id = request()->session()->get('user.business_id');

        // if (! (auth()->user()->can('superadmin') ||
        //     $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
        //     ! (auth()->user()->can('accounting.view_journal'))) {
        //     abort(403, 'Unauthorized action.');
        // }

        // if (request()->ajax()) {
        //     $journal = AccountingAccTransMapping::where('accounting_acc_trans_mappings.business_id', $business_id)
        //                 ->join('users as u', 'accounting_acc_trans_mappings.created_by', 'u.id')
        //                 ->where('type', 'journal_entry')
        //                 ->select(['accounting_acc_trans_mappings.id', 'ref_no', 'operation_date', 'note',
        //                     DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
        //                 ]);

        //     if (! empty(request()->start_date) && ! empty(request()->end_date)) {
        //         $start = request()->start_date;
        //         $end = request()->end_date;
        //         $journal->whereDate('accounting_acc_trans_mappings.operation_date', '>=', $start)
        //                     ->whereDate('accounting_acc_trans_mappings.operation_date', '<=', $end);
        //     }

        //     return Datatables::of($journal)
        //         ->addColumn(
        //             'action', function ($row) {
        //                 $html = '<div class="btn-group">
        //                         <button type="button" class="btn btn-info dropdown-toggle btn-xs"
        //                             data-toggle="dropdown" aria-expanded="false">'.
        //                             __('messages.actions').
        //                             '<span class="caret"></span><span class="sr-only">Toggle Dropdown
        //                             </span>
        //                         </button>
        //                         <ul class="dropdown-menu dropdown-menu-right" role="menu">';
        //                 if (auth()->user()->can('accounting.view_journal')) {
        //                     // $html .= '<li>
        //                     //         <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'show'], [$row->id]).'">
        //                     //             <i class="fas fa-eye" aria-hidden="true"></i>'.__("messages.view").'
        //                     //         </a>
        //                     //         </li>';
        //                 }

        //                 if (auth()->user()->can('accounting.edit_journal')) {
        //                     $html .= '<li>
        //                             <a href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'edit'], [$row->id]).'">
        //                                 <i class="fas fa-edit"></i>'.__('messages.edit').'
        //                             </a>
        //                         </li>';
        //                 }

        //                 if (auth()->user()->can('accounting.delete_journal')) {
        //                     $html .= '<li>
        //                             <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'destroy'], [$row->id]).'" class="delete_journal_button">
        //                                 <i class="fas fa-trash" aria-hidden="true"></i>'.__('messages.delete').'
        //                             </a>
        //                             </li>';
        //                 }

        //                 $html .= '</ul></div>';

        //                 return $html;
        //             })
        //         ->rawColumns(['action'])
        //         ->make(true);
        // }

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $journal = AccountingAccTransMapping::where('accounting_acc_trans_mappings.business_id', $business_id)
                ->join('users as u', 'accounting_acc_trans_mappings.created_by', 'u.id')
                ->join('accounting_accounts_transactions', 'accounting_acc_trans_mappings.id', 'accounting_accounts_transactions.acc_trans_mapping_id')
                ->join('accounting_accounts', 'accounting_accounts_transactions.accounting_account_id', 'accounting_accounts.id')
                     //   ->where('accounting_acc_trans_mappings.type', 'journal_entry')
                ->select(['accounting_acc_trans_mappings.id', 'accounting_acc_trans_mappings.ref_no', 'accounting_acc_trans_mappings.operation_date', 'accounting_acc_trans_mappings.note', 'accounting_accounts.name as accname', 'accounting_accounts_transactions.sub_type as sub_type',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                    DB::raw("CASE  WHEN accounting_accounts_transactions.type  ='debit' THEN accounting_accounts_transactions.amount ELSE 0 END AS debit"),
                    DB::raw("CASE  WHEN accounting_accounts_transactions.type  ='credit' THEN accounting_accounts_transactions.amount ELSE 0 END AS credit"),
                ]);

            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $journal->whereDate('accounting_acc_trans_mappings.operation_date', '>=', $start)
                    ->whereDate('accounting_acc_trans_mappings.operation_date', '<=', $end);
            }

            return Datatables::of($journal)
                ->addColumn(
                    'action', function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">'.
                                    __('messages.actions').
                                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <hr>';

                        if (auth()->user()->can('accounting.view_journal')) {
                            // $html .= '<li>
                            //         <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'show'], [$row->id]).'">
                            //             <i class="fas fa-eye" aria-hidden="true"></i>'.__("messages.view").'
                            //         </a>
                            //         </li>';
                        }

                        if (auth()->user()->can('accounting.edit_journal')) {
                            $html .= '<li>
                                    <a href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'edit'], [$row->id]).'">
                                        <i class="fas fa-edit"></i>'.__('messages.edit').'
                                    </a>
                                </li>';
                        }

                        if (auth()->user()->can('accounting.delete_journal')) {
                            $html .= '<li>
                                    <a href="#" data-href="'.action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'destroy'], [$row->id]).'" class="delete_journal_button">
                                        <i class="fas fa-trash" aria-hidden="true"></i>'.__('messages.delete').'
                                    </a>
                                    </li>';
                        }

                        $html .= '</ul><hr/></div>';

                        return $html;
                    })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::journal_entry.index');
    }

    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        $data = AccountingAccTransMapping::query()
            ->where('business_id', $business_id);

        if (! empty(request()->start_date) && ! empty(request()->end_date)) {
            $start = request()->start_date;
            $end = request()->end_date;
            $data = $data->whereDate('accounting_acc_trans_mappings.operation_date', '>=', $start)
                ->whereDate('accounting_acc_trans_mappings.operation_date', '<=', $end);
        }

        $data = $data->orderBy('id', 'DESC');

        $data = $data->get();

        if (! empty(request()->location_id) || ! empty(request()->account_id)) {
            foreach ($data as $key => $journal) {
                $boo = true;

                foreach ($journal->childs() as $child) {
                    $acc = $child->account()->first();

                    if (! empty(request()->location_id) && ! empty(request()->account_id)) {
                        if (request()->location_id == $child->location_id && request()->account_id == $acc->id) {
                            $boo = false;
                        }
                    } else {
                        if (! empty(request()->location_id) && request()->location_id == $child->location_id) {
                            $boo = false;
                        }

                        if (! empty(request()->account_id) && request()->account_id == $acc->id) {
                            $boo = false;
                        }

                    }

                }

                if ($boo) {
                    unset($data[$key]);
                }

            }
        }

        if (! empty(request()->search)) {
            $search = request()->search;

            foreach ($data as $key => $journal) {
                if (strpos($journal->ref_no, $search) !== false) {
                    continue;
                }

                if (strpos($journal->type_trans, $search) !== false) {
                    continue;
                }

                $user = $journal->createdBy()->first();

                if (isset($user->id) && strpos($user->first_name, $search) !== false) {
                    continue;
                }

                if (isset($user->id) && strpos($user->last_name, $search) !== false) {
                    continue;
                }

                $boo = false;

                foreach ($journal->childs() as $child) {

                    if (strpos($child->note, $search) !== false) {
                        $boo = true;
                    }

                    if (strpos($child->amount, $search) !== false) {
                        $boo = true;
                    }

                    $location = $child->location()->first();

                    if (isset($location->id) && strpos($location->name, $search) !== false) {
                        $boo = true;
                    }

                    $acc = $child->account()->first();

                    if (isset($acc->id) && strpos($acc->gl_code, $search) !== false) {
                        $boo = true;
                    }

                    if (isset($acc->id) && strpos($acc->name, $search) !== false) {
                        $boo = true;
                    }
                }

                if ($boo) {
                    continue;
                }

                unset($data[$key]);
            }

        }

        $data = PaginateCollection::paginate($data, request()->has('page_number') && is_numeric(request()->page_number) ? request()->page_number : 10);

        $business_locations = BusinessLocation::forDropdown($business_id);

        $all_accounts = AccountingAccount::forDropdown($business_id);

        return view('accounting::new_journal_entry.index')
            ->with(compact('data', 'business_locations', 'all_accounts'));
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
            ! (auth()->user()->can('accounting.add_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('accounting::journal_entry.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
    
        if (
            !auth()->user()->can('superadmin') &&
            !$this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module') ||
            !auth()->user()->can('accounting.add_journal')
        ) {
            abort(403, 'Unauthorized action.');
        }
    
        try {
            DB::beginTransaction();
    
            $user_id = $request->session()->get('user.id');
            $account_ids = $request->get('account_id');
            $location_ids = $request->get('location_id');
            $rec_notes = $request->get('rec_note');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $journal_date = $this->util->uf_date($request->get('journal_date'), true);
    
            $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id);
    
            $ref_no = $request->get('ref_no');
            $ref_count = $this->util->setAndGetReferenceCount('journal_entry');
            if (empty($ref_no)) {
                $prefix = !empty($accounting_settings['journal_entry_prefix']) ? $accounting_settings['journal_entry_prefix'] : '';
                $ref_no = $this->util->generateReferenceNumber('journal_entry', $ref_count, $business_id, $prefix);
            }
    
            $acc_trans_mapping = new AccountingAccTransMapping();
            $acc_trans_mapping->business_id = $business_id;
            $acc_trans_mapping->ref_no = $ref_no;
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->type = 'journal_entry';
            $acc_trans_mapping->created_by = $user_id;
            $acc_trans_mapping->operation_date = $journal_date;
            $acc_trans_mapping->save();
    
            $should_create_transaction = false;
            $final_total = 0;
            $transaction_location_id = null;
            $dynamic_expense_category_id = null;
    
            foreach ($account_ids as $index => $account_id) {
                if (empty($account_id)) {
                    continue;
                }
    
                $amount = null;
                $type = null;
    
                if (!empty($credits[$index])) {
                    $amount = (float) $credits[$index];
                    $type = 'credit';
                } elseif (!empty($debits[$index])) {
                    $amount = (float) $debits[$index];
                    $type = 'debit';
                }
    
                if (is_null($amount)) {
                    continue;
                }
    
                // Save to accounting_accounts_transactions
                $transaction_row = [
                    'accounting_account_id' => $account_id,
                    'amount' => $amount,
                    'type' => $type,
                    'location_id' => $location_ids[$index] ?? null,
                    'note' => $rec_notes[$index] ?? null,
                    'created_by' => $user_id,
                    'operation_date' => $journal_date,
                    'sub_type' => 'journal_entry',
                    'acc_trans_mapping_id' => $acc_trans_mapping->id,
                ];
    
                $account_transaction = new AccountingAccountsTransaction();
                $account_transaction->fill($transaction_row);
                $account_transaction->save();
    
                // Capture location for transaction
                if (!$transaction_location_id && !empty($location_ids[$index])) {
                    $transaction_location_id = $location_ids[$index];
                }
    
                // Check GL code logic
                $account = AccountingAccount::find($account_id);
                if ($account && is_numeric($account->gl_code)) {
                    $gl_code = (int) $account->gl_code;
    
                    if ($gl_code >= 5101 && $gl_code <= 5399) {
                        $should_create_transaction = true;
    
                        // Count only debit as expense total
                        if ($type === 'debit') {
                            $final_total += $amount;
                        }
    
                        // Link expense category only once
                        if (!$dynamic_expense_category_id && $account->link_table === 'expense_categories') {
                            $dynamic_expense_category_id = $account->link_id;
                        }
                    }
                }
            }
    
            if ($should_create_transaction && $final_total > 0) {
                $transaction = new \App\Transaction();
                $transaction->business_id = $business_id;
                $transaction->location_id = $transaction_location_id;
                $transaction->type = 'expense';
                $transaction->status = 'final';
                $transaction->payment_status = 'paid';
                $transaction->ref_no = $ref_no;
                $transaction->transaction_date = $journal_date;
                $transaction->final_total = $final_total;
                $transaction->expense_category_id = $dynamic_expense_category_id;
                $transaction->created_by = $user_id;
                $transaction->save();
    
                $payment = new \App\TransactionPayment();
                $payment->transaction_id = $transaction->id;
                $payment->business_id = $business_id;
                $payment->amount = $final_total;
                $payment->method = 'cash';
                $payment->paid_on = $journal_date;
                $payment->created_by = $user_id;
                $payment->payment_ref_no = 'SP' . date('Y') . '/' . str_pad($transaction->id, 4, '0', STR_PAD_LEFT);
                $payment->save();
            }
    
            DB::commit();
    
            $output = ['success' => 1, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
    
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
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
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') ||
            $this->moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module')) ||
            ! (auth()->user()->can('accounting.view_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('accounting::journal_entry.show');
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
            ! (auth()->user()->can('accounting.edit_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        $journal = AccountingAccTransMapping::where('business_id', $business_id)
            ->where('type', 'journal_entry')
            ->where('id', $id)
            ->firstOrFail();
        $accounts_transactions = AccountingAccountsTransaction::with('account')
            ->with('location')
            ->where('acc_trans_mapping_id', $id)
            ->get()->toArray();

        return view('accounting::journal_entry.edit')
            ->with(compact('journal', 'accounts_transactions'));
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
            ! (auth()->user()->can('accounting.edit_journal'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $user_id = request()->session()->get('user.id');

            $account_ids = $request->get('account_id');
            $location_ids = $request->get('location_id');
            $rec_notes = $request->get('rec_note');
            $accounts_transactions_id = $request->get('accounts_transactions_id');
            $credits = $request->get('credit');
            $debits = $request->get('debit');
            $journal_date = $request->get('journal_date');

            $acc_trans_mapping = AccountingAccTransMapping::where('business_id', $business_id)
                ->where('type', 'journal_entry')
                ->where('id', $id)
                ->firstOrFail();
            $acc_trans_mapping->note = $request->get('note');
            $acc_trans_mapping->operation_date = $this->util->uf_date($journal_date, true);
            $acc_trans_mapping->update();

            // save details in account trnsactions table
            foreach ($account_ids as $index => $account_id) {
                if (! empty($account_id)) {
                    $transaction_row = [];
                    $transaction_row['accounting_account_id'] = $account_id;

                    if (! empty($location_ids[$index])) {
                        $transaction_row['location_id'] = $location_ids[$index];
                    }

                    if (! empty($credits[$index])) {
                        $transaction_row['amount'] = $credits[$index];
                        $transaction_row['type'] = 'credit';
                    }

                    if (! empty($debits[$index])) {
                        $transaction_row['amount'] = $debits[$index];
                        $transaction_row['type'] = 'debit';
                    }

                    if (! empty($rec_notes[$index])) {
                        $transaction_row['note'] = $rec_notes[$index];
                    }

                    $transaction_row['created_by'] = $user_id;
                    $transaction_row['operation_date'] = $this->util->uf_date($journal_date, true);
                    $transaction_row['sub_type'] = 'journal_entry';
                    $transaction_row['acc_trans_mapping_id'] = $acc_trans_mapping->id;

                    if (! empty($accounts_transactions_id[$index])) {
                        $accounts_transactions = AccountingAccountsTransaction::find($accounts_transactions_id[$index]);
                        $accounts_transactions->fill($transaction_row);
                        $accounts_transactions->update();
                    } else {
                        $accounts_transactions = new AccountingAccountsTransaction;
                        $accounts_transactions->fill($transaction_row);
                        $accounts_transactions->save();
                    }
                } elseif (! empty($accounts_transactions_id[$index])) {
                    AccountingAccountsTransaction::delete($accounts_transactions_id[$index]);
                }
            }

            $output = ['success' => 1,
                'msg' => __('lang_v1.updated_success'),
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            print_r($e->getMessage());
            exit;
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
            ! (auth()->user()->can('accounting.delete_journal'))) {
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

    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $journal = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->firstOrFail();

        $business = Business::where('id', $business_id)->first();

        foreach ($journal->childs() as $child) {
            $location_id = $child->location_id;
            break;
        }

        $business_location = BusinessLocation::where('id', $location_id)->first();

        $invoice_layout = null;
        if (isset($business_location->id)) {
            $invoice_layout = InvoiceLayout::where('id', $business_location->invoice_layout_id)->first();
        }

        return view('accounting::new_journal_entry.print')
            ->with(compact('journal', 'business', 'business_location', 'invoice_layout'));
    }

    public function print_receipt($id)
    {

        $business_id = request()->session()->get('business.id');

        $business = Business::where('id', $business_id)->first();

        $journal = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->first();

        $location_id = null;

        foreach ($journal->childs() as $child) {
            $location_id = $child->location_id;
            break;
        }

        $debit = $journal->childs()[1];
        $credit = $journal->childs()[0];

        $business_location = BusinessLocation::where('id', $location_id)->first();

        $invoice_layout = InvoiceLayout::where('id', $business_location->invoice_layout_id)->first();

        $data['is_enabled'] = true;
        $data['print_title'] = 'Receipt';
        $data['html_content'] = view('accounting::receipt.print')
            ->with(compact('journal', 'business', 'business_location', 'invoice_layout', 'debit', 'credit'))->render();

        $output = ['success' => 1, 'receipt' => $data];

        return $output;

    }

    public function print_expense($id)
    {

        $business_id = request()->session()->get('business.id');

        $business = Business::where('id', $business_id)->first();

        $journal = AccountingAccTransMapping::where('id', $id)
            ->where('business_id', $business_id)->first();

        $location_id = null;

        foreach ($journal->childs() as $child) {
            $location_id = $child->location_id;
            break;
        }

        $debit = null;
        $credit = null;
        $tax_debit = null;
        foreach ($journal->childs() as $one_journal) {
            if ($one_journal->account()->first()->gl_code == 2105) {
                $tax_debit = $one_journal;
            } elseif ($one_journal->type == 'credit') {
                $credit = $one_journal;
            } elseif ($one_journal->type == 'debit') {
                $debit = $one_journal;
            }

        }

        $business_location = BusinessLocation::where('id', $location_id)->first();

        $invoice_layout = InvoiceLayout::where('id', $business_location->invoice_layout_id)->first();

        $data['is_enabled'] = true;
        $data['print_title'] = 'Expense';
        $data['html_content'] = view('accounting::expense.print')
            ->with(compact('journal', 'business', 'business_location', 'invoice_layout', 'debit', 'credit', 'tax_debit'))->render();

        $output = ['success' => 1, 'receipt' => $data];

        return $output;

    }
}
