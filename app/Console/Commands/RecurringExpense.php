<?php

namespace App\Console\Commands;

use App\Transaction;
use App\User;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecurringExpense extends Command
{
    /**
     * Utility instances.
     */
    protected TransactionUtil $transactionUtil;
    protected NotificationUtil $notificationUtil;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:generateRecurringExpense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates recurring expenses if enabled';

    /**
     * Create a new command instance.
     */
    public function __construct(
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil
    ) {
        parent::__construct();
        $this->transactionUtil = $transactionUtil;
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
                ->where('type', 'expense')
                ->whereNull('recur_stopped_on')
                ->whereNotNull('recur_interval')
                ->whereNotNull('recur_interval_type')
                ->with(['recurring_invoices', 'business'])
                ->get();

            foreach ($transactions as $transaction) {
                date_default_timezone_set($transaction->business->time_zone);

                try {
                    $count = $transaction->recurring_invoices->count();

                    if (!empty($transaction->recur_repetitions) && $count >= $transaction->recur_repetitions) {
                        continue;
                    }

                    $lastGenerated = $count > 0
                        ? $transaction->recurring_invoices->max('transaction_date')
                        : $transaction->transaction_date;

                    if (!empty($lastGenerated)) {
                        $lastGenerated = \Carbon\Carbon::parse($lastGenerated->format('Y-m-d'));
                        $today = \Carbon\Carbon::parse(now()->format('Y-m-d'));

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
                            default:
                                $diff = 0;
                        }

                        if ($diff === 0 || $diff % $transaction->recur_interval !== 0) {
                            continue;
                        }
                    }

                    DB::beginTransaction();

                    $expense = $this->transactionUtil->createRecurringExpense($transaction);

                    $createdBy = User::find($transaction->created_by);
                    $this->notificationUtil->recurringExpenseNotification($createdBy, $expense);

                    if ($createdBy->id !== $transaction->business->owner_id) {
                        $admin = User::find($transaction->business->owner_id);
                        $this->notificationUtil->recurringExpenseNotification($admin, $expense);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::emergency("RecurringExpense Error: File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");
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
