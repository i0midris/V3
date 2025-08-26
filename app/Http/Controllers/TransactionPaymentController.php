<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Events\TransactionPaymentAdded;
use App\Events\TransactionPaymentUpdated;
use App\Exceptions\AdvanceBalanceNotAvailable;
use App\Transaction;
use App\TransactionPayment;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Datatables;
use DB;
use Illuminate\Http\Request;

class TransactionPaymentController extends Controller
{
    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
public function store(Request $request)
{
    try {
        $business_id = $request->session()->get('user.business_id');
        $transaction_id = $request->input('transaction_id');

        $transaction = Transaction::where('business_id', $business_id)
            ->with(['contact'])
            ->findOrFail($transaction_id);

        $output_acc = (new \App\Utils\ModuleUtil)->getModuleData('MKamel_check888', [
            'request' => $request,
            'transaction' => $transaction
        ]);

        if (isset($output_acc['Accounting']['success']) && $output_acc['Accounting']['success'] == 0) {
            return redirect()->back()->with(['status' => $output_acc['Accounting']]);
        }

        $transaction_before = $transaction->replicate();

        if (! (
            auth()->user()->can('purchase.payments') ||
            auth()->user()->can('hms.add_booking_payment') ||
            auth()->user()->can('sell.payments') ||
            auth()->user()->can('all_expense.access') ||
            auth()->user()->can('view_own_expense')
        )) {
            abort(403, 'Unauthorized action.');
        }

        if ($transaction->payment_status != 'paid') {
            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number', 'currency_code', 'exchange_rate', 'base_amount'
            ]);

            $inputs['currency_code'] = $request->input('currency_code') ?: null;
            $inputs['exchange_rate'] = $request->input('exchange_rate') ?: null;
            $inputs['base_amount'] = $this->transactionUtil->num_uf($request->input('base_amount')) ?: null;
            $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
            $inputs['transaction_id'] = $transaction->id;
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            $inputs['created_by'] = auth()->user()->id;
            $inputs['payment_for'] = $transaction->contact_id;

            if ($inputs['method'] == 'custom_pay_1') {
                $inputs['transaction_no'] = $request->input('transaction_no_1');
            } elseif ($inputs['method'] == 'custom_pay_2') {
                $inputs['transaction_no'] = $request->input('transaction_no_2');
            } elseif ($inputs['method'] == 'custom_pay_3') {
                $inputs['transaction_no'] = $request->input('transaction_no_3');
            }

            if (!empty($request->input('account_id')) && $inputs['method'] != 'advance') {
                $inputs['account_id'] = $request->input('account_id');
            }

            $prefix_type = 'purchase_payment';
            if (in_array($transaction->type, ['sell', 'sell_return'])) {
                $prefix_type = 'sell_payment';
            } elseif (in_array($transaction->type, ['expense', 'expense_refund'])) {
                $prefix_type = 'expense_payment';
            }

            DB::beginTransaction();

            $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
            $inputs['payment_ref_no'] = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);

            $inputs['business_id'] = $request->session()->get('business.id');
            $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            if ($inputs['method'] == 'advance' && $inputs['amount'] > ($transaction->contact->balance ?? 0)) {
                throw new AdvanceBalanceNotAvailable(__('lang_v1.required_advance_balance_not_available'));
            }

