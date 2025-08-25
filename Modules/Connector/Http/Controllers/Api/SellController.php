<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Unit;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Transformers\SellResource;

/**
 * @group Sales management
 * @authenticated
 *
 * APIs for managing sales
 */
class SellController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $cashRegisterUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->notificationUtil = $notificationUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', ];
        parent::__construct();
    }


    public function index()
    {
        //TODO::order by
        $user = Auth::user();
        $business_id = $user->business_id;
        $is_admin = $this->businessUtil->is_admin($user, $business_id);

        if (! $is_admin && ! auth()->user()->hasAnyPermission(['sell.view', 'direct_sell.access', 'direct_sell.view', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            abort(403, 'Unauthorized action.');
        }

        $filters = request()->only(['location_id', 'contact_id', 'payment_status', 'start_date', 'end_date', 'user_id', 'service_staff_id', 'only_subscriptions', 'per_page', 'shipping_status', 'order_by_date', 'source', 'status']);

        $with = ['sell_lines', 'payment_lines', 'contact'];
        $query = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell');

        if (! empty(request()->input('send_purchase_details')) && request()->input('send_purchase_details') == 1) {
            $with[] = 'sell_lines.sell_line_purchase_lines';
            $with[] = 'sell_lines.sell_line_purchase_lines.purchase_line';
        }

        $query->with($with);

        $permitted_locations = $user->permitted_locations($business_id);
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (! $user->can('direct_sell.view')) {
            $query->where(function ($q) use ($user) {
                if ($user->hasAnyPermission(['view_own_sell_only', 'access_own_shipping'])) {
                    $q->where('transactions.created_by', $user->id);
                }

                //if user is commission agent display only assigned sells
                if ($user->hasAnyPermission(['view_commission_agent_sell', 'access_commission_agent_shipping'])) {
                    $q->orWhere('transactions.commission_agent', $user->id);
                }
            });
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] == 'final' || $filters['status'] == 'draft') {
                $query->where('transactions.status', $filters['status']);
            } elseif ($filters['status'] == 'quotation') {
                $query->where('transactions.sub_status', 'quotation');
            } elseif ($filters['status'] == 'proforma') {
                $query->where('transactions.sub_status', 'proforma');
            }
        }

        if (! empty($filters['location_id'])) {
            $query->where('transactions.location_id', $filters['location_id']);
        }

        if (! empty($filters['contact_id'])) {
            $query->where('transactions.contact_id', $filters['contact_id']);
        }

        $payment_status = [];
        if (! empty($filters['payment_status'])) {
            $payment_status = explode(',', $filters['payment_status']);
        }

        if (! $is_admin) {
            $payment_status_arr = [];
            if (auth()->user()->can('view_paid_sells_only')) {
                $payment_status_arr[] = 'paid';
            }

            if (auth()->user()->can('view_due_sells_only')) {
                $payment_status_arr[] = 'due';
            }

            if (auth()->user()->can('view_partial_sells_only')) {
                $payment_status_arr[] = 'partial';
            }

            if (empty($payment_status_arr)) {
                if (auth()->user()->can('view_overdue_sells_only')) {
                    $query->OverDue();
                }
            } else {
                if (auth()->user()->can('view_overdue_sells_only')) {
                    $query->where(function ($q) use ($payment_status_arr) {
                        $q->whereIn('transactions.payment_status', $payment_status_arr)
                        ->orWhere(function ($qr) {
                            $qr->OverDue();
                        });
                    });
                } else {
                    $query->whereIn('transactions.payment_status', $payment_status_arr);
                }
            }
        }

        if (! empty($payment_status)) {
            $query->where(function ($q) use ($payment_status) {
                $is_overdue = false;
                if (in_array('overdue', $payment_status)) {
                    $is_overdue = true;
                    $key = array_search('overdue', $payment_status);
                    unset($payment_status[$key]);
                }

                if (! empty($payment_status)) {
                    $q->whereIn('transactions.payment_status', $payment_status);
                }

                if ($is_overdue) {
                    $q->orWhere(function ($qr) {
                        $qr->whereIn('transactions.payment_status', ['due', 'partial'])
                            ->whereNotNull('transactions.pay_term_number')
                            ->whereNotNull('transactions.pay_term_type')
                            ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
                    });
                }
            });
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('transactions.transaction_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('transactions.transaction_date', '<=', $filters['end_date']);
        }

        if (! empty($filters['order_by_date'])) {
            $order_by_date = in_array($filters['order_by_date'], ['asc', 'desc']) ? $filters['order_by_date'] : 'desc';
            $query->orderBy('transactions.transaction_date', $order_by_date);
        }

        if (! empty($filters['user_id'])) {
            $query->where('transactions.created_by', $filters['user_id']);
        }

        if (! empty($filters['service_staff_id'])) {
            $query->where('transactions.res_waiter_id', $filters['service_staff_id']);
        }

        if (! empty($filters['shipping_status'])) {
            $query->where('transactions.shipping_status', $filters['shipping_status']);
        }

        if (! empty($filters['only_subscriptions']) && $filters['only_subscriptions'] == 1) {
            $query->where(function ($q) {
                $q->whereNotNull('transactions.recur_parent_id')
                    ->orWhere('transactions.is_recurring', 1);
            });
        }

        if (! empty($filters['source'])) {
            //only exception for woocommerce
            if ($filters['source'] == 'woocommerce') {
                $query->whereNotNull('transactions.woocommerce_order_id');
            } else {
                $query->where('transactions.source', $filters['source']);
            }
        }

        $perPage = ! empty($filters['per_page']) ? $filters['per_page'] : $this->perPage;
        if ($perPage == -1) {
            $sells = $query->get();
        } else {
            $sells = $query->paginate($perPage);
            $sells->appends(request()->query());
        }

        return SellResource::collection($sells);
    }

    public function show($sell_ids)
    {
        $user = Auth::user();

        $business_id = $user->business_id;
        $sell_ids = explode(',', $sell_ids);

        $query = Transaction::where('business_id', $business_id)
                        ->whereIn('id', $sell_ids);

        $with = ['sell_lines', 'payment_lines'];

        if (! empty(request()->input('send_purchase_details')) && request()->input('send_purchase_details') == 1) {
            $with[] = 'sell_lines.sell_line_purchase_lines';
            $with[] = 'sell_lines.sell_line_purchase_lines.purchase_line';
        }

        $sells = $query->with($with)
                    ->get();

        return SellResource::collection($sells);
    }


    public function store(Request $request)
    {
        //TODO::Check customer credit limit
        try {
            $sells = $request->input('sells');
            $user = Auth::user();

            $business_id = $user->business_id;
            $business = Business::find($business_id);
            $commsn_agnt_setting = $business->sales_cmsn_agnt;
            $output = [];

            if (empty($sells) || ! is_array($sells)) {
                throw new \Exception('Invalid form data');
            }

            foreach ($sells as $sell_data) {
                try {
                    DB::beginTransaction();
                    $sell_data['business_id'] = $user->business_id;
                    $input = $this->__formatSellData($sell_data);

                    //TODO: temporarily used false to bypass the check, bcz of session issue in can_access_this_location function
                    //Check if location allowed
                    if (false && ! $user->can_access_this_location($input['location_id'])) {
                        throw new \Exception('User not allowed to access location with id '.$input['location_id']);
                    }

                    if (empty($input['products'])) {
                        throw new \Exception('No products added');
                    }

                    $discount = ['discount_type' => $input['discount_type'],
                        'discount_amount' => $input['discount_amount'],
                    ];
                    $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount, false);

                    if ($commsn_agnt_setting == 'logged_in_user') {
                        $input['commission_agent'] = $user->id;
                    }

                    $transaction = $this->transactionUtil->createSellTransaction($user->business_id, $input, $invoice_total, $user->id, false);

                    $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], false, null, [], false);
                    //Add change return
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_return'] = 1;
                    $input['payment'][] = $change_return;

                    if (! empty($input['payment']) && $transaction->is_suspend == 0) {
                        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment'], $business_id, $user->id, false);
                    }

                    if ($input['status'] == 'final') {
                        //Check for final and do some processing.
                        //update product stock
                        foreach ($input['products'] as $product) {
                            $decrease_qty = $product['quantity'];
                            if (! empty($product['base_unit_multiplier'])) {
                                $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                            }

                            if ($product['enable_stock']) {
                                $this->productUtil->decreaseProductQuantity(
                                    $product['product_id'],
                                    $product['variation_id'],
                                    $input['location_id'],
                                    $decrease_qty
                                );
                            }

                            if ($product['product_type'] == 'combo') {
                                //Decrease quantity of combo as well.
                                $this->productUtil
                                    ->decreaseProductQuantityCombo(
                                        $product['combo'],
                                        $input['location_id']
                                    );
                            }
                        }

                        //Update payment status
                        $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                        if ($business->enable_rp == 1) {
                            $redeemed = ! empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0;
                            $this->transactionUtil->updateCustomerRewardPoints($transaction->contact_id, $transaction->rp_earned, 0, $redeemed);
                        }

                        //Allocate the quantity from purchase and add mapping of
                        //purchase & sell lines in
                        //transaction_sell_lines_purchase_lines table
                        $business_details = $this->businessUtil->getDetails($business_id);
                        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                        $business_info = ['id' => $business_id,
                            'accounting_method' => $business->accounting_method,
                            'location_id' => $input['location_id'],
                            'pos_settings' => $pos_settings,
                        ];
                        $this->transactionUtil->mapPurchaseSell($business_info, $transaction->sell_lines, 'purchase');

                        //Auto send notification
                        $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);

                        $client = $this->getClient();

                        $this->transactionUtil->activityLog($transaction, 'added', null, ['from_api' => $client]);
                    }

                    $transaction->invoice_url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);
                    $transaction->payment_link = $this->transactionUtil->getInvoicePaymentLink($transaction->id, $business_id);

                    DB::commit();
                    $output[] = $transaction;
                } catch (ModelNotFoundException $e) {
                    DB::rollback();

                    \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                    $output[] = $this->modelNotFoundExceptionResult($e);
                } catch (\Exception $e) {
                    DB::rollback();

                    \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                    $output[] = $this->otherExceptions($e);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output[] = $this->otherExceptions($e);
        }

        return $output;
    }


    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $business_id = $user->business_id;
            $business = Business::find($business_id);

            $sell_data = $request->input();
            $sell_data['business_id'] = $user->business_id;

            $transaction_before = Transaction::where('business_id', $user->business_id)->with(['payment_lines'])
                                    ->findOrFail($id);

            //Check if location allowed
            if (! $user->can_access_this_location($transaction_before->location_id)) {
                throw new \Exception('User not allowed to access location with id '.$input['location_id']);
            }

            $status_before = $transaction_before->status;
            $rp_earned_before = $transaction_before->rp_earned;
            $rp_redeemed_before = $transaction_before->rp_redeemed;

            $sell_data['location_id'] = $transaction_before->location_id;
            $input = $this->__formatSellData($sell_data, $transaction_before);
            $discount = ['discount_type' => $input['discount_type'],
                'discount_amount' => $input['discount_amount'],
            ];
            $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

            //Begin transaction
            DB::beginTransaction();

            $transaction = $this->transactionUtil->updateSellTransaction($transaction_before, $business_id, $input, $invoice_total, $user->id, false);

            //Update Sell lines
            $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before, [], false);
            if (! empty($input['payment']) && $transaction->is_suspend == 0) {
                $change_return = $this->dummyPaymentLine;
                $change_return['amount'] = $input['change_return'];
                $change_return['is_return'] = 1;
                if (! empty($input['change_return_id'])) {
                    $change_return['id'] = $input['change_return_id'];
                }
                $input['payment'][] = $change_return;

                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment'], $business_id, $user->id, false);
            }

            if ($business->enable_rp == 1) {
                $this->transactionUtil->updateCustomerRewardPoints($transaction->contact_id, $transaction->rp_earned, $rp_earned_before, $transaction->rp_redeemed, $rp_redeemed_before);
            }

            //Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            //Update product stock
            $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input, false);

            //Allocate the quantity from purchase and add mapping of
            //purchase & sell lines in
            //transaction_sell_lines_purchase_lines table
            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $business = ['id' => $business_id,
                'accounting_method' => $business->accounting_method,
                'location_id' => $input['location_id'],
                'pos_settings' => $pos_settings,
            ];
            $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);

            $updated_transaction = Transaction::where('business_id', $user->business_id)->with(['payment_lines'])
                                    ->findOrFail($id);

            $updated_transaction->invoice_url = $this->transactionUtil->getInvoiceUrl($updated_transaction->id, $business_id);
            $updated_transaction->payment_link = $this->transactionUtil->getInvoicePaymentLink($updated_transaction->id, $business_id);

            $output = $updated_transaction;

            $client = $this->getClient();

            $this->transactionUtil->activityLog($updated_transaction, 'edited', $transaction_before, ['from_api' => $client]);
            DB::commit();
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            $output = $this->modelNotFoundExceptionResult($e);
        } catch (\Exception $e) {
            DB::rollback();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = $this->otherExceptions($e);
        }

        return $output;
    }

    private function __getValue($key, $data, $obj, $default = null, $db_key = null)
    {
        $value = $default;

        if (isset($data[$key])) {
            $value = $data[$key];
        } elseif (! empty($obj)) {
            $key = ! empty($db_key) ? $db_key : $key;
            $value = $obj->$key;
        }

        return $value;
    }

    /**
     * Formats input form data to sell data
     *
     * @param  array  $data
     * @return array
     */
    private function __formatSellData($data, $transaction = null)
    {
        $business_id = $data['business_id'];
        $location = BusinessLocation::where('business_id', $business_id)
                                    ->findOrFail($data['location_id']);

        $customer_id = $this->__getValue('contact_id', $data, $transaction, null);
        $contact = Contact::where('business_id', $data['business_id'])
                            ->whereIn('type', ['customer', 'both'])
                            ->findOrFail($customer_id);

        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact->id);
        $customer_group_id = (empty($cg) || empty($cg->id)) ? null : $cg->id;
        $formated_data = [
            'business_id' => $business_id,
            'location_id' => $location->id,
            'contact_id' => $contact->id,
            'customer_group_id' => $customer_group_id,
            'transaction_date' => $this->__getValue('transaction_date', $data,
                                $transaction, \Carbon::now()->toDateTimeString()),
            'invoice_no' => $this->__getValue('invoice_no', $data, $transaction, null, 'invoice_no'),
            'source' => $this->__getValue('source', $data, $transaction, null, 'source'),
            'status' => $this->__getValue('status', $data, $transaction, 'final'),
            'sub_status' => $this->__getValue('sub_status', $data, $transaction, null),
            'sale_note' => $this->__getValue('sale_note', $data, $transaction),
            'staff_note' => $this->__getValue('staff_note', $data, $transaction),
            'commission_agent' => $this->__getValue('commission_agent',
                                    $data, $transaction),
            'shipping_details' => $this->__getValue('shipping_details',
                                    $data, $transaction),
            'shipping_address' => $this->__getValue('shipping_address',
                                $data, $transaction),
            'shipping_status' => $this->__getValue('shipping_status', $data, $transaction),
            'delivered_to' => $this->__getValue('delivered_to', $data, $transaction),
            'shipping_charges' => $this->__getValue('shipping_charges', $data,
                $transaction, 0),
            'exchange_rate' => $this->__getValue('exchange_rate', $data, $transaction, 1),
            'selling_price_group_id' => $this->__getValue('selling_price_group_id', $data, $transaction),
            'pay_term_number' => $this->__getValue('pay_term_number', $data, $transaction),
            'pay_term_type' => $this->__getValue('pay_term_type', $data, $transaction),
            'is_recurring' => $this->__getValue('is_recurring', $data, $transaction, 0),
            'recur_interval' => $this->__getValue('recur_interval', $data, $transaction),
            'recur_interval_type' => $this->__getValue('recur_interval_type', $data, $transaction),
            'subscription_repeat_on' => $this->__getValue('subscription_repeat_on', $data, $transaction),
            'subscription_no' => $this->__getValue('subscription_no', $data, $transaction),
            'recur_repetitions' => $this->__getValue('recur_repetitions', $data, $transaction, 0),
            'order_addresses' => $this->__getValue('order_addresses', $data, $transaction),
            'rp_redeemed' => $this->__getValue('rp_redeemed', $data, $transaction, 0),
            'rp_redeemed_amount' => $this->__getValue('rp_redeemed_amount', $data, $transaction, 0),
            'is_created_from_api' => 1,
            'types_of_service_id' => $this->__getValue('types_of_service_id', $data, $transaction),
            'packing_charge' => $this->__getValue('packing_charge', $data, $transaction, 0),
            'packing_charge_type' => $this->__getValue('packing_charge_type', $data, $transaction),
            'service_custom_field_1' => $this->__getValue('service_custom_field_1', $data, $transaction),
            'service_custom_field_2' => $this->__getValue('service_custom_field_2', $data, $transaction),
            'service_custom_field_3' => $this->__getValue('service_custom_field_3', $data, $transaction),
            'service_custom_field_4' => $this->__getValue('service_custom_field_4', $data, $transaction),
            'service_custom_field_5' => $this->__getValue('service_custom_field_5', $data, $transaction),
            'service_custom_field_6' => $this->__getValue('service_custom_field_6', $data, $transaction),
            'round_off_amount' => $this->__getValue('round_off_amount', $data, $transaction),
            'res_table_id' => $this->__getValue('table_id', $data, $transaction, null, 'res_table_id'),
            'res_waiter_id' => $this->__getValue('service_staff_id', $data, $transaction, null, 'res_waiter_id'),
            'change_return' => $this->__getValue('change_return', $data, $transaction, 0),
            'change_return_id' => $this->__getValue('change_return_id', $data, $transaction, null),
            'is_quotation' => $this->__getValue('is_quotation', $data, $transaction, 0),
            'is_suspend' => $this->__getValue('is_suspend', $data, $transaction, 0),
        ];

        //Generate reference number
        if (! empty($formated_data['is_recurring'])) {
            //Update reference count
            $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription', $business_id);
            $formated_data['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count, $business_id);
        }

        $sell_lines = [];
        $subtotal = 0;

        if (! empty($data['products'])) {
            foreach ($data['products'] as $product_data) {
                $sell_line = null;
                if (! empty($product_data['sell_line_id'])) {
                    $sell_line = TransactionSellLine::findOrFail($product_data['sell_line_id']);
                }

                $product_id = $this->__getValue('product_id', $product_data, $sell_line);
                $variation_id = $this->__getValue('variation_id', $product_data, $sell_line);
                $product = Product::where('business_id', $business_id)
                                ->with(['variations'])
                                ->findOrFail($product_id);

                $variation = $product->variations->where('id', $variation_id)->first();

                //Calculate line discount
                $unit_price = $this->__getValue('unit_price', $product_data, $sell_line, $variation->sell_price_inc_tax, 'unit_price_before_discount');

                $discount_amount = $this->__getValue('discount_amount', $product_data, $sell_line, 0, 'line_discount_amount');

                $line_discount = $discount_amount;
                $line_discount_type = $this->__getValue('discount_type', $product_data, $sell_line, 'fixed', 'line_discount_type');

                if ($line_discount_type == 'percentage') {
                    $line_discount = $this->transactionUtil->calc_percentage($unit_price, $discount_amount);
                }
                $discounted_price = $unit_price - $line_discount;

                //calculate line tax
                $item_tax = 0;
                $unit_price_inc_tax = $discounted_price;
                $tax_id = $this->__getValue('tax_rate_id', $product_data, $sell_line, null, 'tax_id');
                if (! empty($tax_id)) {
                    $tax = TaxRate::where('business_id', $business_id)
                                ->findOrFail($tax_id);

                    $item_tax = $this->transactionUtil->calc_percentage($discounted_price, $tax->amount);
                    $unit_price_inc_tax += $item_tax;
                }

                $formated_sell_line = [
                    'product_id' => $product->id,
                    'variation_id' => $variation->id,
                    'product_type' => $product->type,
                    'unit_price' => $unit_price,
                    'line_discount_type' => $line_discount_type,
                    'line_discount_amount' => $discount_amount,
                    'tax_id' => $tax_id,
                    'item_tax' => $item_tax,
                    'sell_line_note' => $this->__getValue('note', $product_data, $sell_line, null, 'sell_line_note'),
                    'enable_stock' => $product->enable_stock,
                    'quantity' => $this->__getValue('quantity', $product_data,
                                        $sell_line, 0),
                    'product_unit_id' => $product->unit_id,
                    'sub_unit_id' => $this->__getValue('sub_unit_id', $product_data,
                                        $sell_line),
                    'unit_price_inc_tax' => $unit_price_inc_tax,
                    'res_service_staff_id' => $this->__getValue('res_service_staff_id', $product_data,
                    $sell_line),
                ];
                if (! empty($sell_line)) {
                    $formated_sell_line['transaction_sell_lines_id'] = $sell_line->id;
                }

                if (($formated_sell_line['product_unit_id'] != $formated_sell_line['sub_unit_id']) && ! empty($formated_sell_line['sub_unit_id'])) {
                    $sub_unit = Unit::where('business_id', $business_id)
                                    ->findOrFail($formated_sell_line['sub_unit_id']);
                    $formated_sell_line['base_unit_multiplier'] = $sub_unit->base_unit_multiplier;
                } else {
                    $formated_sell_line['base_unit_multiplier'] = 1;
                }

                //Combo product
                if ($product->type == 'combo') {
                    $combo_variations = $this->productUtil->calculateComboDetails($location->id, $variation->combo_variations);
                    foreach ($combo_variations as $key => $value) {
                        $combo_variations[$key]['quantity'] = $combo_variations[$key]['qty_required'] * $formated_sell_line['quantity'] * $formated_sell_line['base_unit_multiplier'];
                    }

                    $formated_sell_line['combo'] = $combo_variations;
                }

                $line_total = $unit_price_inc_tax * $formated_sell_line['quantity'];

                $sell_lines[] = $formated_sell_line;

                $subtotal += $line_total;
            }
        }

        $formated_data['products'] = $sell_lines;

        //calculate sell discount and tax
        $order_discount_amount = $this->__getValue('discount_amount', $data, $transaction, 0);
        $order_discount_type = $this->__getValue('discount_type', $data, $transaction, 'fixed');
        $order_discount = $order_discount_amount;
        if ($order_discount_type == 'percentage') {
            $order_discount = $this->transactionUtil->calc_percentage($subtotal, $order_discount_amount);
        }
        $discounted_total = $subtotal - $order_discount;

        //calculate line tax
        $order_tax = 0;
        $final_total = $discounted_total;
        $order_tax_id = $this->__getValue('tax_rate_id', $data, $transaction);
        if (! empty($order_tax_id)) {
            $tax = TaxRate::where('business_id', $business_id)
                        ->findOrFail($order_tax_id);

            $order_tax = $this->transactionUtil->calc_percentage($discounted_total, $tax->amount);
            $final_total += $order_tax;
        }

        $formated_data['discount_amount'] = $order_discount_amount;
        $formated_data['discount_type'] = $order_discount_type;
        $formated_data['tax_rate_id'] = $order_tax_id;
        $formated_data['tax_calculation_amount'] = $order_tax;

        $final_total += $formated_data['shipping_charges'];

        if (! empty($formated_data['packing_charge']) && ! empty($formated_data['types_of_service_id'])) {
            $final_total += $formated_data['packing_charge'];
        }

        $formated_data['final_total'] = $final_total;

        $payments = [];
        if (! empty($data['payments'])) {
            foreach ($data['payments'] as $payment_data) {
                $transaction_payment = null;
                if (! empty($payment_data['payment_id'])) {
                    $transaction_payment = TransactionPayment::findOrFail($payment_data['payment_id']);
                }
                $payment = [
                    'amount' => $this->__getValue('amount', $payment_data, $transaction_payment),
                    'method' => $this->__getValue('method', $payment_data, $transaction_payment),
                    'account_id' => $this->__getValue('account_id', $payment_data, $transaction_payment),
                    'card_number' => $this->__getValue('card_number', $payment_data, $transaction_payment),
                    'card_holder_name' => $this->__getValue('card_holder_name', $payment_data, $transaction_payment),
                    'card_transaction_number' => $this->__getValue('card_transaction_number', $payment_data, $transaction_payment),
                    'card_type' => $this->__getValue('card_type', $payment_data, $transaction_payment),
                    'card_month' => $this->__getValue('card_month', $payment_data, $transaction_payment),
                    'card_year' => $this->__getValue('card_year', $payment_data, $transaction_payment),
                    'card_security' => $this->__getValue('card_security', $payment_data, $transaction_payment),
                    'cheque_number' => $this->__getValue('cheque_number', $payment_data, $transaction_payment),
                    'bank_account_number' => $this->__getValue('bank_account_number', $payment_data, $transaction_payment),
                    'transaction_no_1' => $this->__getValue('transaction_no_1', $payment_data, $transaction_payment),
                    'transaction_no_2' => $this->__getValue('transaction_no_2', $payment_data, $transaction_payment),
                    'transaction_no_3' => $this->__getValue('transaction_no_3', $payment_data, $transaction_payment),
                    'note' => $this->__getValue('note', $payment_data, $transaction_payment),
                ];
                if (! empty($transaction_payment)) {
                    $payment['payment_id'] = $transaction_payment->id;
                }

                $payments[] = $payment;
            }

            $formated_data['payment'] = $payments;
        }

        return $formated_data;
    }

    /**
     * Delete Sell
     *
     * @urlParam sell required id of the sell to be deleted
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            //Begin transaction
            DB::beginTransaction();

            $output = $this->transactionUtil->deleteSale($business_id, $id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output['success'] = false;
            $output['msg'] = trans('messages.something_went_wrong');
        }

        return $output;
    }

    /**
     * Update shipping status
     *
     * @bodyParam id int required id of the sale
     * @bodyParam shipping_status string ('ordered', 'packed', 'shipped', 'delivered', 'cancelled') Example:ordered
     * @bodyParam delivered_to string Name of the consignee
     */
    public function updateSellShippingStatus(Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->business_id;

            $sell_id = $request->input('id');
            $shipping_status = $request->input('shipping_status');
            $delivered_to = $request->input('delivered_to');
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            if (array_key_exists($shipping_status, $shipping_statuses)) {
                Transaction::where('business_id', $business_id)
                    ->where('id', $sell_id)
                    ->where('type', 'sell')
                    ->update(['shipping_status' => $shipping_status, 'delivered_to' => $delivered_to]);
            } else {
                return $this->otherExceptions('Invalid shipping status');
            }

            return $this->respond(['success' => 1,
                'msg' => trans('lang_v1.updated_success'),
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return $this->otherExceptions($e);
        }
    }


    public function addSellReturn(Request $request)
    {
        try {
            $input = $request->except('_token');

            if (! empty($input['products'])) {
                $user = Auth::user();

                $business_id = $user->business_id;

                DB::beginTransaction();

                $output = $this->transactionUtil->addSellReturn($input, $business_id, $user->id);

                DB::commit();
            }
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            $output = $this->modelNotFoundExceptionResult($e);
        } catch (\Exception $e) {
            DB::rollback();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = $this->otherExceptions($e);
        }

        return $output;
    }

 
    public function listSellReturn()
    {
        $filters = request()->input();
        $user = Auth::user();
        $business_id = $user->business_id;

        $sell_id = request()->input('sell_id');
        $query = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell_return')
                            ->where('status', 'final')
                            ->with(['payment_lines', 'return_parent_sell', 'return_parent_sell.sell_lines'])
                            ->select('transactions.*');

        $permitted_locations = $user->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        if (! empty($sell_id)) {
            $query->where('return_parent_id', $sell_id);
        }

        $perPage = ! empty($filters['per_page']) ? $filters['per_page'] : $this->perPage;
        if ($perPage == -1) {
            $sell_returns = $query->get();
        } else {
            $sell_returns = $query->paginate($perPage);
            $sell_returns->appends(request()->query());
        }

        return SellResource::collection($sell_returns);
    }
}