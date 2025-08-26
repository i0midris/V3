<?php

namespace App\Console\Commands;

use App\Business;
use App\Notifications\CustomerNotification;
use App\NotificationTemplate;
use App\Transaction;
use App\Utils\NotificationUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class AutoSendPaymentReminder extends Command
{
    /**
     * The Notification utility instance.
     */
    protected NotificationUtil $notificationUtil;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:autoSendPaymentReminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends payment reminders to customers with overdue sells if auto-send is enabled in the notification template.';

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationUtil $notificationUtil)
    {
        parent::__construct();
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

            $templates = NotificationTemplate::where('template_for', 'payment_reminder')
                ->where(function ($query): void {
                    $query->where('auto_send', 1)
                          ->orWhere('auto_send_sms', 1)
                          ->orWhere('auto_send_wa_notif', 1);
                })
                ->get();

            foreach ($templates as $template) {
                $business = Business::with('currency')
                    ->find($template->business_id);

                if (!$business) {
                    continue;
                }

                $data = [
                    'subject' => $template->subject ?? '',
                    'sms_body' => $template->sms_body ?? '',
                    'whatsapp_text' => $template->whatsapp_text ?? '',
                    'email_body' => $template->email_body ?? '',
                    'template_for' => 'payment_reminder',
                    'cc' => $template->cc ?? '',
                    'bcc' => $template->bcc ?? '',
                    'auto_send' => !empty($template->auto_send),
                    'auto_send_sms' => !empty($template->auto_send_sms),
                    'auto_send_wa_notif' => !empty($template->auto_send_wa_notif),
                ];

                $original = [
                    'email_body' => $data['email_body'],
                    'sms_body' => $data['sms_body'],
                    'subject' => $data['subject'],
                    'whatsapp_text' => $data['whatsapp_text'],
                ];

                if ($data['auto_send'] || $data['auto_send_sms']) {
                    $overdueSells = Transaction::where('transactions.business_id', $business->id)
                        ->where('transactions.type', 'sell')
                        ->where('transactions.status', 'final')
                        ->leftJoin('activity_log as a', function ($join): void {
                            $join->on('a.subject_id', '=', 'transactions.id')
                                 ->where('subject_type', Transaction::class)
                                 ->where('description', 'payment_reminder');
                        })
                        ->whereNull('a.id')
                        ->with(['contact', 'payment_lines'])
                        ->select('transactions.*')
                        ->groupBy('transactions.id')
                        ->OverDue()
                        ->get();

                    foreach ($overdueSells as $sell) {
                        $tagged = $this->notificationUtil->replaceTags($business, $original, $sell);

                        $data['email_body'] = $tagged['email_body'];
                        $data['sms_body'] = $tagged['sms_body'];
                        $data['subject'] = $tagged['subject'];
                        $data['whatsapp_text'] = $tagged['whatsapp_text'];
                        $data['email_settings'] = $business->email_settings ?? [];
                        $data['sms_settings'] = $business->sms_settings ?? [];

                        // Email Notification
                        if ($data['auto_send'] && !empty($sell->contact?->email)) {
                            try {
                                Notification::route('mail', [$sell->contact->email])
                                    ->notify(new CustomerNotification($data));

                                $this->notificationUtil->activityLog(
                                    $sell,
                                    'payment_reminder',
                                    null,
                                    ['email' => $sell->contact->email, 'is_automatic' => true],
                                    false
                                );
                            } catch (\Exception $e) {
                                \Log::emergency("Email error: File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");
                            }
                        }

                        // SMS Notification
                        if ($data['auto_send_sms'] && !empty($sell->contact?->mobile)) {
                            try {
                                $this->notificationUtil->sendSms($data);

                                $this->notificationUtil->activityLog(
                                    $sell,
                                    'payment_reminder',
                                    null,
                                    ['mobile' => $sell->contact->mobile, 'is_automatic' => true],
                                    false
                                );
                            } catch (\Exception $e) {
                                \Log::emergency("SMS error: File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");
                            }
                        }

                        // TODO: WhatsApp notification to be implemented.
                    }
                }
            }

            return 0;
        } catch (\Exception $e) {
            \Log::emergency("General error: File: {$e->getFile()} Line: {$e->getLine()} Message: {$e->getMessage()}");
            $this->error($e->getMessage());
            return 1;
        }
    }
}