            if (!empty($inputs['amount'])) {
                $tp = TransactionPayment::create($inputs);

                if (isset($output_acc['Accounting']['success']) && $output_acc['Accounting']['success'] == 1) {
                    (new \App\Utils\ModuleUtil)->getModuleData('MKamel_store888', [
                        'request' => $request,
                        'contact_linked' => $output_acc['Accounting']['contact_linked'],
                        'account_linked' => $output_acc['Accounting']['account_linked'],
                        'transaction' => $transaction,
                        'tp' => $tp
                    ]);
                }

                if (!empty($request->input('denominations'))) {
                    $this->transactionUtil->addCashDenominations($tp, $request->input('denominations'));
                }

                $inputs['transaction_type'] = $transaction->type;
                event(new TransactionPaymentAdded($tp, $inputs));
            }

            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction_id, $transaction->final_total);
            $transaction->payment_status = $payment_status;

            $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);

            DB::commit();
        }

        $output = ['success' => true, 'msg' => __('purchase.payment_added_success')];
    } catch (\Exception $e) {
        DB::rollBack();
        $msg = __('messages.something_went_wrong');

        if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
            $msg = $e->getMessage();
        } else {
            \Log::error('Exception in TransactionPaymentController@store', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $output = ['success' => false, 'msg' => $msg];
    }

    return redirect()->back()->with(['status' => $output]);
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! (auth()->user()->can('sell.payments') || auth()->user()->can('purchase.payments') || auth()->user()->can('hms.add_booking_payment'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $transaction = Transaction::where('id', $id)
                ->with(['contact', 'business', 'transaction_for'])
                ->first();
            $payments_query = TransactionPayment::where('transaction_id', $id);

            $accounts_enabled = false;
            if ($this->moduleUtil->isModuleEnabled('account')) {
                $accounts_enabled = true;
                $payments_query->with(['payment_account']);
            }

            $payments = $payments_query->get();
            $location_id = ! empty($transaction->location_id) ? $transaction->location_id : null;
            $payment_types = $this->transactionUtil->payment_types($location_id, true);

            return view('transaction_payment.show_payments')
                ->with(compact('transaction', 'payments', 'payment_types', 'accounts_enabled'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('edit_purchase_payment') && ! auth()->user()->can('edit_sell_payment') && ! auth()->user()->can('hms.edit_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $payment_line = TransactionPayment::with(['denominations'])->where('method', '!=', 'advance')->findOrFail($id);

            $transaction = Transaction::where('id', $payment_line->transaction_id)
                ->where('business_id', $business_id)
                ->with(['contact', 'location'])
                ->first();

            $payment_types = $this->transactionUtil->payment_types($transaction->location);

            // Accounts
            $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

            return view('transaction_payment.edit_payment_row')
                ->with(compact('transaction', 'payment_types', 'payment_line', 'accounts'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('edit_purchase_payment') && ! auth()->user()->can('edit_sell_payment') && ! auth()->user()->can('all_expense.access') && ! auth()->user()->can('view_own_expense') && ! auth()->user()->can('hms.edit_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $inputs = $request->only(['amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number', ]);
            $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);

            if ($inputs['method'] == 'custom_pay_1') {
                $inputs['transaction_no'] = $request->input('transaction_no_1');
            } elseif ($inputs['method'] == 'custom_pay_2') {
                $inputs['transaction_no'] = $request->input('transaction_no_2');
            } elseif ($inputs['method'] == 'custom_pay_3') {
                $inputs['transaction_no'] = $request->input('transaction_no_3');
            }

            if (! empty($request->input('account_id'))) {
                $inputs['account_id'] = $request->input('account_id');
            }

            $payment = TransactionPayment::where('method', '!=', 'advance')->findOrFail($id);

            if (! empty($request->input('denominations'))) {
                $this->transactionUtil->updateCashDenominations($payment, $request->input('denominations'));
            }

            // Update parent payment if exists
            if (! empty($payment->parent_id)) {
                $parent_payment = TransactionPayment::find($payment->parent_id);
                $parent_payment->amount -= $payment->amount - $inputs['amount'];

                $parent_payment->save();
            }

            $business_id = $request->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                ->find($payment->transaction_id);

            $transaction_before = $transaction->replicate();
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (! empty($document_name)) {
                $inputs['document'] = $document_name;
            }

            DB::beginTransaction();

            $payment->update($inputs);

            // update payment status
            $payment_status = $this->transactionUtil->updatePaymentStatus($payment->transaction_id);
            $transaction->payment_status = $payment_status;

            $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);

            DB::commit();

            // event
            event(new TransactionPaymentUpdated($payment, $transaction->type));

            $output = ['success' => true,
                'msg' => __('purchase.payment_updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('delete_purchase_payment') && ! auth()->user()->can('delete_sell_payment') && ! auth()->user()->can('all_expense.access') && ! auth()->user()->can('view_own_expense') && ! auth()->user()->can('hms.delete_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $payment = TransactionPayment::findOrFail($id);

                DB::beginTransaction();

                if (! empty($payment->transaction_id)) {
                    TransactionPayment::deletePayment($payment);
                } else { // advance payment
                    $adjusted_payments = TransactionPayment::where('parent_id',
                        $payment->id)
                        ->get();

                    $total_adjusted_amount = $adjusted_payments->sum('amount');

                    // Get customer advance share from payment and deduct from advance balance
                    $total_customer_advance = $payment->amount - $total_adjusted_amount;
                    if ($total_customer_advance > 0) {
                        $this->transactionUtil->updateContactBalance($payment->payment_for, $total_customer_advance, 'deduct');
                    }

                    // Delete all child payments
                    foreach ($adjusted_payments as $adjusted_payment) {
                        // Make parent payment null as it will get deleted
                        $adjusted_payment->parent_id = null;
                        TransactionPayment::deletePayment($adjusted_payment);
                    }

                    // Delete advance payment
                    TransactionPayment::deletePayment($payment);
                }

                DB::commit();

                $output = ['success' => true,
                    'msg' => __('purchase.payment_deleted_success'),
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

    /**
     * Adds new payment to the given transaction.
     *
     * @param  int  $transaction_id
     * @return \Illuminate\Http\Response
     */
    
     public function addPayment($transaction_id)
{
    if (
        !auth()->user()->can('purchase.payments') &&
        !auth()->user()->can('sell.payments') &&
        !auth()->user()->can('all_expense.access') &&
        !auth()->user()->can('view_own_expense') &&
        !auth()->user()->can('hms.add_booking_payment')
    ) {
        abort(403, 'Unauthorized action.');
    }

    if (request()->ajax()) {
        $business_id = request()->session()->get('user.business_id');

        // Fetch transaction with related info
        $transaction = Transaction::where('business_id', $business_id)
            ->with(['contact', 'location'])
            ->findOrFail($transaction_id);

        // Fetch active currencies
        $currency_rates = \App\Models\CurrencyRate::where('business_id', $business_id)
            ->where('status', 1)
            ->get();

        if ($transaction->payment_status != 'paid') {
            // Show advance only for purchase/sell types
            $show_advance = in_array($transaction->type, ['sell', 'purchase']);

            // Get payment types
            $payment_types = $this->transactionUtil->payment_types(
                $transaction->location,
                $show_advance,
                $business_id
            );

            // Calculate due amount
            $paid_amount = $this->transactionUtil->getTotalPaid($transaction_id);
            $amount = $transaction->final_total - $paid_amount;
            $amount = $amount < 0 ? 0 : $amount;
            $amount_formated = $this->transactionUtil->num_f($amount);

            // Prepare default payment line
            $payment_line = new TransactionPayment;
            $payment_line->amount = $amount;
            $payment_line->method = 'cash';
            $payment_line->paid_on = now()->toDateTimeString();

            // Get available accounts
            $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

            // Render payment form view
            $view = view('transaction_payment.payment_row')
                ->with(compact(
                    'transaction',
                    'payment_types',
                    'payment_line',
                    'amount_formated',
                    'accounts',
                    'currency_rates' // Pass currency rates to the view
                ))
                ->render();

            return json_encode([
                'status' => 'due',
                'view' => $view
            ]);
        } else {
            return json_encode([
                'status' => 'paid',
                'view' => '',
                'msg' => __('purchase.amount_already_paid')
            ]);
        }
    }
}


    /**
     * Shows contact's payment due modal
     *
     * @param  int  $contact_id
     * @return \Illuminate\Http\Response
     */
    public function getPayContactDue($contact_id)
{
    if (!auth()->user()->can('sell.payments') && !auth()->user()->can('purchase.payments')) {
        abort(403, 'Unauthorized action.');
    }

    if (request()->ajax()) {
        $business_id = request()->session()->get('user.business_id');
        $due_payment_type = request()->input('type');

        $query = Contact::where('contacts.id', $contact_id)
            ->leftJoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->addSelect('contacts.name', 'contacts.supplier_business_name', 'contacts.id as contact_id');

        switch ($due_payment_type) {
            case 'purchase':
                $query->addSelect(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = t.id), 0)) as total_paid")
                );
                break;

            case 'purchase_return':
                $query->addSelect(
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = t.id), 0)) as total_return_paid")
                );
                break;

            case 'sell':
                $query->addSelect(
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1, -1 * amount, amount)) FROM transaction_payments WHERE transaction_payments.transaction_id = t.id), 0)) as total_paid"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = t.id), 0)) as total_return_paid")
                );
                break;

            case 'sell_return':
                $query->addSelect(
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = t.id), 0)) as total_return_paid")
                );
                break;
        }

        // Opening balance
        $query->addSelect(
            DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
            DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = t.id), 0)) as opening_balance_paid")
        );

        $contact_details = $query->first();

        // Calculate due amount
        $payment_line = new TransactionPayment;
        $ob_due = (float) $contact_details->opening_balance - (float) $contact_details->opening_balance_paid;

        switch ($due_payment_type) {
            case 'purchase':
                $payment_line->amount = (float) $contact_details->total_purchase - (float) $contact_details->total_paid;
                break;
            case 'purchase_return':
                $payment_line->amount = (float) $contact_details->total_purchase_return - (float) $contact_details->total_return_paid;
                break;
            case 'sell':
                $payment_line->amount = (float) $contact_details->total_invoice - (float) $contact_details->total_sell_return - (float) $contact_details->total_paid;
                break;
            case 'sell_return':
                $payment_line->amount = (float) $contact_details->total_sell_return - (float) $contact_details->total_return_paid;
                break;
        }

        // Add unpaid opening balance if positive
        if ($ob_due > 0) {
            $payment_line->amount += $ob_due;
        }

        $payment_line->amount = max($payment_line->amount, 0);
        $payment_line->method = 'cash';
        $payment_line->paid_on = now()->toDateTimeString();
        $amount_formated = $this->transactionUtil->num_f($payment_line->amount);

        // Get payment types, accounts, and currencies
        $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        $currencies = \App\Models\CurrencyRate::where('business_id', $business_id)
            ->where('status', 1)
            ->pluck('exchange_rate', 'currency_code');

        return view('transaction_payment.pay_supplier_due_modal')->with(compact(
            'contact_details',
            'payment_types',
            'payment_line',
            'due_payment_type',
            'ob_due',
            'amount_formated',
            'accounts',
            'currencies'
        ));
    }
}




    /**
     * Adds Payments for Contact due
     *
     * @return \Illuminate\Http\Response
     */
   public function postPayContactDue(Request $request)
    {
        if (! (auth()->user()->can('sell.payments') || auth()->user()->can('purchase.payments'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $rawAmount = str_replace(',', '', $request->input('amount'));
            if (is_null($rawAmount) || trim($rawAmount) === '' || !is_numeric($rawAmount)) {
                return redirect()->back()->with([
                    'status' => ['success' => false, 'msg' => __('lang_v1.amount_required')]
                ]);
            }

            $is_reverse = $request->input('is_reverse', 0);
            $amount = floatval($rawAmount);
            $amount = ($is_reverse == 1) ? -abs($amount) : abs($amount);


            $base_amount = $request->input('base_amount');
            $exchange_rate = $request->input('exchange_rate');
            $currency_code = $request->input('currency_code');

            if (!empty($currency_code)) {
                $exchange_rate = is_numeric($exchange_rate) && $exchange_rate > 0 ? $exchange_rate : 1;
                $base_amount = is_numeric($base_amount) ? floatval($base_amount) : 0;

                if ($base_amount <= 0) {
                    return redirect()->back()->with([
                        'status' => ['success' => false, 'msg' => __('lang_v1.base_amount_required')]
                    ]);
                }

                $amount = $base_amount * $exchange_rate;

                $amount = ($is_reverse == 1) ? -abs($amount) : abs($amount);
                $base_amount = ($is_reverse == 1) ? -abs($base_amount) : abs($base_amount);

                $request->merge([
                    'amount' => $amount,
                    'base_amount' => $base_amount,
                    'exchange_rate' => $exchange_rate,
                    'currency_code' => $currency_code,
                ]);
            } else {
                $amount = ($is_reverse == 1) ? -abs($amount) : abs($amount);
                $request->merge(['amount' => $amount]);
            }


            DB::beginTransaction();

            $moduleUtil = new \App\Utils\ModuleUtil;
            $output_acc = $moduleUtil->getModuleData('MKamel_check_2_888', ['request' => $request]);

            if (!empty($output_acc['Accounting']) && $output_acc['Accounting']['success'] == 0) {
                return redirect()->back()->with(['status' => $output_acc['Accounting']]);
            }

            $business_id = $request->session()->get('business.id');
            $tp = $this->transactionUtil->payContact($request);

            if (!empty($output_acc['Accounting']) && $output_acc['Accounting']['success'] == 1) {
                $moduleUtil->getModuleData('MKamel_store_2_888', [
                    'request' => $request,
                    'contact_linked' => $output_acc['Accounting']['contact_linked'],
                    'account_linked' => $output_acc['Accounting']['account_linked'],
                    'tp' => $tp,
                ]);
            }

            $pos_settings = session()->get('business.pos_settings');
            $pos_settings = !empty($pos_settings) ? json_decode($pos_settings, true) : [];
            $enable_cash_denomination = $pos_settings['enable_cash_denomination_for_payment_methods'] ?? [];

            if (
                in_array($tp->method, $enable_cash_denomination) &&
                !empty($request->input('denominations')) &&
                ($pos_settings['enable_cash_denomination_on'] ?? '') === 'all_screens'
            ) {
                $denominations = collect($request->input('denominations'))
                    ->filter()
                    ->map(function ($value, $key) use ($business_id) {
                        return [
                            'business_id' => $business_id,
                            'amount' => $key,
                            'total_count' => $value,
                        ];
                    })->values()->all();

                if (!empty($denominations)) {
                    $tp->denominations()->createMany($denominations);
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'status' => ['success' => true, 'msg' => __('purchase.payment_added_success')]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('postPayContactDue error: ' . $e->getMessage());

            return redirect()->back()->with([
                'status' => ['success' => false, 'msg' => __('messages.something_went_wrong')]
            ]);
        }
    }


    /**
     * view details of single..,
     * payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function viewPayment($payment_id)
    {
        if (! (auth()->user()->can('sell.payments') ||
                auth()->user()->can('purchase.payments') ||
                auth()->user()->can('edit_sell_payment') ||
                auth()->user()->can('delete_sell_payment') ||
                auth()->user()->can('edit_purchase_payment') ||
                auth()->user()->can('delete_purchase_payment') ||
                auth()->user()->can('hms.add_booking_payment')
        )) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('business.id');
            $single_payment_line = TransactionPayment::findOrFail($payment_id);

            $transaction = null;
            if (! empty($single_payment_line->transaction_id)) {
                $transaction = Transaction::where('id', $single_payment_line->transaction_id)
                    ->with(['contact', 'location', 'transaction_for'])
                    ->first();
            } else {
                $child_payment = TransactionPayment::where('business_id', $business_id)
                    ->where('parent_id', $payment_id)
                    ->with(['transaction', 'transaction.contact', 'transaction.location', 'transaction.transaction_for'])
                    ->first();
                $transaction = ! empty($child_payment) ? $child_payment->transaction : null;
            }

            $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

            return view('transaction_payment.single_payment_view')
                ->with(compact('single_payment_line', 'transaction', 'payment_types'));
        }
    }

    /**
     * Retrieves all the child payments of a parent payments
     * payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showChildPayments($payment_id)
    {
        if (! (auth()->user()->can('sell.payments') ||
                auth()->user()->can('purchase.payments') ||
                auth()->user()->can('edit_sell_payment') ||
                auth()->user()->can('delete_sell_payment') ||
                auth()->user()->can('edit_purchase_payment') ||
                auth()->user()->can('delete_purchase_payment')
        )) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('business.id');

            $child_payments = TransactionPayment::where('business_id', $business_id)
                ->where('parent_id', $payment_id)
                ->with(['transaction', 'transaction.contact'])
                ->get();

            $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

            return view('transaction_payment.show_child_payments')
                ->with(compact('child_payments', 'payment_types'));
        }
    }

    /**
     * Retrieves list of all opening balance payments.
     *
     * @param  int  $contact_id
     * @return \Illuminate\Http\Response
     */
    public function getOpeningBalancePayments($contact_id)
    {
        if (! (auth()->user()->can('sell.payments') ||
                auth()->user()->can('purchase.payments') ||
                auth()->user()->can('edit_sell_payment') ||
                auth()->user()->can('delete_sell_payment') ||
                auth()->user()->can('edit_purchase_payment') ||
                auth()->user()->can('delete_purchase_payment')
        )) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('business.id');
        if (request()->ajax()) {
            $query = TransactionPayment::leftjoin('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'opening_balance')
                ->where('t.contact_id', $contact_id)
                ->where('transaction_payments.business_id', $business_id)
                ->select(
                    'transaction_payments.amount',
                    'method',
                    'paid_on',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.document',
                    'transaction_payments.id',
                    'cheque_number',
                    'card_transaction_number',
                    'bank_account_number'
                )
                ->groupBy('transaction_payments.id');

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            return Datatables::of($query)
                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')
                ->editColumn('method', function ($row) {
                    $method = __('lang_v1.'.$row->method);
                    if ($row->method == 'cheque') {
                        $method .= '<br>('.__('lang_v1.cheque_no').': '.$row->cheque_number.')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>('.__('lang_v1.card_transaction_no').': '.$row->card_transaction_number.')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>('.__('lang_v1.bank_account_no').': '.$row->bank_account_number.')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method = __('lang_v1.custom_payment_1').'<br>('.__('lang_v1.transaction_no').': '.$row->transaction_no.')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method = __('lang_v1.custom_payment_2').'<br>('.__('lang_v1.transaction_no').': '.$row->transaction_no.')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method = __('lang_v1.custom_payment_3').'<br>('.__('lang_v1.transaction_no').': '.$row->transaction_no.')';
                    }

                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency paid-amount" data-orig-value="'.$row->amount.'" data-currency_symbol = true>'.$row->amount.'</span>';
                })
                ->addColumn('action', '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary view_payment" data-href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'viewPayment\'], [$id]) }}"><i class="fas fa-eye"></i> @lang("messages.view")
                    </button>
                    @if((new App\Utils\ModuleUtil)->getModuleData("MKamel_checkTreeAccountingDefined") == false)
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info edit_payment" 
                    data-href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'edit\'], [$id]) }}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    &nbsp; <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_payment" 
                    data-href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'destroy\'], [$id]) }}"
                    ><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</button> 
                    @endif
                    @if(!empty($document))<a href="{{asset("/uploads/documents/" . $document)}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-accent" download=""><i class="fa fa-download"></i> @lang("purchase.download_document")</a>@endif')
                ->rawColumns(['amount', 'method', 'action'])
                ->make(true);
        }
    }
}
