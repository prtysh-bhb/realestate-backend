<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\CreditTransaction;
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
     * List all user wallets with filters
     */
    public function index(Request $request)
    {
        $wallets = UserWallet::with('user:id,name,email')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('current_credits', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $wallets,
        ]);
    }

    /**
     * Get specific user's wallet details
     */
    public function show($userId)
    {
        $user = User::with(['wallet', 'creditTransactions' => function ($query) {
            $query->latest()->limit(20);
        }])->findOrFail($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'wallet' => $user->wallet,
                'recent_transactions' => $user->creditTransactions,
            ],
        ]);
    }

    /**
     * Manually add credits to user wallet
     */
    public function addCredits(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'credits' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $transaction = $this->creditService->adminAddCredits(
                $user,
                $validated['credits'],
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => "{$validated['credits']} credits added successfully",
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
     * Manually deduct credits from user wallet
     */
    public function deductCredits(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'credits' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $transaction = $this->creditService->adminDeductCredits(
                $user,
                $validated['credits'],
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => "{$validated['credits']} credits deducted successfully",
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
     * Get credit usage statistics
     */
    public function creditUsageReport(Request $request)
    {
        $stats = [
            'total_credits_purchased' => UserWallet::sum('total_credits_purchased'),
            'total_credits_spent' => UserWallet::sum('total_credits_spent'),
            'total_current_credits' => UserWallet::sum('current_credits'),
            'total_users_with_wallet' => UserWallet::count(),
            'total_transactions' => CreditTransaction::count(),
        ];

        // Transaction breakdown by type
        $transactionsByType = CreditTransaction::selectRaw('type, COUNT(*) as count, SUM(ABS(credits)) as total_credits')
            ->groupBy('type')
            ->get();

        // Recent purchases
        $recentPurchases = CreditTransaction::with('user:id,name,email')
            ->where('type', 'purchase')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $stats,
                'transactions_by_type' => $transactionsByType,
                'recent_purchases' => $recentPurchases,
            ],
        ]);
    }

    /**
     * Get all credit transactions with filters
     */
    public function transactions(Request $request)
    {
        $transactions = CreditTransaction::with(['user:id,name,email', 'property:id,title'])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->from_date, function ($query, $fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($query, $toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}