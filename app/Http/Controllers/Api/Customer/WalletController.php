<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Credit;
use App\Services\CreditService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * GET /customer/wallet
     */
    public function index()
    {
        $user = auth()->user();
        $wallet = $user->getOrCreateWallet();

        return response()->json([
            'success' => true,
            'data' => [
                'current_credits' => $wallet->current_credits,
                'total_credits_purchased' => $wallet->total_credits_purchased,
                'total_credits_spent' => $wallet->total_credits_spent,
            ],
        ]);
    }

    /**
     * GET /customer/wallet/packages
     */
    public function packages()
    {
        $packages = Credit::where('status', 'active')
            ->orderBy('coins', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * POST /customer/wallet/buy
     */
    public function buy(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:credits,id',
            'payment_method_id' => 'required|string',
        ]);

        $package = Credit::findOrFail($validated['package_id']);

        if ($package->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This package is not available',
            ], 400);
        }

        try {
            $user = auth()->user();
            
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $package->price * 100,
                'currency' => 'inr',
                'payment_method' => $validated['payment_method_id'],
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'description' => "Purchase {$package->name} - {$package->coins} credits",
                'metadata' => [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'coins' => $package->coins,
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $transaction = $this->creditService->purchaseCredits(
                    $user,
                    $package->id,
                    $package->coins,
                    $package->price,
                    [
                        'package_name' => $package->name,
                        'payment_method' => 'stripe',
                        'stripe_payment_intent_id' => $paymentIntent->id,
                        'stripe_charge_id' => $paymentIntent->charges->data[0]->id ?? null,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => "Successfully purchased {$package->coins} credits",
                    'data' => [
                        'transaction' => $transaction,
                        'wallet' => $user->wallet->fresh(),
                        'payment_intent_id' => $paymentIntent->id,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
            ], 400);

        } catch (\Stripe\Exception\CardException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getError()->message,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /customer/wallet/spend
     */
    public function spend(Request $request)
    {
        $validated = $request->validate([
            'action_type' => 'required|string|in:property_photo,property_video,agent_number,book_appointment,exact_location,unlock_documents,send_inquiry,unlock_vr_tour,view_analytics',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        try {
            $user = auth()->user();
            
            $transaction = $this->creditService->spendCredits(
                $user,
                $validated['action_type'],
                $validated['property_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Credits spent successfully',
                'data' => [
                    'transaction' => $transaction,
                    'wallet' => $user->wallet->fresh(),
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
     * GET /customer/wallet/transactions
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();

        $transactions = $user->creditTransactions()
            ->with('property:id,title')
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * GET /customer/wallet/balance
     */
    public function balance()
    {
        $user = auth()->user();
        $wallet = $user->getOrCreateWallet();

        return response()->json([
            'success' => true,
            'balance' => $wallet->current_credits,
        ]);
    }
}