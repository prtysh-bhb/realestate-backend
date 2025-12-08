<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\CreditTransaction;
use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;
use Exception;

class CreditService
{
    /**
     * Purchase credits for user
     */
    public function purchaseCredits(User $user, int $creditPackageId, int $credits, float $price, array $metaData = [])
    {
        return DB::transaction(function () use ($user, $creditPackageId, $credits, $price, $metaData) {
            $wallet = $user->getOrCreateWallet();
            
            // Add credits to wallet
            $wallet->addCredits($credits);

            // Create transaction record
            $transaction = CreditTransaction::create([
                'user_id' => $user->id,
                'property_id' => null,
                'type' => 'purchase',
                'credits' => $credits,
                'description' => "Purchased {$credits} credits for ₹{$price}",
                'meta_data' => array_merge($metaData, [
                    'package_id' => $creditPackageId,
                    'price' => $price,
                ]),
            ]);

            return $transaction;
        });
    }

    /**
     * Spend credits for an action
     */
    public function spendCredits(User $user, string $actionType, ?int $propertyId = null, ?int $customAmount = null)
    {
        return DB::transaction(function () use ($user, $actionType, $propertyId, $customAmount) {
            $wallet = $user->getOrCreateWallet();

            // Get credit cost from settings
            $creditCost = $customAmount ?? (int) AppSetting::get($actionType, 0);

            if ($creditCost <= 0) {
                throw new Exception("Invalid action type or credit cost not configured");
            }

            // Check if user has enough credits
            if (!$wallet->hasEnoughCredits($creditCost)) {
                throw new Exception("Insufficient credits. You need {$creditCost} credits but have {$wallet->current_credits}");
            }

            // Deduct credits
            $wallet->deductCredits($creditCost);

            // Create transaction record
            $transaction = CreditTransaction::create([
                'user_id' => $user->id,
                'property_id' => $propertyId,
                'type' => 'spend',
                'credits' => -$creditCost, // Negative for deduction
                'description' => $this->getActionDescription($actionType),
                'meta_data' => [
                    'action_type' => $actionType,
                    'credit_cost' => $creditCost,
                ],
            ]);

            return $transaction;
        });
    }

    /**
     * Admin adds credits manually
     */
    public function adminAddCredits(User $user, int $credits, string $reason)
    {
        return DB::transaction(function () use ($user, $credits, $reason) {
            $wallet = $user->getOrCreateWallet();
            $wallet->addCredits($credits);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'property_id' => null,
                'type' => 'admin_add',
                'credits' => $credits,
                'description' => $reason,
                'meta_data' => ['added_by_admin' => true],
            ]);
        });
    }

    /**
     * Admin deducts credits manually
     */
    public function adminDeductCredits(User $user, int $credits, string $reason)
    {
        return DB::transaction(function () use ($user, $credits, $reason) {
            $wallet = $user->getOrCreateWallet();

            if (!$wallet->hasEnoughCredits($credits)) {
                throw new Exception("User doesn't have enough credits to deduct");
            }

            $wallet->deductCredits($credits);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'property_id' => null,
                'type' => 'admin_deduct',
                'credits' => -$credits,
                'description' => $reason,
                'meta_data' => ['deducted_by_admin' => true],
            ]);
        });
    }

    /**
     * Refund credits
     */
    public function refundCredits(User $user, int $credits, string $reason, array $metaData = [])
    {
        return DB::transaction(function () use ($user, $credits, $reason, $metaData) {
            $wallet = $user->getOrCreateWallet();
            $wallet->increment('current_credits', $credits);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'property_id' => null,
                'type' => 'refund',
                'credits' => $credits,
                'description' => $reason,
                'meta_data' => $metaData,
            ]);
        });
    }

    /**
     * Give bonus credits
     */
    public function giveBonusCredits(User $user, int $credits, string $reason)
    {
        return DB::transaction(function () use ($user, $credits, $reason) {
            $wallet = $user->getOrCreateWallet();
            $wallet->increment('current_credits', $credits);

            return CreditTransaction::create([
                'user_id' => $user->id,
                'property_id' => null,
                'type' => 'bonus',
                'credits' => $credits,
                'description' => $reason,
                'meta_data' => ['bonus' => true],
            ]);
        });
    }

    /**
     * Get action description for transaction
     */
    private function getActionDescription(string $actionType): string
    {
        $descriptions = [
            'property_photo' => 'Viewed property photos',
            'property_video' => 'Viewed property video',
            'agent_number' => 'Viewed agent contact number',
            'book_appointment' => 'Booked property visit appointment',
            'exact_location' => 'Viewed exact property location',
            'unlock_documents' => 'Unlocked property documents',
            'send_inquiry' => 'Sent inquiry to property owner',
            'unlock_vr_tour' => 'Unlocked VR/3D tour',
            'view_analytics' => 'Viewed property analytics',
        ];

        return $descriptions[$actionType] ?? 'Credit spent on action';
    }
}