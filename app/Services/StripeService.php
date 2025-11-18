<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use App\Models\UserSubscription;
use App\Models\Payment;
use App\Models\SubscriptionPlan;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create or get Stripe customer
     */
    public function createOrGetCustomer($user)
    {
        $subscription = $user->subscriptions()->first();
        
        if ($subscription && $subscription->stripe_customer_id) {
            return $subscription->stripe_customer_id;
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        return $customer->id;
    }

    /**
     * Create payment intent (Production - without auto-confirm)
     */
    public function createPaymentIntent($user, SubscriptionPlan $plan)
    {
        $customerId = $this->createOrGetCustomer($user);
        $amount = (int) ($plan->price * 100);

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'description' => "Subscription: {$plan->name}",
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return $paymentIntent;
    }

    /**
     * Create payment intent with test card and auto-confirm (Testing only)
     */
    public function createAndConfirmPaymentIntent($user, SubscriptionPlan $plan)
    {
        $customerId = $this->createOrGetCustomer($user);
        $amount = (int) ($plan->price * 100);

        // Step 1: Create a test payment method
        $paymentMethod = PaymentMethod::create([
            'type' => 'card',
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 12,
                'exp_year' => 2025,
                'cvc' => '123',
            ],
        ]);

        // Step 2: Create payment intent
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'payment_method' => $paymentMethod->id,
            'description' => "Subscription: {$plan->name}",
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        // Step 3: Retrieve and confirm the payment intent
        $confirmed = PaymentIntent::retrieve($paymentIntent->id);
        $confirmed->confirm();

        // Step 4: Retrieve final status
        return PaymentIntent::retrieve($paymentIntent->id);
    }

    /**
     * Retrieve payment intent status
     */
    public function getPaymentIntentStatus($paymentIntentId)
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Confirm an existing payment intent with payment method
     */
    public function confirmPaymentIntent($paymentIntentId, $paymentMethodId = null)
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        $confirmParams = [];
        if ($paymentMethodId) {
            $confirmParams['payment_method'] = $paymentMethodId;
        }

        $confirmed = $paymentIntent->confirm($confirmParams);
        
        return $confirmed;
    }

    /**
     * Verify and process successful payment
     */
    public function verifyAndProcessPayment($paymentIntentId, $user, $planId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                throw new \Exception('Payment not completed. Status: ' . $paymentIntent->status);
            }

            $plan = SubscriptionPlan::findOrFail($planId);
            $expectedAmount = (int) ($plan->price * 100);
            
            if ($paymentIntent->amount !== $expectedAmount) {
                throw new \Exception('Payment amount mismatch');
            }

            // Check if subscription already created
            $existingPayment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($existingPayment) {
                return $existingPayment->subscription;
            }

            // Create subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_customer_id' => $paymentIntent->customer,
                'stripe_subscription_id' => $paymentIntent->id,
                'status' => 'active',
                'amount_paid' => $plan->price,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->duration_days),
            ]);

            // Create payment record
            Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_charge_id' => $paymentIntent->charges->data[0]->id ?? null,
                'amount' => $plan->price,
                'currency' => 'usd',
                'status' => 'succeeded',
                'description' => "Subscription payment for {$plan->name}",
                'metadata' => $paymentIntent->metadata->toArray(),
            ]);

            return $subscription;
        } catch (\Exception $e) {
            throw new \Exception('Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(UserSubscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Create payment intent with test token and auto-confirm (TESTING ONLY)
     * This is separate from production methods
     */
    public function createAndConfirmPaymentIntentForTesting($user, SubscriptionPlan $plan)
    {
        $customerId = $this->createOrGetCustomer($user);
        $amount = (int) ($plan->price * 100);

        // Use Stripe's test payment method token
        $testPaymentMethod = 'pm_card_visa';

        // Create payment intent with test payment method
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customerId,
            'payment_method' => $testPaymentMethod,
            'description' => "Subscription: {$plan->name}",
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
            ],
            'confirm' => true, // Auto-confirm
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        // Retrieve final status
        return PaymentIntent::retrieve($paymentIntent->id);
    }

    /**
     * Confirm payment intent with test token (TESTING ONLY)
     */
    public function confirmPaymentIntentForTesting($paymentIntentId)
    {
        $testPaymentMethod = 'pm_card_visa';
        
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        
        $confirmed = $paymentIntent->confirm([
            'payment_method' => $testPaymentMethod,
        ]);
        
        return $confirmed;
    }

}