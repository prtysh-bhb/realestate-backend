<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to user
     */
    public function send(User $user, string $type, array $data, bool $sendEmail = false, ?string $mailClass = null)
    {
        try {
            // ⭐ NEW: Add frontend URL to action_url if it exists
            if (isset($data['action_url'])) {
                $data['action_url'] = config('app.frontend_url') . $data['action_url'];
            }

            // Store in database
            $user->notifications()->create([
                'type' => $type,
                'data' => $data,
                'read_at' => null,
            ]);

            // Send email if requested
            if ($sendEmail && $mailClass && $user->email) {
                $mail = new $mailClass($data);
                Mail::to($user->email)->send($mail);
            }

            Log::info("Notification sent: {$type} to user {$user->id}");

        } catch (\Exception $e) {
            Log::error("Notification failed: {$type}", ['error' => $e->getMessage()]);
        }
    }
}