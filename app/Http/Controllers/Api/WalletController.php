<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletTransactionResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\WalletService;
use App\Services\WalletStatisticsService;
use App\Support\CurrentProfile;
use App\WalletDepositSourceEnum;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class WalletController extends Controller
{
    use ApiResponseTrait;

    protected WalletService $walletService;
    protected WalletStatisticsService $walletStatisticsService;
    protected CurrentProfile $currentProfile;

    public function __construct(
        WalletService $walletService,
        WalletStatisticsService $walletStatisticsService,
        CurrentProfile $currentProfile
    ) {
        $this->walletService = $walletService;
        $this->walletStatisticsService = $walletStatisticsService;
        $this->currentProfile = $currentProfile;
    }

    /**
     * Get wallet balance
     * الحصول على رصيد المحفظة
     */
    public function getBalance(Request $request): JsonResponse
    {
        try {
            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Use getWalletBalance to ensure consistency across all APIs
            $balance = $this->walletService->getWalletBalance($this->currentProfile->model);

            return $this->respondSuccessWithData('Balance retrieved successfully', [
                'balance' => $balance,
                'formatted_balance' => number_format($balance, 2),
                'profile_type' => $this->currentProfile->type,
                'profile_id' => $this->currentProfile->model->id,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get wallet balance', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Failed to retrieve balance');
        }
    }

    /**
     * Deposit money to wallet
     * إيداع مبلغ إلى المحفظة
     */
    public function deposit(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'description' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:100',
                'metadata' => 'nullable|array',
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Prepare metadata
            $meta = [
                'source' => WalletDepositSourceEnum::API,
                'description' => $data['description'] ?? 'Wallet deposit',
                'reference' => $data['reference'] ?? null,
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type,
                'timestamp' => now()->toISOString(),
            ];

            // Merge additional metadata if provided
            if (!empty($data['metadata'])) {
                $meta = array_merge($meta, $data['metadata']);
            }

            DB::beginTransaction();

            try {
                $success = $this->walletService->deposit($data['amount'], $meta);

                if (!$success) {
                    throw new Exception('Deposit operation failed');
                }

                // Get updated balance using getWalletBalance for consistency
                $newBalance = $this->walletService->getWalletBalance($this->currentProfile->model);

                DB::commit();

                Log::info('Wallet deposit successful', [
                    'amount' => $data['amount'],
                    'user_id' => $request->user()?->id,
                    'profile_type' => $this->currentProfile->type,
                    'new_balance' => $newBalance,
                ]);

                return $this->respondSuccessWithData('Deposit successful', [
                    'amount' => $data['amount'],
                    'new_balance' => $newBalance,
                    'formatted_amount' => number_format($data['amount'], 2),
                    'formatted_balance' => number_format($newBalance, 2),
                    'profile_type' => $this->currentProfile->type,
                    'timestamp' => now()->toISOString(),
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (AmountInvalid $e) {
            return $this->respondBadRequest('Invalid amount provided');
        } catch (Exception $e) {
            Log::error('Wallet deposit failed', [
                'error' => $e->getMessage(),
                'amount' => $request->input('amount'),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Deposit failed. Please try again.');
        }
    }

    /**
     * Withdraw money from wallet
     * سحب مبلغ من المحفظة
     */
    public function withdraw(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'description' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:100',
                'metadata' => 'nullable|array',
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Check if user has sufficient balance using getWalletBalance for consistency
            $currentBalance = $this->walletService->getWalletBalance($this->currentProfile->model);
            if ($currentBalance < $data['amount']) {
                return $this->respondBadRequest('Insufficient balance', [
                    'current_balance' => $currentBalance,
                    'requested_amount' => $data['amount'],
                    'shortfall' => $data['amount'] - $currentBalance,
                ]);
            }

            // Prepare metadata
            $meta = [
                'description' => $data['description'] ?? 'Wallet withdrawal',
                'reference' => $data['reference'] ?? null,
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type,
                'timestamp' => now()->toISOString(),
            ];

            // Merge additional metadata if provided
            if (!empty($data['metadata'])) {
                $meta = array_merge($meta, $data['metadata']);
            }

            DB::beginTransaction();

            try {
                $success = $this->walletService->withdraw($data['amount'], $meta);

                if (!$success) {
                    throw new Exception('Withdrawal operation failed');
                }

                // Get updated balance using getWalletBalance for consistency
                $newBalance = $this->walletService->getWalletBalance($this->currentProfile->model);

                DB::commit();

                Log::info('Wallet withdrawal successful', [
                    'amount' => $data['amount'],
                    'user_id' => $request->user()?->id,
                    'profile_type' => $this->currentProfile->type,
                    'new_balance' => $newBalance,
                ]);

                return $this->respondSuccessWithData('Withdrawal successful', [
                    'amount' => $data['amount'],
                    'new_balance' => $newBalance,
                    'formatted_amount' => number_format($data['amount'], 2),
                    'formatted_balance' => number_format($newBalance, 2),
                    'profile_type' => $this->currentProfile->type,
                    'timestamp' => now()->toISOString(),
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (AmountInvalid $e) {
            return $this->respondBadRequest('Invalid amount provided');
        } catch (InsufficientFunds $e) {
            return $this->respondBadRequest('Insufficient funds for withdrawal');
        } catch (BalanceIsEmpty $e) {
            return $this->respondBadRequest('Wallet balance is empty');
        } catch (Exception $e) {
            Log::error('Wallet withdrawal failed', [
                'error' => $e->getMessage(),
                'amount' => $request->input('amount'),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Withdrawal failed. Please try again.');
        }
    }

    /**
     * Get wallet transactions history
     * الحصول على تاريخ معاملات المحفظة
     */
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'type' => 'nullable|in:deposit,withdraw',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            $perPage = $data['per_page'] ?? 15;
            $wallet = $this->currentProfile->model;

            // Build query with filters
            $query = $wallet->transactions()->orderBy('created_at', 'desc');

            // Apply filters if provided
            if (!empty($data['type'])) {
                $query->where('type', $data['type']);
            }

            if (!empty($data['date_from'])) {
                $query->whereDate('created_at', '>=', $data['date_from']);
            }

            if (!empty($data['date_to'])) {
                $query->whereDate('created_at', '<=', $data['date_to']);
            }

            $transactions = $query->paginate($perPage);

            return $this->respondSuccessWithData('Transactions retrieved successfully', [
                'transactions' => WalletTransactionResource::collection($transactions),
                'total_count' => $transactions->count(),
                'profile_type' => $this->currentProfile->type,
                'profile_id' => $this->currentProfile->model->id,
            ]);

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (Exception $e) {
            Log::error('Failed to get wallet transactions', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Failed to retrieve transactions');
        }
    }

    /**
     * Transfer money to another profile's wallet
     * تحويل مبلغ إلى محفظة أخرى
     */
    public function transfer(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'to_profile_type' => 'required|in:investor,owner',
                'to_profile_id' => 'required|integer|exists:' . $this->getProfileTable($request->to_profile_type) . ',id',
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'description' => 'nullable|string|max:255',
                'reference' => 'nullable|string|max:100',
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Get the target profile
            $targetProfile = $this->getTargetProfile($data['to_profile_type'], $data['to_profile_id']);
            if (!$targetProfile) {
                return $this->respondNotFound('Target profile not found');
            }

            // Check if user has sufficient balance using getWalletBalance for consistency
            $currentBalance = $this->walletService->getWalletBalance($this->currentProfile->model);
            if ($currentBalance < $data['amount']) {
                return $this->respondBadRequest('Insufficient balance for transfer');
            }

            // Prepare metadata
            $meta = [
                'description' => $data['description'] ?? 'Wallet transfer',
                'reference' => $data['reference'] ?? null,
                'from_user_id' => $request->user()?->id,
                'from_profile_type' => $this->currentProfile->type,
                'to_profile_type' => $data['to_profile_type'],
                'to_profile_id' => $data['to_profile_id'],
                'timestamp' => now()->toISOString(),
            ];

            DB::beginTransaction();

            try {
                $success = $this->walletService->transfer($targetProfile, $data['amount'], $meta);

                if (!$success) {
                    throw new Exception('Transfer operation failed');
                }

                // Get updated balance using getWalletBalance for consistency
                $newBalance = $this->walletService->getWalletBalance($this->currentProfile->model);

                DB::commit();

                Log::info('Wallet transfer successful', [
                    'amount' => $data['amount'],
                    'from_user_id' => $request->user()?->id,
                    'from_profile_type' => $this->currentProfile->type,
                    'to_profile_type' => $data['to_profile_type'],
                    'to_profile_id' => $data['to_profile_id'],
                    'new_balance' => $newBalance,
                ]);

                return $this->respondSuccessWithData('Transfer successful', [
                    'amount' => $data['amount'],
                    'new_balance' => $newBalance,
                    'formatted_amount' => number_format($data['amount'], 2),
                    'formatted_balance' => number_format($newBalance, 2),
                    'from_profile_type' => $this->currentProfile->type,
                    'to_profile_type' => $data['to_profile_type'],
                    'to_profile_id' => $data['to_profile_id'],
                    'timestamp' => now()->toISOString(),
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (AmountInvalid $e) {
            return $this->respondBadRequest('Invalid amount provided');
        } catch (InsufficientFunds $e) {
            return $this->respondBadRequest('Insufficient funds for transfer');
        } catch (BalanceIsEmpty $e) {
            return $this->respondBadRequest('Wallet balance is empty');
        } catch (Exception $e) {
            Log::error('Wallet transfer failed', [
                'error' => $e->getMessage(),
                'amount' => $request->input('amount'),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Transfer failed. Please try again.');
        }
    }

    /**
     * Get wallet screen data (main wallet page)
     * الحصول على بيانات شاشة المحفظة الرئيسية
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Validate request parameters
            $request->validate([
                'period' => 'sometimes|string|in:day,week,month,quarter,year,all',
                'type' => 'sometimes|string|in:deposit,withdraw,all',
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            $wallet = $this->currentProfile->model;
            // Use getWalletBalance to ensure consistency across all APIs
            $balance = $this->walletService->getWalletBalance($wallet);

            // Get filters
            $period = $request->input('period', 'all');
            $type = $request->input('type', 'all');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Build transactions query with filtering
            $transactionsQuery = $wallet->transactions()->orderBy('created_at', 'desc');

            // Apply period filter
            if ($period !== 'all') {
                $transactionsQuery = $this->applyPeriodFilter($transactionsQuery, $period);
            }

            // Apply type filter
            if ($type !== 'all') {
                $transactionsQuery->where('type', $type);
            }

            // Get paginated transactions
            $transactions = $transactionsQuery->paginate($perPage, ['*'], 'page', $page);

            // Get all statistics from the dedicated service
            $statistics = $this->walletStatisticsService->getAllStatistics();

            return $this->respondSuccessWithData('Wallet screen data retrieved successfully', [
                'total_balance' => [
                    'amount' => $balance,
                    'formatted_amount' => number_format($balance, 0) . ' ريال',
                    'currency' => 'SAR',
                    'is_visible' => true, // For the eye icon toggle
                ],
                'realized_profits' => $statistics['realized_profits'],
                'pending_profits' => $statistics['pending_profits'],
                'upcoming_earnings' => $statistics['upcoming_earnings'],
                'transactions' => [
                    'data' => WalletTransactionResource::collection($transactions),
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                        'last_page' => $transactions->lastPage(),
                        'from' => $transactions->firstItem(),
                        'to' => $transactions->lastItem(),
                        'has_more_pages' => $transactions->hasMorePages(),
                    ],
                    'filters' => [
                        'period' => $period,
                        'type' => $type,
                        'applied_filters' => [
                            'period' => $period,
                            'type' => $type,
                            'per_page' => $perPage,
                        ]
                    ]
                ],
                'profile_type' => $this->currentProfile->type,
                'profile_id' => $this->currentProfile->model->id,
            ]);

        } catch (ValidationException $e) {
            return $this->respondError('Invalid request parameters', 422);
        } catch (Exception $e) {
            Log::error('Failed to get wallet screen data', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Failed to retrieve wallet screen data');
        }
    }

    /**
     * Apply period filter to transactions query
     * تطبيق فلتر الفترة على استعلام المعاملات
     */
    private function applyPeriodFilter($query, string $period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'day':
                return $query->whereDate('created_at', $now->toDateString());

            case 'week':
                return $query->whereBetween('created_at', [
                    $now->startOfWeek()->toDateTimeString(),
                    $now->endOfWeek()->toDateTimeString()
                ]);

            case 'month':
                return $query->whereMonth('created_at', $now->month)
                            ->whereYear('created_at', $now->year);

            case 'quarter':
                $quarter = ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;

                return $query->whereYear('created_at', $now->year)
                            ->whereBetween('created_at', [
                                $now->copy()->month($startMonth)->startOfMonth()->toDateTimeString(),
                                $now->copy()->month($endMonth)->endOfMonth()->toDateTimeString()
                            ]);

            case 'year':
                return $query->whereYear('created_at', $now->year);

            case 'all':
            default:
                return $query;
        }
    }

    /**
     * Toggle balance visibility
     * تبديل إظهار/إخفاء الرصيد
     */
    public function toggleBalanceVisibility(Request $request): JsonResponse
    {
        try {
            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // This would typically be stored in user preferences or session
            // For now, we'll return a simple toggle response
            $isVisible = $request->input('is_visible', true);

            return $this->respondSuccessWithData('Balance visibility updated', [
                'is_visible' => $isVisible,
                'message' => $isVisible ? 'Balance is now visible' : 'Balance is now hidden',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to toggle balance visibility', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('Failed to toggle balance visibility');
        }
    }

    /**
     * Get quick actions for wallet
     * الحصول على الإجراءات السريعة للمحفظة
     */
    public function getQuickActions(Request $request): JsonResponse
    {
        try {
            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Use getWalletBalance to ensure consistency across all APIs
            $balance = $this->walletService->getWalletBalance($this->currentProfile->model);

            return $this->respondSuccessWithData('Quick actions retrieved successfully', [
                'actions' => [
                    [
                        'id' => 'add_funds',
                        'title' => 'إضافة أموال',
                        'title_en' => 'Add Funds',
                        'icon' => 'plus',
                        'color' => 'green',
                        'enabled' => true,
                        'route' => 'api.wallet.deposit',
                        'method' => 'POST',
                    ],
                    [
                        'id' => 'request_funds',
                        'title' => 'طلب أموال',
                        'title_en' => 'Request Funds',
                        'icon' => 'arrow-up-right',
                        'color' => 'purple',
                        'enabled' => $balance > 0,
                        'route' => 'api.wallet.withdraw',
                        'method' => 'POST',
                    ],
                ],
                'current_balance' => $balance,
                'formatted_balance' => number_format($balance, 0) . ' ريال',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get quick actions', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('Failed to retrieve quick actions');
        }
    }

    /**
     * Create wallet for current profile
     * إنشاء محفظة للبروفايل الحالي
     */
    public function createWallet(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'meta' => 'nullable|array',
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            $attributes = [
                'name' => $data['name'] ?? 'Default Wallet',
                'description' => $data['description'] ?? null,
                'meta' => $data['meta'] ?? [],
            ];

            $success = $this->walletService->createWallet($attributes);

            if ($success) {
                return $this->respondSuccessWithData('Wallet created successfully', [
                    'profile_type' => $this->currentProfile->type,
                    'profile_id' => $this->currentProfile->model->id,
                    'wallet_attributes' => $attributes,
                ]);
            }

            return $this->respondInternalError('Failed to create wallet');

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (Exception $e) {
            Log::error('Failed to create wallet', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'profile_type' => $this->currentProfile->type ?? null,
            ]);

            return $this->respondInternalError('Failed to create wallet');
        }
    }

    /**
     * Get profile table name based on type
     */
    private function getProfileTable(string $profileType): string
    {
        return match ($profileType) {
            'investor' => 'investor_profiles',
            'owner' => 'owner_profiles',
            default => throw new Exception('Invalid profile type'),
        };
    }

    /**
     * Get target profile model
     */
    private function getTargetProfile(string $profileType, int $profileId)
    {
        return match ($profileType) {
            'investor' => \App\Models\InvestorProfile::find($profileId),
            'owner' => \App\Models\OwnerProfile::find($profileId),
            default => null,
        };
    }
}
