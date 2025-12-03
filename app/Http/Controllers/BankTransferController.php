<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\BankTransferDataTable;
use App\Models\BankTransferRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Bank;

class BankTransferController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(BankTransferDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.bank-transfer.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(BankTransferRequest $bankTransfer): View
    {
        $bankTransfer->load(['user', 'investor', 'bank', 'actionBy']);

        return view('pages.bank-transfer.show', compact('bankTransfer'));
    }

    /**
     * Show rejection form
     */
    public function showRejectForm(BankTransferRequest $bankTransfer): View
    {
        return view('pages.bank-transfer.reject-form', compact('bankTransfer'));
    }

    /**
     * Show approval form (to add transfer details)
     */
    public function showApproveForm(BankTransferRequest $bankTransfer): View
    {
        $banks = Bank::active()->orderBy('name_ar')->get();
        return view('pages.bank-transfer.approve-form', compact('bankTransfer', 'banks'));
    }

    /**
     * Update bank transfer status (approve/reject)
     */
    public function updateStatus(Request $request, BankTransferRequest $bankTransfer): JsonResponse
    {
        // Get status from query parameter or request body
        $status = $request->query('status') ?? $request->input('status');

        // Merge query parameters into request for validation
        if ($status) {
            $request->merge(['status' => $status]);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'bank_id' => 'required_if:status,approved|exists:banks,id',
            'transfer_reference' => 'required_if:status,approved|string|max:255|unique:bank_transfer_requests,transfer_reference,' . $bankTransfer->id,
            'amount' => 'required_if:status,approved|numeric|min:0.01',
            'admin_notes' => 'nullable|string|max:500',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $bankTransfer->status;
            $newStatus = $validated['status'];
            $investor = $bankTransfer->investor;

            // Update bank transfer request
            $bankTransfer->status = $newStatus;
            $bankTransfer->admin_notes = $validated['admin_notes'] ?? null;

            // Set action_by for any action taken
            $bankTransfer->action_by = auth()->id();

            if ($newStatus === BankTransferRequest::STATUS_APPROVED) {
                // Set approval details
                $bankTransfer->bank_id = $validated['bank_id'];
                $bankTransfer->transfer_reference = $validated['transfer_reference'];
                $bankTransfer->amount = $validated['amount'];
                $bankTransfer->processed_at = now();

                // Deposit money to investor wallet
                if ($investor) {
                    $this->walletService->depositToWallet(
                        $investor,
                        $validated['amount'],
                        [
                            'description' => 'Bank transfer deposit',
                            'bank_transfer_request_id' => $bankTransfer->id,
                            'transfer_reference' => $validated['transfer_reference'],
                            'bank_id' => $validated['bank_id'],
                            'admin_user_id' => auth()->id(),
                        ]
                    );
                }
            }

            if ($newStatus === BankTransferRequest::STATUS_REJECTED) {
                $bankTransfer->rejection_reason = $validated['rejection_reason'] ?? null;
                $bankTransfer->processed_at = now();
            }

            $bankTransfer->save();

            DB::commit();

            Log::info('Bank transfer request status updated', [
                'bank_transfer_id' => $bankTransfer->id,
                'transfer_reference' => $bankTransfer->transfer_reference,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_id' => auth()->id(),
            ]);

            // Build success message
            $message = 'Bank transfer request status updated successfully.';
            if ($newStatus === BankTransferRequest::STATUS_APPROVED) {
                $message = 'Bank transfer request approved. Amount has been added to the wallet.';
            } elseif ($newStatus === BankTransferRequest::STATUS_REJECTED) {
                $message = 'Bank transfer request rejected.';
            }

            return response()->json([
                'status' => true,
                'success' => true,
                'msg' => $message,
                'message' => $message,
                'data' => $bankTransfer->fresh(['user', 'investor', 'bank', 'actionBy']),
                'reload' => true,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update bank transfer request status', [
                'bank_transfer_id' => $bankTransfer->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'msg' => 'Failed to update bank transfer request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
