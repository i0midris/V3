<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\Events\TransactionPaymentDeleted;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Modules\Accounting\Entities\AccountingAccount;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class SellReturnController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $contactUtil;

    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ContactUtil $contactUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->join(
                    'transactions as T1',
                    'transactions.return_parent_id',
                    '=',
                    'T1.id'
                )
                ->leftJoin(
                    'transaction_payments AS TP',
                    'transactions.id',
                    '=',
                    'TP.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_return')
                ->where('transactions.status', 'final')
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'transactions.final_total',
                    'transactions.payment_status',
                    'bl.name as business_location',
                    'T1.invoice_no as parent_sale',
                    'T1.id as parent_sale_id',
                    DB::raw('SUM(TP.amount) as amount_paid')
                );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (! auth()->user()->can('access_sell_return') && auth()->user()->can('access_own_sell_return')) {
                $sells->where('transactions.created_by', request()->session()->get('user.id'));
            }

            // Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (! empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            // Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (! empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (! empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            $sells->groupBy('transactions.id');

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle" 
                        data-toggle="dropdown" aria-expanded="false">'.
                        __('messages.actions').
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="#" class="btn-modal" data-container=".view_modal" data-href="{{action(\'App\Http\Controllers\SellReturnController@show\', [$parent_sale_id])}}"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</a></li>
                        <li><a href="javascript:void(0)" class="zatca-link" data-transaction="{{$id}}"><i class="fa fa-paper-plane" aria-hidden="true"></i> Send To Zatca </a></li>
                        <li><a href="javascript:void(0)" data-container="#zatca-link-details" class="zatca-link-details-modal btn" data-transaction="{{$id}}"><i class="fa fa-paper-plane" aria-hidden="true"></i>Zatca Invoice Detail</a></li>
                        <li><a href="{{action(\'App\Http\Controllers\SellReturnController@add\', [$parent_sale_id])}}" ><i class="fa fa-edit" aria-hidden="true"></i> @lang("messages.edit")</a></li>
                        <li><a href="{{action(\'App\Http\Controllers\SellReturnController@destroy\', [$id])}}" class="delete_sell_return" ><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</a></li>
                        <li><a href="#" class="print-invoice" data-href="{{action(\'App\Http\Controllers\SellReturnController@printInvoice\', [$id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a></li>

                    @if($payment_status != "paid")
                        <li><a href="{{action(\'App\Http\Controllers\TransactionPaymentController@addPayment\', [$id])}}" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a></li>
                    @endif

                    <li><a href="{{action(\'App\Http\Controllers\TransactionPaymentController@show\', [$id])}}" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.view_payments")</a></li>
                    </ul>
                    </div>'
                )
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn('parent_sale', function ($row) {
                    return '<button type="button" class="btn btn-link btn-modal" data-container=".view_modal" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->parent_sale_id]).'">'.$row->parent_sale.'</button>';
                })
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    '<a href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'show\'], [$id])}}" class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;

                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="'.$due.'">'.$due.'</sapn>';
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view')) {
                            return action([\App\Http\Controllers\SellReturnController::class, 'show'], [$row->parent_sale_id]);
                        } else {
                            return '';
                        }
                    }, ])
                ->rawColumns(['final_total', 'action', 'parent_sale', 'payment_status', 'payment_due', 'name'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sell_return.index')->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function create()
     {
         if (!auth()->user()->can('sell.create')) {
             abort(403, 'Unauthorized action.');
         }

         $business_id = request()->session()->get('user.business_id');

         //Check if subscribed or not
         if (!$this->moduleUtil->isSubscribed($business_id)) {
             return $this->moduleUtil->expiredResponse(action([\App\Http\Controllers\SellReturnController::class, 'index']));
         }

         $business_locations = BusinessLocation::forDropdown($business_id);
         //$walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

         return view('sell_return.create')
             ->with(compact('business_locations'));
     }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
 public function add($id)
{
    if (!auth()->user()->can('access_sell_return') && !auth()->user()->can('access_own_sell_return')) {
        abort(403, 'Unauthorized action.');
    }

    $business_id = request()->session()->get('user.business_id');

    // Check subscription
    if (!$this->moduleUtil->isSubscribed($business_id)) {
        return $this->moduleUtil->expiredResponse();
    }

    // Fetch original sale transaction
    $sell = Transaction::where('business_id', $business_id)
        ->with([
            'sell_lines', 'location', 'return_parent', 'contact', 'tax',
            'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit'
        ])
        ->findOrFail($id);

    // If this is already a return, go back to the parent sale
    if ($sell->type === 'sell_return' && $sell->return_parent_id) {
        $sell = Transaction::where('business_id', $business_id)
            ->with([
                'sell_lines', 'location', 'contact', 'tax',
                'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit'
            ])
            ->findOrFail($sell->return_parent_id);
    }

    foreach ($sell->sell_lines as $key => $value) {
        if (!empty($value->sub_unit_id)) {
            $sell->sell_lines[$key] = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
        }

        $sell->sell_lines[$key]->formatted_qty = number_format($value->quantity, 4, '.', '');
    }

    $refund_accounts = AccountingAccount::where('business_id', $business_id)
        ->whereBetween('gl_code', [110101, 110299])
        ->pluck('name', 'id');

    return view('sell_return.add')->with(compact('sell', 'refund_accounts'));
}

public function store(Request $request)
{
    if (!auth()->user()->can('access_sell_return') && !auth()->user()->can('access_own_sell_return')) {
        abort(403, 'Unauthorized action.');
    }

    try {
        $input = $request->except('_token');
        if (empty($input['products'])) {
            throw new \Exception('No products selected for return.');
        }

        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');

        // Check subscription
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action([self::class, 'index']));
        }

        // Run accounting pre-check
        $output_acc = (new ModuleUtil)->getModuleData('MKamel_check555', [
            'request' => $request,
            'input' => $input,
        ]);

        if (!empty($output_acc['Accounting']['success']) && $output_acc['Accounting']['success'] == 0) {
            return redirect()->back()->with(['status' => $output_acc['Accounting']]);
        }

        DB::beginTransaction();

        // Get contact ID
        $contact_id = $input['contact_id'] ?? Transaction::find($input['transaction_id'])?->contact_id;

        if (!$contact_id) {
            throw new \Exception('Customer ID is missing or invalid.');
        }

        // Ensure contact is linked in accounting_accounts
        $customer_linked = DB::table('accounting_accounts')
            ->where('link_table', 'contacts')
            ->where('link_id', $contact_id)
            ->first();

        if (!$customer_linked) {
            \Log::warning('Accounting link missing. Creating new account for contact.', ['contact_id' => $contact_id]);

            DB::table('accounting_accounts')->insert([
                'link_table'   => 'contacts',
                'link_id'      => $contact_id,
                'business_id'  => $business_id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $customer_linked = DB::table('accounting_accounts')
                ->where('link_table', 'contacts')
                ->where('link_id', $contact_id)
                ->first();

            if (!$customer_linked) {
                throw new \Exception('Could not create or find linked customer account.');
            }
        }

        // Validate return quantities
        foreach ($input['products'] as $product) {
            $sell_line = TransactionSellLine::find($product['sell_line_id']);
            if (!$sell_line) {
                throw new \Exception("Sell line not found.");
            }

            $already_returned = $sell_line->quantity_returned ?? 0;
            $max_returnable = $sell_line->quantity - $already_returned;
            $requested = $this->transactionUtil->num_f($product['quantity']);

            if ($requested > $max_returnable) {
                return [
                    'success' => 0,
                    'msg' => __('lang_v1.return_quantity_exceeds', [
                        'product'   => $sell_line->product->name,
                        'available' => $max_returnable
                    ])
                ];
            }
        }

        // Create sell return
        $sell_transaction = Transaction::with('sell_lines')->findOrFail($input['transaction_id']);
        $input['total_sold_qty'] = $sell_transaction->sell_lines->sum('quantity');

        $sell_return = $this->transactionUtil->addSellReturn($input, $business_id, $user_id);

$orig_before_tax   = (float) ($sell_transaction->total_before_tax ?? 0);
$return_before_tax = (float) ($sell_return->total_before_tax ?? 0);

$ratio = $orig_before_tax > 0 
    ? max(0.0, min(1.0, $return_before_tax / $orig_before_tax)) 
    : 0.0;

$sell_return->commission_agent  = $sell_transaction->commission_agent ?? null;

// Positive proportional commission
$sell_return->commission_amount = round(((float) ($sell_transaction->commission_amount ?? 0)) * $ratio, 3);


        // Round financial fields to 3 decimals before saving
        $sell_return->final_total       = round((float) $sell_return->final_total, 3);
        $sell_return->tax_amount        = round((float) $sell_return->tax_amount, 3);
        $sell_return->total_before_tax  = round((float) $sell_return->total_before_tax, 3);
        $sell_return->save();

        $this->transactionUtil->payCustomerForReturn($sell_return, $business_id, $user_id);

        // Generate receipt
        $receipt = $this->receiptContent($business_id, $sell_return->location_id, $sell_return->id);

        DB::commit();

        // Post-store accounting integration
        if (!empty($output_acc['Accounting']['success']) && $output_acc['Accounting']['success'] == 1) {
            (new \App\Utils\ModuleUtil)->getModuleData('MKamel_store555', [
                'request'           => $request,
                'input'             => $input,
                'user_id'           => $user_id,
                'customer_linked'   => $customer_linked,
                'sell_return'       => $sell_return,
                'refund_account_id' => $request->input('refund_account_id'),
            ]);
        }

        return [
            'success' => 1,
            'msg'     => __('lang_v1.success'),
            'receipt' => $receipt,
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Sell Return Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return [
            'success' => 0,
            'msg'     => __('messages.something_went_wrong'),
        ];
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'return_parent',
                'tax',
                'sell_lines',
                'sell_lines.product',
                'sell_lines.variations',
                'sell_lines.sub_unit',
                'sell_lines.product',
                'sell_lines.product.unit',
                'location'
            );

        if (! auth()->user()->can('access_sell_return') && auth()->user()->can('access_own_sell_return')) {
            $sells->where('created_by', request()->session()->get('user.id'));
        }
        $sell = $query->first();

        foreach ($sell->sell_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }
        }

        $sell_taxes = [];
        if (! empty($sell->return_parent->tax)) {
            if ($sell->return_parent->tax->is_tax_group) {
                $sell_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->return_parent->tax, $sell->return_parent->tax_amount));
            } else {
                $sell_taxes[$sell->return_parent->tax->name] = $sell->return_parent->tax_amount;
            }
        }

        $total_discount = 0;
        if ($sell->return_parent->discount_type == 'fixed') {
            $total_discount = $sell->return_parent->discount_amount;
        } elseif ($sell->return_parent->discount_type == 'percentage') {
            $discount_percent = $sell->return_parent->discount_amount;
            if ($discount_percent == 100) {
                $total_discount = $sell->return_parent->total_before_tax;
            } else {
                $total_after_discount = $sell->return_parent->final_total - $sell->return_parent->tax_amount;
                $total_before_discount = $total_after_discount * 100 / (100 - $discount_percent);
                $total_discount = $total_before_discount - $total_after_discount;
            }
        }

        $activities = Activity::forSubject($sell->return_parent)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        return view('sell_return.show')
            ->with(compact('sell', 'sell_taxes', 'total_discount', 'activities'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
{
    if (! auth()->user()->can('access_sell_return') && ! auth()->user()->can('access_own_sell_return')) {
        abort(403, 'Unauthorized action.');
    }

    if (request()->ajax()) {
        try {
            $business_id = request()->session()->get('user.business_id');
            DB::beginTransaction();

            $query = Transaction::where('id', $id)
                ->where('business_id', $business_id)
                ->where('type', 'sell_return')
                ->with(['sell_lines', 'payment_lines']);

            if (! auth()->user()->can('access_sell_return') && auth()->user()->can('access_own_sell_return')) {
                $sells->where('created_by', request()->session()->get('user.id'));
            }

            $sell_return = $query->first();

            if (! empty($sell_return)) {
                $sell_lines = TransactionSellLine::where('transaction_id', $sell_return->return_parent_id)->get();
                $transaction_payments = $sell_return->payment_lines;

                // 🔑 Delete accounting journal entries first
                foreach ($transaction_payments as $payment) {
                    \Modules\Accounting\Utils\AccountingUtil::deleteJournalEntry('transaction_payments', $payment->id);
                }
                \Modules\Accounting\Utils\AccountingUtil::deleteJournalEntry('transactions', $sell_return->id);

                foreach ($sell_lines as $sell_line) {
                    if ($sell_line->quantity_returned > 0) {
                        $quantity_before = $this->transactionUtil->num_f($sell_line->quantity_returned);

                        $sell_line->quantity_returned = 0;
                        $sell_line->save();

                        $this->transactionUtil->updateQuantitySoldFromSellLine($sell_line, 0, $quantity_before);

                        $this->productUtil->updateProductQuantity(
                            $sell_return->location_id,
                            $sell_line->product_id,
                            $sell_line->variation_id,
                            0,
                            $quantity_before
                        );
                    }
                }

                $sell_return->delete();

                foreach ($transaction_payments as $payment) {
                    event(new TransactionPaymentDeleted($payment));
                }
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            } else {
                \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());
                $msg = __('messages.something_went_wrong');
            }

            $output = [
                'success' => 0,
                'msg' => $msg,
            ];
        }

        return $output;
    }
}


    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param  string  $printer_type  = null
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null
    ) {
        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];
    
        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);
    
        if ($location_details->print_receipt_on_invoice == 1) {
            $output['is_enabled'] = true;
    
            $invoice_layout = $this->businessUtil->invoiceLayout(
                $business_id,
                $location_details->invoice_layout_id
            );
    
            $receipt_printer_type = is_null($printer_type)
                ? $location_details->receipt_printer_type
                : $printer_type;
    
            $receipt_details = $this->transactionUtil->getReceiptDetails(
                $transaction_id,
                $location_id,
                $invoice_layout,
                $business_details,
                $location_details,
                $receipt_printer_type
            );
    
            // Load sell return lines with tax
            $raw_lines = \App\TransactionSellLine::where('transaction_id', $transaction_id)
                ->with(['product.brand', 'tax', 'variations', 'sub_unit'])
                ->get();
    
            // Group by product, variation, and sub_unit to avoid repetition
            $grouped_lines = [];
            foreach ($raw_lines as $line) {
                $key = $line->product_id . '_' . $line->variation_id . '_' . ($line->sub_unit_id ?? 0);
    
                if (!isset($grouped_lines[$key])) {
                    $grouped_lines[$key] = (object)[
                        'name' => $line->product->name ?? '',
                        'variation' => $line->variations->name ?? '',
                        'quantity' => $line->quantity,
                        'unit_price_exc_tax' => $line->unit_price ?? 0,
                        'unit_price_inc_tax' => $line->unit_price_inc_tax ?? 0,
                        'line_total' => $line->quantity * $line->unit_price_inc_tax,
                        'item_tax' => $line->item_tax ?? 0,
                        'tax' => $line->tax ?? null,
                        'units' => optional($line->sub_unit)->short_name ?? '',
                        'sub_sku' => $line->variations->sub_sku ?? '',
                        'brand' => optional($line->product->brand)->name ?? '',
                        'sell_line_note' => $line->sell_line_note ?? '',
                    ];
                } else {
                    $grouped_lines[$key]->quantity += $line->quantity;
                    $grouped_lines[$key]->line_total += $line->quantity * $line->unit_price_inc_tax;
                    $grouped_lines[$key]->item_tax += $line->item_tax ?? 0;
                }
            }
    
            $receipt_details->lines = array_values($grouped_lines);
            $output['print_title'] = $receipt_details->invoice_no;
    
            if ($receipt_printer_type === 'printer') {
                $output['print_type'] = 'printer';
                $output['printer_config'] = $this->businessUtil->printerConfig(
                    $business_id,
                    $location_details->printer_id
                );
                $output['data'] = $receipt_details;
            } else {
                $output['html_content'] = view('sell_return.receipt', compact('receipt_details'))->render();
            }
        }
    
        return $output;
    }
    
    /**
     * Prints invoice for sell
     *
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];

                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                    ->where('id', $transaction_id)
                    ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, 'browser');

                if (! empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                $output = ['success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Function to validate sell for sell return
     */
    public function validateInvoiceToReturn($invoice_no)
    {
        if (! auth()->user()->can('sell.create') && ! auth()->user()->can('direct_sell.access') && ! auth()->user()->can('view_own_sell_only')) {
            return ['success' => 0,
                'msg' => trans('lang_v1.permission_denied'),
            ];
        }

        $business_id = request()->session()->get('user.business_id');
        $query = Transaction::where('business_id', $business_id)
            ->where('invoice_no', $invoice_no);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (! auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('created_by', auth()->user()->id);
        }

        $sell = $query->first();

        if (empty($sell)) {
            return ['success' => 0,
                'msg' => trans('lang_v1.sell_not_found'),
            ];
        }

        return ['success' => 1,
            'redirect_url' => action([\App\Http\Controllers\SellReturnController::class, 'add'], [$sell->id]),
        ];
    }
}
