<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Mail\InquiryFollowupMail;
use App\Mail\AppointmentFollowupMail;
use App\Mail\GeneralReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessReminders extends Command
{
    protected $signature = 'reminders:process';
    protected $description = 'Process pending reminders and send emails';

    public function handle()
    {
        $this->info('Processing reminders...');

        // Fetch reminders with status PENDING and remind_at < now()
        $reminders = Reminder::with(['customer', 'agent', 'property', 'inquiry', 'appointment'])
            ->where('status', 'pending')
            ->where('remind_at', '<=', now())
            ->where('email_status', 'pending')
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('No pending reminders to process.');
            return 0;
        }

        $this->info("Found {$reminders->count()} reminders to process.");

        $successCount = 0;
        $failedCount = 0;

        foreach ($reminders as $reminder) {
            try {
                // Skip if no customer email
                if (!$reminder->customer || !$reminder->customer->email) {
                    $this->warn("Reminder ID {$reminder->id}: No customer email found. Skipping...");
                    $reminder->update([
                        'email_status' => 'failed',
                        'email_error' => 'No customer email address',
                    ]);
                    $failedCount++;
                    continue;
                }

                // Send email based on type
                $emailSent = $this->sendReminderEmail($reminder);

                if ($emailSent) {
                    // Success: Update reminder
                    $reminder->update([
                        'email_sent' => true,
                        'email_status' => 'sent',
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    $this->info("✓ Reminder ID {$reminder->id}: Email sent successfully");
                    $successCount++;
                } else {
                    // Failed
                    $reminder->update([
                        'email_status' => 'failed',
                        'email_error' => 'Email sending failed',
                    ]);

                    $this->error("✗ Reminder ID {$reminder->id}: Email sending failed");
                    $failedCount++;
                }

            } catch (\Exception $e) {
                // Error handling
                $reminder->update([
                    'email_status' => 'failed',
                    'email_error' => $e->getMessage(),
                ]);

                $this->error("✗ Reminder ID {$reminder->id}: Error - {$e->getMessage()}");
                Log::error("Reminder processing error: {$e->getMessage()}", [
                    'reminder_id' => $reminder->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $failedCount++;
            }
        }

        $this->info("\nProcessing complete!");
        $this->info("Success: {$successCount}");
        $this->info("Failed: {$failedCount}");

        return 0;
    }

    /**
     * Send reminder email based on type
     */
    private function sendReminderEmail(Reminder $reminder): bool
    {
        try {
            $customerEmail = $reminder->customer->email;

            switch ($reminder->type) {
                case 'inquiry_followup':
                    Mail::to($customerEmail)->send(new InquiryFollowupMail($reminder));
                    break;

                case 'appointment_followup':
                    Mail::to($customerEmail)->send(new AppointmentFollowupMail($reminder));
                    break;

                case 'general':
                case 'document_pending':
                case 'payment_followup':
                default:
                    Mail::to($customerEmail)->send(new GeneralReminderMail($reminder));
                    break;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Email sending error for reminder {$reminder->id}: {$e->getMessage()}");
            return false;
        }
    }
}