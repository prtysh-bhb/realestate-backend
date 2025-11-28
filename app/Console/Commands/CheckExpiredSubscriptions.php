<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscription;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check and handle expired subscriptions';

    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        // Find active subscriptions that have expired
        $expiredSubscriptions = UserSubscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return 0;
        }

        $this->info("Found {$expiredSubscriptions->count()} expired subscriptions.");

        $processedCount = 0;
        $failedCount = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                // 1. Update subscription status to expired
                $subscription->update([
                    'status' => 'expired',
                ]);

                $this->info("✓ Subscription ID {$subscription->id}: Marked as expired");

                // 2. Remove featured status from all agent's properties
                $featuredRemoved = Property::where('agent_id', $subscription->user_id)
                    ->where('is_featured', true)
                    ->update([
                        'is_featured' => false,
                        'featured_until' => null,
                    ]);

                if ($featuredRemoved > 0) {
                    $this->info("  - Removed featured status from {$featuredRemoved} properties");
                }

                // 3. Log the expiry
                Log::info("Subscription expired", [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'plan' => $subscription->plan->name,
                    'expired_at' => $subscription->ends_at,
                ]);

                // 4. Send expiry notification email (optional)
                try {
                    if ($subscription->user && $subscription->user->email) {
                        // Mail::to($subscription->user->email)
                        //     ->send(new \App\Mail\SubscriptionExpiredMail($subscription));
                        // $this->info("  - Expiry email sent to {$subscription->user->email}");
                    }
                } catch (\Exception $e) {
                    $this->warn("  - Failed to send email: {$e->getMessage()}");
                }

                $processedCount++;

            } catch (\Exception $e) {
                $this->error("✗ Subscription ID {$subscription->id}: Error - {$e->getMessage()}");
                Log::error("Subscription expiry error: {$e->getMessage()}", [
                    'subscription_id' => $subscription->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $failedCount++;
            }
        }

        $this->info("\nProcessing complete!");
        $this->info("Successfully processed: {$processedCount}");
        $this->info("Failed: {$failedCount}");

        return 0;
    }
}