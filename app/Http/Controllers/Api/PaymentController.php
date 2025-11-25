<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use App\Services\StripeService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $stripeService;
    protected $invoiceService;

    public function __construct(StripeService $stripeService, InvoiceService $invoiceService)
    {
        $this->stripeService = $stripeService;
        $this->invoiceService = $invoiceService;
    }


    /**
     * Download invoice
     */
    public function downloadInvoice($paymentId)
    {
        try {
            $payment = Payment::with(['user', 'subscription.plan'])
                ->where('user_id', auth()->id())
                ->findOrFail($paymentId);

            if ($payment->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is only available for successful payments',
                ], 400);
            }

            $pdf = $this->invoiceService->generateInvoice($payment);
            $filename = 'invoice_' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View invoice (stream in browser)
     */
    public function viewInvoice($paymentId)
    {
        try {
            $payment = Payment::with(['user', 'subscription.plan'])
                ->where('user_id', auth()->id())
                ->findOrFail($paymentId);

            if ($payment->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is only available for successful payments',
                ], 400);
            }

            $pdf = $this->invoiceService->generateInvoice($payment);

            return $pdf->stream('invoice_' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Email invoice to customer
     */
    public function emailInvoice($paymentId)
    {
        try {
            $payment = Payment::with(['user', 'subscription.plan'])
                ->where('user_id', auth()->id())
                ->findOrFail($paymentId);

            if ($payment->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is only available for successful payments',
                ], 400);
            }

            $sent = $this->invoiceService->emailInvoice($payment);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice sent to your email successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send invoice email',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to email invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Step 1: Create payment intent
     * Frontend gets client_secret to collect card details
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $plan = SubscriptionPlan::where('is_active', true)
                ->findOrFail($request->plan_id);
            
            $user = auth()->user();

            // Check if user already has active subscription
            $activeSubscription = $user->activeSubscription()->first();
            if ($activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription',
                    'data' => [
                        'subscription' => $activeSubscription->load('plan'),
                    ],
                ], 400);
            }

            $paymentIntent = $this->stripeService->createPaymentIntent($user, $plan);

            return response()->json([
                'success' => true,
                'message' => 'Payment intent created successfully',
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                    'publishable_key' => config('services.stripe.key'),
                    'amount' => $plan->price,
                    'currency' => 'usd',
                    'plan' => $plan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Step 2: Check payment status (Optional - for frontend polling)
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = $this->stripeService->getPaymentIntentStatus($request->payment_intent_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'status' => $paymentIntent->status,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Step 3: Verify payment and create subscription
     * Called after frontend confirms payment with Stripe.js
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $user = auth()->user();

            $subscription = $this->stripeService->verifyAndProcessPayment(
                $request->payment_intent_id,
                $user,
                $request->plan_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Subscription activated.',
                'data' => [
                    'subscription' => $subscription->load('plan'),
                    'payment' => $subscription->payments()->latest()->first(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's subscriptions
     */
    public function mySubscriptions()
    {
        $subscriptions = auth()->user()
            ->subscriptions()
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'subscriptions' => $subscriptions,
            ],
        ]);
    }

    /**
     * Get active subscription
     */
    public function activeSubscription()
    {
        $subscription = auth()->user()
            ->activeSubscription()
            ->with('plan')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription,
                'has_active_subscription' => $subscription !== null,
            ],
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($id)
    {
        try {
            $subscription = auth()->user()
                ->subscriptions()
                ->findOrFail($id);

            if ($subscription->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active subscriptions can be cancelled',
                ], 400);
            }

            $this->stripeService->cancelSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'data' => [
                    'subscription' => $subscription->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history
     */
    public function paymentHistory()
    {
        $payments = auth()->user()
            ->payments()
            ->with('subscription.plan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments,
            ],
        ]);
    }

    /**
     * Create and confirm payment in one step (FOR TESTING ONLY)
     */
    public function createAndConfirmPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $plan = SubscriptionPlan::where('is_active', true)
                ->findOrFail($request->plan_id);
            
            $user = auth()->user();

            // Check if user already has active subscription
            $activeSubscription = $user->activeSubscription()->first();
            if ($activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription',
                    'data' => [
                        'subscription' => $activeSubscription->load('plan'),
                    ],
                ], 400);
            }

            // Create and confirm payment with test card
            $paymentIntent = $this->stripeService->createAndConfirmPaymentIntent($user, $plan);

            // Verify and create subscription
            $subscription = $this->stripeService->verifyAndProcessPayment(
                $paymentIntent->id,
                $user,
                $plan->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment completed and subscription activated!',
                'data' => [
                    'payment_intent' => [
                        'id' => $paymentIntent->id,
                        'status' => $paymentIntent->status,
                        'amount' => $paymentIntent->amount / 100,
                    ],
                    'subscription' => $subscription->load('plan'),
                    'payment' => $subscription->payments()->latest()->first(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm an existing payment intent (FOR TESTING)
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $user = auth()->user();

            // Create test payment method
            $paymentMethod = \Stripe\PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 12,
                    'exp_year' => 2025,
                    'cvc' => '123',
                ],
            ]);

            // Confirm the payment intent
            $paymentIntent = $this->stripeService->confirmPaymentIntent(
                $request->payment_intent_id,
                $paymentMethod->id
            );

            if ($paymentIntent->status === 'succeeded') {
                // Create subscription
                $subscription = $this->stripeService->verifyAndProcessPayment(
                    $paymentIntent->id,
                    $user,
                    $request->plan_id
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed and subscription activated!',
                    'data' => [
                        'payment_intent' => [
                            'id' => $paymentIntent->id,
                            'status' => $paymentIntent->status,
                        ],
                        'subscription' => $subscription->load('plan'),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment confirmation failed. Status: ' . $paymentIntent->status,
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * TESTING ONLY: Create payment intent with test payment method attached
     */
    public function testCreateIntent(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $plan = SubscriptionPlan::where('is_active', true)
                ->findOrFail($request->plan_id);
            
            $user = auth()->user();

            // Check if user already has active subscription
            $activeSubscription = $user->activeSubscription()->first();
            if ($activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription',
                    'data' => [
                        'subscription' => $activeSubscription->load('plan'),
                    ],
                ], 400);
            }

            $customerId = $this->stripeService->createOrGetCustomer($user);
            $amount = (int) ($plan->price * 100);

            // Create payment intent with test payment method attached (but not confirmed yet)
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $customerId,
                'payment_method' => 'pm_card_visa', // Attach test payment method
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

            return response()->json([
                'success' => true,
                'message' => 'Payment intent created with test card attached (not confirmed yet)',
                'data' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'status' => $paymentIntent->status,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                    'plan' => $plan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * TESTING ONLY: Confirm payment intent (retrieve and confirm)
     */
    public function testConfirmIntent(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $user = auth()->user();

            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Step 1: Retrieve the payment intent
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            // Step 2: Confirm the payment intent
            $confirmed = $paymentIntent->confirm();

            // Step 3: Retrieve updated status
            $finalIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($finalIntent->status === 'succeeded') {
                // Step 4: Create subscription
                $subscription = $this->stripeService->verifyAndProcessPayment(
                    $finalIntent->id,
                    $user,
                    $request->plan_id
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed and subscription activated!',
                    'data' => [
                        'payment_intent' => [
                            'id' => $finalIntent->id,
                            'status' => $finalIntent->status,
                            'amount' => $finalIntent->amount / 100,
                        ],
                        'subscription' => $subscription->load('plan'),
                        'payment' => $subscription->payments()->latest()->first(),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment confirmation failed. Status: ' . $finalIntent->status,
                    'data' => [
                        'payment_intent' => [
                            'id' => $finalIntent->id,
                            'status' => $finalIntent->status,
                        ],
                    ],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}