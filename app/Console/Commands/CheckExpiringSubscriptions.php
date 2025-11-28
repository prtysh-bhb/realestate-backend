<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring {--days=7 : Number of days before expiry}';
    protected $description = 'Check subscriptions expiring soon and send notifications';

    public function handle()
    {
        // Cast to integer
        $days = (int) $this->option('days');
        
        $this->info("Checking for subscriptions expiring in {$days} days...");

        // Find active subscriptions expiring in next N days
        $expiringSubscriptions = UserSubscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays($days)])
            ->get();

        if ($expiringSubscriptions->isEmpty()) {
            $this->info('No expiring subscriptions found.');
            return 0;
        }

        $this->info("Found {$expiringSubscriptions->count()} subscriptions expiring soon.");

        $notifiedCount = 0;
        $failedCount = 0;

        foreach ($expiringSubscriptions as $subscription) {
            try {
                $daysLeft = (int) now()->diffInDays($subscription->ends_at);

                $this->info("Subscription ID {$subscription->id}: Expires in {$daysLeft} days");

                // Log expiring subscription
                Log::info("Subscription expiring soon", [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'plan' => $subscription->plan->name,
                    'expires_at' => $subscription->ends_at,
                    'days_left' => $daysLeft,
                ]);

                // Send expiry warning email (optional)
                try {
                    if ($subscription->user && $subscription->user->email) {
                        // Mail::to($subscription->user->email)
                        //     ->send(new \App\Mail\SubscriptionExpiringMail($subscription, $daysLeft));
                        $this->info("  - Warning email would be sent to {$subscription->user->email}");
                    }
                } catch (\Exception $e) {
                    $this->warn("  - Failed to send email: {$e->getMessage()}");
                }

                $notifiedCount++;

            } catch (\Exception $e) {
                $this->error("✗ Subscription ID {$subscription->id}: Error - {$e->getMessage()}");
                Log::error("Subscription expiring check error: {$e->getMessage()}", [
                    'subscription_id' => $subscription->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $failedCount++;
            }
        }

        $this->info("\nNotification complete!");
        $this->info("Successfully notified: {$notifiedCount}");
        $this->info("Failed: {$failedCount}");

        return 0;
    }
}