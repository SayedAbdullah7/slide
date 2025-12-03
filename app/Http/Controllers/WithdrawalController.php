<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\WithdrawalDataTable;
use App\Models\WithdrawalRequest;
// use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(WithdrawalDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.withdrawal.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(WithdrawalRequest $withdrawal): View
    {
        $withdrawal->load(['user', 'investor', 'bankAccount.bank', 'actionBy']);

        return view('pages.withdrawal.show', compact('withdrawal'));
    }

    /**
     * Show rejection form
     */
    public function showRejectForm(WithdrawalRequest $withdrawal): View
    {
        return view('pages.withdrawal.reject-form', compact('withdrawal'));
    }

    /**
     * Update withdrawal status (approve/reject/process)
     */
    public function updateStatus(Request $request, WithdrawalRequest $withdrawal): JsonResponse
    {
        // Get status from query parameter or request body
        $status = $request->query('status') ?? $request->input('status');

        // Merge query parameters into request for validation
        if ($status) {
            $request->merge(['status' => $status]);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,rejected',
            'admin_notes' => 'nullable|string|max:500',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        // Prevent processing if money was not withdrawn
        if ($validated['status'] === WithdrawalRequest::STATUS_PROCESSING) {
            if (!$withdrawal->money_withdrawn) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'msg' => 'Cannot process withdrawal request. Money was not withdrawn from the wallet.',
                    'message' => 'Cannot process withdrawal request. Money was not withdrawn from the wallet.',
                ], 400);
            }
        }

        // Prevent completion before processing
        if ($validated['status'] === WithdrawalRequest::STATUS_COMPLETED) {
            if ($withdrawal->status !== WithdrawalRequest::STATUS_PROCESSING) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'msg' => 'Cannot complete withdrawal request that has not been processed yet.',
                    'message' => 'Cannot complete withdrawal request that has not been processed yet.',
                ], 400);
            }
            // Also check if money was withdrawn
            if (!$withdrawal->money_withdrawn) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'msg' => 'Cannot complete withdrawal request. Money was not withdrawn from the wallet.',
                    'message' => 'Cannot complete withdrawal request. Money was not withdrawn from the wallet.',
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            $oldStatus = $withdrawal->status;
            $newStatus = $validated['status'];
            $investor = $withdrawal->investor;

            // Update withdrawal request
            $withdrawal->status = $newStatus;
            $withdrawal->admin_notes = $validated['admin_notes'] ?? null;

            // Set action_by for any action taken
            $withdrawal->action_by = auth()->id();

            if ($newStatus === WithdrawalRequest::STATUS_REJECTED) {
                $withdrawal->rejection_reason = $validated['rejection_reason'] ?? null;
            }

            // When changing to PROCESSING: Set processed_at (money already withdrawn when request was created)
            if ($newStatus === WithdrawalRequest::STATUS_PROCESSING) {
                $withdrawal->processed_at = now();
                // Money already withdrawn when request was created, no need to withdraw again
            }

            // When changing to COMPLETED: Just set completed_at (money already withdrawn when request was created)
            if ($newStatus === WithdrawalRequest::STATUS_COMPLETED) {
                $withdrawal->completed_at = now();
                // Money already withdrawn when request was created, no need to withdraw again
            }

            // When REJECTED: Refund the money (only if money was withdrawn)
            $moneyRefunded = false;
            if ($newStatus === WithdrawalRequest::STATUS_REJECTED) {
                // Only refund if money was actually withdrawn
                if ($withdrawal->money_withdrawn && $investor) {
                    // Refund by depositing back to wallet
                    $this->walletService->depositToWallet(
                        $investor,
                        $withdrawal->amount,
                        [
                            'description' => 'Withdrawal request rejected - refund',
                            'withdrawal_request_id' => $withdrawal->id,
                            'reference_number' => $withdrawal->reference_number,
                            'admin_user_id' => auth()->id(),
                        ]
                    );

                    // Mark money as not withdrawn (refunded)
                    $withdrawal->money_withdrawn = false;
                    $moneyRefunded = true;
                }
            }

            $withdrawal->save();

            DB::commit();

            Log::info('Withdrawal request status updated', [
                'withdrawal_id' => $withdrawal->id,
                'reference_number' => $withdrawal->reference_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_id' => auth()->id(),
            ]);

            // Build success message with refund information
            $message = 'Withdrawal request status updated successfully.';
            if ($newStatus === WithdrawalRequest::STATUS_REJECTED) {
                if ($moneyRefunded) {
                    $message = 'Withdrawal request rejected successfully. Money has been refunded to the wallet.';
                } else {
                    $message = 'Withdrawal request rejected successfully. No money was refunded (money was not withdrawn).';
                }
            }

            return response()->json([
                'status' => true,
                'success' => true,
                'msg' => $message,
                'message' => $message,
                'data' => $withdrawal->fresh(['user', 'investor', 'bankAccount.bank', 'actionBy']),
                'money_refunded' => $moneyRefunded ?? false,
                // 'reload' => true,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update withdrawal request status', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'msg' => 'Failed to update withdrawal request: ' . $e->getMessage(),
            ], 500);
        }
    }
}

