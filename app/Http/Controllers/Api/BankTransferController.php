<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\BankTransferRequest;
use App\Models\Bank;
use App\Services\WalletService;
use App\Support\CurrentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;

class BankTransferController extends Controller
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
     * Get company bank account details
     * الحصول على بيانات الحساب البنكي للشركة
     */
    public function getCompanyBankAccount(Request $request): JsonResponse
    {
        try {
            $bankAccount = config('bank_transfer.company_bank_account');

            return $this->respondSuccessWithData('Company bank account details retrieved successfully', [
                'bank_name' => $bankAccount['bank_name'],
                'bank_name_en' => $bankAccount['bank_name_en'],
                'bank_code' => $bankAccount['bank_code'],
                'account_number' => $bankAccount['account_number'],
                'iban' => $bankAccount['iban'],
                'company_name' => $bankAccount['company_name'],
                'company_name_en' => $bankAccount['company_name_en'],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get company bank account', [
                'error' => $e->getMessage(),
            ]);

            return $this->respondInternalError('Failed to retrieve company bank account details');
        }
    }

    /**
     * Submit bank transfer request with receipt
     * إرسال طلب التحويل البنكي مع الإيصال
     */
    public function submitBankTransfer(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            ]);

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            // Only investor profile can deposit
            if ($this->currentProfile->type !== 'investor') {
                return $this->respondError('Only investor profiles can deposit funds', 400);
            }

            $user = $request->user();
            $profile = $this->currentProfile->model;
            $investorId = $profile->id ?? null;

            DB::beginTransaction();

            try {
                // Upload receipt file
                $receiptFile = $request->file('receipt');
                $originalFileName = $receiptFile->getClientOriginalName();
                $storagePath = config('bank_transfer.receipt.storage_path', 'bank_transfer_receipts');

                $storedPath = $receiptFile->store($storagePath, 'public');

                // Create bank transfer request
                $bankTransferRequest = BankTransferRequest::create([
                    'user_id' => $user->id,
                    'investor_id' => $investorId,
                    'profile_type' => $this->currentProfile->type,
                    'profile_id' => $profile->id,
                    'receipt_file' => $storedPath,
                    'receipt_file_name' => $originalFileName,
                    'status' => BankTransferRequest::STATUS_PENDING,
                ]);

                DB::commit();

                Log::info('Bank transfer request created', [
                    'request_id' => $bankTransferRequest->id,
                    'user_id' => $user->id,
                ]);

                return $this->respondSuccessWithData('Bank transfer request submitted successfully', [
                    'bank_transfer_request' => [
                        'id' => $bankTransferRequest->id,
                        'status' => $bankTransferRequest->status,
                        'receipt_file' => $bankTransferRequest->receipt_file_name,
                        'message' => 'Your bank transfer request has been submitted. Our team will review it shortly.',
                        'created_at' => $bankTransferRequest->created_at->toISOString(),
                    ],
                ]);

            } catch (Exception $e) {
                DB::rollBack();

                // Delete uploaded file if request creation failed
                if (isset($storedPath)) {
                    Storage::disk('public')->delete($storedPath);
                }

                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (Exception $e) {
            Log::error('Failed to submit bank transfer request', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->respondInternalError('Failed to submit bank transfer request');
        }
    }

    /**
     * Get bank transfer request history
     * الحصول على تاريخ طلبات التحويل البنكي
     */
    public function getBankTransferHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$this->currentProfile->model) {
                return $this->respondError('No active profile found', 400);
            }

            $profile = $this->currentProfile->model;

            // Get all bank transfer requests for this profile
            $requests = BankTransferRequest::where('profile_id', $profile->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedRequests = $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'reference_number' => $request->transfer_reference,
                    'status' => $request->status,
                    'status_label' => ucfirst($request->status),
                    'amount' => $request->amount ? number_format($request->amount, 2) : null,
                    'bank_name' => $request->bank?->name_ar,
                    'receipt_file_name' => $request->receipt_file_name,
                    'receipt_url' => $request->receipt_url,
                    'admin_notes' => $request->admin_notes,
                    'rejection_reason' => $request->rejection_reason,
                    'processed_at' => $request->processed_at?->toISOString(),
                    'created_at' => $request->created_at->toISOString(),
                    'created_at_formatted' => $request->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return $this->respondSuccessWithData('Bank transfer history retrieved successfully', [
                'history' => $formattedRequests,
                'total' => $requests->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get bank transfer history', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return $this->respondInternalError('Failed to retrieve bank transfer history');
        }
    }
}
