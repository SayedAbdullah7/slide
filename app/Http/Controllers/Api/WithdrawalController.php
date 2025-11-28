<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\WithdrawalRequest;
use App\Rules\SaudiIban;
use App\Services\WalletService;
use App\Support\CurrentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class WithdrawalController extends Controller
{
    use ApiResponseTrait;

    protected WalletService $walletService;
    protected CurrentProfile $currentProfile;

    public function __construct(
        WalletService $walletService,
        CurrentProfile $currentProfile
    ) {
        $this->walletService = $walletService;
        $this->currentProfile = $currentProfile;
    }

    /**
     * Get available balance for withdrawal
     * الحصول على المبلغ المتاح للسحب
     */
    public function getAvailableBalance(Request $request): JsonResponse
    {
        try {
            if (!$this->currentProfile->model) {
                return $this->respondError('لم يتم العثور على بروفايل نشط', 400);
            }

            // Only investor profile can withdraw
            if ($this->currentProfile->type !== 'investor') {
                return $this->respondError('يمكن لبروفايلات المستثمرين فقط سحب الأموال', 400);
            }

            $balance = $this->walletService->getBalance();

            return $this->respondSuccessWithData('Available balance retrieved successfully', [
                'available_balance' => $balance,
                'formatted_balance' => number_format($balance, 2) . ' ريال',
                'currency' => 'SAR',
                'processing_time' => 'معالجة العملية تستغرق من يومين إلى ٥ أيام عمل',
                'processing_time_en' => 'Processing takes 2 to 5 business days',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get available balance', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('فشل في الحصول على الرصيد المتاح');
        }
    }

    /**
     * Get list of Saudi banks
     * الحصول على قائمة البنوك السعودية
     */
    public function getBanks(Request $request): JsonResponse
    {
        try {
            $banks = Bank::active()
                ->orderBy('name_ar')
                ->get()
                ->map(function ($bank) {
                    return [
                        'id' => $bank->id,
                        'code' => $bank->code,
                        'name_ar' => $bank->name_ar,
                        'name_en' => $bank->name_en,
                        'icon' => $bank->icon,
                    ];
                });

            return $this->respondSuccessWithData('Banks list retrieved successfully', [
                'banks' => $banks,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get banks list', [
                'error' => $e->getMessage(),
            ]);

            return $this->respondInternalError('فشل في الحصول على قائمة البنوك');
        }
    }

    /**
     * Get saved bank accounts for the user
     * الحصول على الحسابات البنكية المحفوظة
     */
    public function getBankAccounts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->respondUnAuthorized('المستخدم غير مصادق عليه');
            }

            // Get investor profile if available
            $investorId = null;
            if ($this->currentProfile->model && $this->currentProfile->type === 'investor') {
                $investorId = $this->currentProfile->model->id;
            }

            $query = BankAccount::query()
                ->where(function ($q) use ($user, $investorId) {
                    $q->where('user_id', $user->id);
                    if ($investorId) {
                        $q->orWhere('investor_id', $investorId);
                    }
                })
                ->active()
                ->with('bank')
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc');

            $bankAccounts = $query->get();

            $formattedAccounts = $bankAccounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_id' => $account->bank_id,
                    'bank_name' => $account->bank?->name_ar,
                    'bank_name_en' => $account->bank?->name_en,
                    'bank_code' => $account->bank?->code,
                    'masked_account_number' => $account->masked_account_number,
                    'account_number' => $account->masked_account_number, // For backward compatibility
                    'account_holder_name' => $account->account_holder_name,
                    'is_default' => $account->is_default,
                ];
            });

            return $this->respondSuccessWithData('Bank accounts retrieved successfully', [
                'bank_accounts' => $formattedAccounts,
                'count' => $formattedAccounts->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get bank accounts', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('فشل في الحصول على الحسابات البنكية');
        }
    }

    /**
     * Add a new bank account
     * إضافة حساب بنكي جديد
     */
    public function addBankAccount(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'bank_id' => 'required|integer|exists:banks,id',
                'account_holder_name' => 'required|string|max:255',
                'iban' => ['required', 'string', new SaudiIban],
                'account_number' => 'nullable|string|max:50',
                // 'save_for_future' => 'boolean',
                // 'set_as_default' => 'boolean',
            ]);

            $user = $request->user();

            if (!$user) {
                return $this->respondUnAuthorized('المستخدم غير مصادق عليه');
            }

            // Get investor profile if available
            $investorId = null;
            if ($this->currentProfile->model && $this->currentProfile->type === 'investor') {
                $investorId = $this->currentProfile->model->id;
            }

            // Verify bank exists and is active
            $bank = Bank::findOrFail($data['bank_id']);
            if (!$bank->is_active) {
                return $this->respondBadRequest('هذا البنك غير نشط');
            }

            // If save_for_future is true, save the bank account
            if ($request->boolean('save_for_future') || true) {
                DB::beginTransaction();

                try {
                    // Normalize IBAN (remove spaces, uppercase)
                    $iban = str_replace(' ', '', strtoupper(trim($data['iban'])));

                    // Check if account already exists
                    $existingQuery = BankAccount::where('iban', $iban);
                    if ($user->id) {
                        $existingQuery->where(function ($q) use ($user, $investorId) {
                            $q->where('user_id', $user->id);
                            if ($investorId) {
                                $q->orWhere('investor_id', $investorId);
                            }
                        });
                    }
                    $existingAccount = $existingQuery->first();

                    if ($existingAccount) {
                        DB::rollBack();
                        return $this->respondBadRequest('هذا الحساب البنكي محفوظ بالفعل');
                    }

                    // Extract last 4 digits for account_number if not provided
                    $accountNumber = $data['account_number'] ?? substr($iban, -4);

                    $bankAccount = BankAccount::create([
                        'user_id' => $user->id,
                        'investor_id' => $investorId,
                        'bank_id' => $data['bank_id'],
                        'account_holder_name' => $data['account_holder_name'],
                        'iban' => $iban,
                        'account_number' => $accountNumber,
                        'is_default' => $request->boolean('set_as_default', false),
                        'is_active' => true,
                    ]);

                    // If set as default, update other accounts
                    if ($bankAccount->is_default) {
                        $bankAccount->setAsDefault();
                    }

                    DB::commit();

                    return $this->respondSuccessWithData('Bank account saved successfully', [
                        'bank_account' => [
                            'id' => $bankAccount->id,
                            'bank_id' => $bankAccount->bank_id,
                            'bank_name' => $bankAccount->bank?->name_ar,
                            'bank_name_en' => $bankAccount->bank?->name_en,
                            'masked_account_number' => $bankAccount->masked_account_number,
                            'is_default' => $bankAccount->is_default,
                        ],
                    ]);

                } catch (Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            // If not saving, just return success (account will be stored with withdrawal request)
            return $this->respondSuccessWithData('Bank account validated successfully', [
                'bank_details' => [
                    'bank_id' => $bank->id,
                    'bank_name' => $bank->name_ar,
                    'account_holder_name' => $data['account_holder_name'],
                    'iban' => str_replace(' ', '', strtoupper(trim($data['iban']))),
                ],
            ]);

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (Exception $e) {
            Log::error('Failed to add bank account', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('فشل في إضافة الحساب البنكي');
        }
    }

    /**
     * Delete a bank account
     * حذف حساب بنكي
     */
    public function deleteBankAccount(Request $request, $bankAccountId): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->respondUnAuthorized('المستخدم غير مصادق عليه');
            }

            // Get investor profile if available
            $investorId = null;
            if ($this->currentProfile->model && $this->currentProfile->type === 'investor') {
                $investorId = $this->currentProfile->model->id;
            }

            // Find bank account and verify ownership
            $bankAccountQuery = BankAccount::where('id', $bankAccountId);

            if ($user->id) {
                $bankAccountQuery->where(function ($q) use ($user, $investorId) {
                    $q->where('user_id', $user->id);
                    if ($investorId) {
                        $q->orWhere('investor_id', $investorId);
                    }
                });
            }

            $bankAccount = $bankAccountQuery->first();

            if (!$bankAccount) {
                return $this->respondNotFound('الحساب البنكي غير موجود');
            }

            // Check if bank account is used in any pending or processing withdrawal requests
            $activeWithdrawals = WithdrawalRequest::where('bank_account_id', $bankAccount->id)
                ->whereIn('status', [
                    WithdrawalRequest::STATUS_PENDING,
                    WithdrawalRequest::STATUS_PROCESSING
                ])
                ->exists();

            // if ($activeWithdrawals) {
            //     return $this->respondBadRequest('لا يمكن حذف الحساب البنكي المستخدم في طلبات سحب نشطة');
            // }

            // Delete the bank account
            $bankAccount->delete();

            Log::info('Bank account deleted', [
                'bank_account_id' => $bankAccount->id,
                'user_id' => $user->id,
            ]);

            return $this->respondSuccess('تم حذف الحساب البنكي بنجاح');

        } catch (Exception $e) {
            Log::error('Failed to delete bank account', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'bank_account_id' => $bankAccountId,
            ]);

            return $this->respondInternalError('فشل في حذف الحساب البنكي');
        }
    }

    /**
     * Create a withdrawal request
     * إنشاء طلب سحب أموال
     */
    public function createWithdrawalRequest(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'bank_account_id' => 'nullable|integer|exists:bank_accounts,id',
                'bank_id' => 'required_without:bank_account_id|integer|exists:banks,id',
                'account_holder_name' => 'required_without:bank_account_id|string|max:255',
                'iban' => ['required_without:bank_account_id', 'string', new SaudiIban],
                'save_for_future' => 'nullable|boolean',
                'set_as_default' => 'nullable|boolean',
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('لم يتم العثور على بروفايل نشط', 400);
            }

            // Only investor profile can withdraw
            if ($this->currentProfile->type !== 'investor') {
                return $this->respondError('يمكن لبروفايلات المستثمرين فقط سحب الأموال', 400);
            }

            $user = $request->user();
            $profile = $this->currentProfile->model;
            $investorId = $profile->id ?? null;

            // Check available balance
            $availableBalance = $this->walletService->getBalance();
            if ($availableBalance < $data['amount']) {
                return $this->respondBadRequest('الرصيد غير كافي', [
                    'available_balance' => $availableBalance,
                    'requested_amount' => $data['amount'],
                    'shortfall' => $data['amount'] - $availableBalance,
                ]);
            }

            DB::beginTransaction();

            try {
                $bankAccountId = null;
                $bankDetails = null;

                // If bank_account_id is provided, use saved account
                if (!empty($data['bank_account_id'])) {
                    $bankAccountQuery = BankAccount::where('id', $data['bank_account_id'])->active();

                    // Check ownership
                    if ($user->id) {
                        $bankAccountQuery->where(function ($q) use ($user, $investorId) {
                            $q->where('user_id', $user->id);
                            if ($investorId) {
                                $q->orWhere('investor_id', $investorId);
                            }
                        });
                    }

                    $bankAccount = $bankAccountQuery->with('bank')->first();

                    if (!$bankAccount) {
                        DB::rollBack();
                        return $this->respondNotFound('Bank account not found');
                    }

                    $bankAccountId = $bankAccount->id;
                    $bankDetails = [
                        'bank_id' => $bankAccount->bank_id,
                        'bank_name' => $bankAccount->bank?->name_ar,
                        'bank_name_en' => $bankAccount->bank?->name_en,
                        'bank_code' => $bankAccount->bank?->code,
                        'account_holder_name' => $bankAccount->account_holder_name,
                        'iban' => $bankAccount->iban,
                        'masked_account_number' => $bankAccount->masked_account_number,
                    ];
                } else {
                    // Use provided bank details
                    $bank = Bank::findOrFail($data['bank_id']);
                    $iban = str_replace(' ', '', strtoupper(trim($data['iban'])));

                    // If save_for_future is true, save the bank account
                    if ($request->boolean('save_for_future')) {
                        // Check if account already exists
                        $existingQuery = BankAccount::where('iban', $iban);
                        if ($user->id) {
                            $existingQuery->where(function ($q) use ($user, $investorId) {
                                $q->where('user_id', $user->id);
                                if ($investorId) {
                                    $q->orWhere('investor_id', $investorId);
                                }
                            });
                        }
                        $existingAccount = $existingQuery->first();

                        if ($existingAccount) {
                            // Account already exists, use it
                            $bankAccountId = $existingAccount->id;
                            $bankDetails = [
                                'bank_id' => $existingAccount->bank_id,
                                'bank_name' => $existingAccount->bank?->name_ar,
                                'bank_name_en' => $existingAccount->bank?->name_en,
                                'bank_code' => $existingAccount->bank?->code,
                                'account_holder_name' => $existingAccount->account_holder_name,
                                'iban' => $existingAccount->iban,
                                'masked_account_number' => $existingAccount->masked_account_number,
                            ];
                        } else {
                            // Create new bank account
                            $accountNumber = substr($iban, -4);
                            $newBankAccount = BankAccount::create([
                                'user_id' => $user->id,
                                'investor_id' => $investorId,
                                'bank_id' => $data['bank_id'],
                                'account_holder_name' => $data['account_holder_name'],
                                'iban' => $iban,
                                'account_number' => $accountNumber,
                                'is_default' => $request->boolean('set_as_default', false),
                                'is_active' => true,
                            ]);

                            // If set as default, update other accounts
                            if ($newBankAccount->is_default) {
                                $newBankAccount->setAsDefault();
                            }

                            $bankAccountId = $newBankAccount->id;
                            $bankDetails = [
                                'bank_id' => $bank->id,
                                'bank_name' => $bank->name_ar,
                                'bank_name_en' => $bank->name_en,
                                'bank_code' => $bank->code,
                                'account_holder_name' => $data['account_holder_name'],
                                'iban' => $iban,
                                'masked_account_number' => $newBankAccount->masked_account_number,
                            ];
                        }
                    } else {
                        // Don't save, just use for this withdrawal
                        $bankDetails = [
                            'bank_id' => $bank->id,
                            'bank_name' => $bank->name_ar,
                            'bank_name_en' => $bank->name_en,
                            'bank_code' => $bank->code,
                            'account_holder_name' => $data['account_holder_name'],
                            'iban' => $iban,
                        ];
                    }
                }

                // Create withdrawal request
                $withdrawalRequest = WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'investor_id' => $investorId,
                    'profile_type' => $this->currentProfile->type,
                    'profile_id' => $profile->id,
                    'bank_account_id' => $bankAccountId,
                    'amount' => $data['amount'],
                    'available_balance' => $availableBalance,
                    'status' => WithdrawalRequest::STATUS_PENDING,
                    'bank_details' => $bankDetails,
                ]);

                // Withdraw money from wallet immediately when request is created (to reserve/hold it)
                $this->walletService->withdrawFromWallet(
                    $profile,
                    $data['amount'],
                    [
                        'description' => 'تم إنشاء طلب سحب أموال',
                        // 'description_en' => 'Withdrawal request created',
                        'withdrawal_request_id' => $withdrawalRequest->id,
                        'reference_number' => $withdrawalRequest->reference_number,
                        'user_id' => $user->id,
                    ]
                );

                // Mark money as withdrawn
                $withdrawalRequest->money_withdrawn = true;
                $withdrawalRequest->save();

                DB::commit();

                Log::info('Withdrawal request created', [
                    'request_id' => $withdrawalRequest->id,
                    'user_id' => $user->id,
                    'amount' => $data['amount'],
                    'reference_number' => $withdrawalRequest->reference_number,
                ]);

                return $this->respondSuccessWithData('Withdrawal request created successfully', [
                    'withdrawal_request' => [
                        'id' => $withdrawalRequest->id,
                        'reference_number' => $withdrawalRequest->reference_number,
                        'amount' => $withdrawalRequest->amount,
                        'formatted_amount' => number_format($withdrawalRequest->amount, 2) . ' ريال',
                        'status' => $withdrawalRequest->status,
                        'available_balance' => $withdrawalRequest->available_balance,
                        'bank_details' => $bankDetails,
                        'processing_time' => 'معالجة العملية تستغرق من يومين إلى ٥ أيام عمل',
                        'created_at' => $withdrawalRequest->created_at->toISOString(),
                    ],
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (Exception $e) {
            Log::error('Failed to create withdrawal request', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->respondInternalError('فشل في إنشاء طلب السحب');
        }
    }

    /**
     * Get withdrawal request history
     * الحصول على تاريخ طلبات السحب
     */
    public function getWithdrawalHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->respondUnAuthorized('المستخدم غير مصادق عليه');
            }

            $perPage = $request->input('per_page', 15);
            $status = $request->input('status');

            // Get investor profile if available
            $investorId = null;
            if ($this->currentProfile->model && $this->currentProfile->type === 'investor') {
                $investorId = $this->currentProfile->model->id;
            }

            $query = WithdrawalRequest::where(function ($q) use ($user, $investorId) {
                    $q->where('user_id', $user->id);
                    if ($investorId) {
                        $q->orWhere('investor_id', $investorId);
                    }
                })
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $requests = $query->paginate($perPage);

            $formattedRequests = $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'reference_number' => $request->reference_number,
                    'amount' => $request->amount,
                    'formatted_amount' => number_format($request->amount, 2) . ' ريال',
                    'status' => $request->status,
                    // 'status_label' => $this->getStatusLabel($request->status),
                    'bank_details' => $request->bank_details,
                    'available_balance' => $request->available_balance,
                    'rejection_reason' => $request->rejection_reason,
                    'approved_by' => $request->approved_by,
                    'created_at' => $request->created_at->toISOString(),
                    'completed_at' => $request->completed_at?->toISOString(),
                ];
            });

            return $this->respondSuccessWithData('Withdrawal history retrieved successfully', [
                'withdrawal_requests' => $formattedRequests,
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                    'last_page' => $requests->lastPage(),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get withdrawal history', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('فشل في الحصول على تاريخ طلبات السحب');
        }
    }


    /**
     * Get status label in Arabic and English
     */
    private function getStatusLabel(string $status): array
    {
        return match ($status) {
            WithdrawalRequest::STATUS_PENDING => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
            WithdrawalRequest::STATUS_PROCESSING => ['ar' => 'قيد المعالجة', 'en' => 'Processing'],
            WithdrawalRequest::STATUS_COMPLETED => ['ar' => 'مكتمل', 'en' => 'Completed'],
            WithdrawalRequest::STATUS_REJECTED => ['ar' => 'مرفوض', 'en' => 'Rejected'],
            WithdrawalRequest::STATUS_CANCELLED => ['ar' => 'ملغي', 'en' => 'Cancelled'],
            default => ['ar' => 'غير معروف', 'en' => 'Unknown'],
        };
    }
}
