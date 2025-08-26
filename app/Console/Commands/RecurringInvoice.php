<?php

namespace App\Console\Commands;

use App\Contact;
use App\Transaction;
use App\User;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecurringInvoice extends Command
{
    /**
     * Utilities
     */
    protected TransactionUtil $transactionUtil;
    protected ProductUtil $productUtil;
    protected NotificationUtil $notificationUtil;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:generateSubscriptionInvoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates subscribed invoices if enabled';

    /**
     * Create a new command instance.
     */
    public function __construct(
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil,
        NotificationUtil $notificationUtil
    ) {
        parent::__construct();
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->notificationUtil = $notificationUtil;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $transactions = Transaction::where('is_recurring', 1)
                ->where('type', 'sell')
                ->where('status', 'final')
                ->whereNull('recur_stopped_on')
                ->whereNotNull('recur_interval')
                ->whereNotNull('recur_interval_type')
                ->with(['recurring_invoices', 'sell_lines', 'business', 'sell_lines.product'])
                ->get();

            foreach ($transactions as $transaction) {
                date_default_timezone_set($transaction->business->time_zone);

                try {
                    if (
                        !empty($transaction->business->enabled_modules) &&
                        !in_array('subscription', $transaction->business->enabled_modules)
                    ) {
                        continue;
                    }

                    $noOfGenerated = $transaction->recurring_invoices->count();
                    if (!empty($transaction->recur_repetitions) && $noOfGenerated >= $transaction->recur_repetitions) {
                        continue;
                    }

                    $lastGenerated = $noOfGenerated > 0
                        ? $transaction->recurring_invoices->max('transaction_date')
                        : $transaction->transaction_date;

                    if (!empty($lastGenerated)) {
                        $lastGenerated = \Carbon\Carbon::parse($lastGenerated->format('Y-m-d'));
                        $today = \Carbon\Carbon::parse(now()->format('Y-m-d'));
                        $diff = 0;

                        switch ($transaction->recur_interval_type) {
                            case 'days':
                                $diff = $lastGenerated->diffInDays($today);
                                break;
                            case 'months':
                                if (!empty($transaction->subscription_repeat_on)) {
                                    $lastGenerated = \Carbon\Carbon::parse(
                                        $lastGenerated->format('Y-m') . '-' . $transaction->subscription_repeat_on
                                    );
                                }
                                $diff = $lastGenerated->diffInMonths($today);
                                break;
                            case 'years':
                                $diff = $lastGenerated->diffInYears($today);
                                break;
                        }

                        if ($diff === 0 || $diff % $transaction->recur_interval !== 0) {
                            continue;
                        }
                    }

                    // Check stock availability
                    $saveAsDraft = false;
                    $outOfStock = null;

                    foreach ($transaction->sell_lines as $line) {
                        if ($line->product->enable_stock) {
                            $currentStock = $this->productUtil->getCurrentStock($line->variation_id, $transaction->location_id);
                            if ($currentStock < $line->quantity) {
                                $saveAsDraft = true;
                                $outOfStock = $line->product->name . ' (' . $line->product->sku . ')';
                                break;
                            }
                        }
                    }

                    DB::beginTransaction();

                    $invoice = $this->transactionUtil->createRecurringInvoice($transaction, $saveAsDraft);

                    if ($invoice->status === 'final') {
                        foreach ($transaction->sell_lines as $line) {
                            $this->productUtil->decreaseProductQuantity(
                                $line->product_id,
                                $line->variation_id,
                                $transaction->location_id,
                                $line->quantity
                            );
                        }

                        $business = [
                            'id' => $transaction->business_id,
                            'accounting_method' => $transaction->business->accounting_method,
                            'location_id' => $transaction->location_id,
                        ];

                        $this->transactionUtil->mapPurchaseSell($business, $invoice->sell_lines, 'purchase');

                        $contact = Contact::find($invoice->contact_id);
                        $this->notificationUtil->autoSendNotification($transaction->business_id, 'new_sale', $invoice, $contact);
                    }

                    $invoice->out_of_stock_product = $outOfStock;
                    $invoice->subscription_no = $transaction->subscription_no;

                    $createdBy = User::find($transaction->created_by);
                    $this->notificationUtil->recurringInvoiceNotification($createdBy, $invoice);

                    if ($createdBy->id !== $transaction->business->owner_id) {
                        $admin = User::find($transaction->business->owner_id);
                        $this->notificationUtil->recurringInvoiceNotification($admin, $invoice);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::emergency("RecurringInvoice Error: File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");
                }
            }

            return 0;
        } catch (\Exception $e) {
            \Log::emergency("Command Error: File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");
            $this->error($e->getMessage());
            return 1;
        }
    }
}
