<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Header -->
    <div class="mb-7">
        <div class="d-flex align-items-center">
            <div class="symbol symbol-circle symbol-75px bg-light-danger me-5">
                <i class="ki-outline ki-cross-circle fs-2x text-danger"></i>
            </div>
            <div class="flex-grow-1">
                <h2 class="text-gray-900 fw-bold mb-1">Reject Withdrawal Request</h2>
                <div class="text-muted fs-6">
                    Reference: <code>{{ $withdrawal->reference_number }}</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form id="kt_modal_form" action="{{ route('admin.withdrawals.update-status', $withdrawal->id) }}" data-method="POST">
        <input type="hidden" name="status" value="rejected">

        <!-- Rejection Reason -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Rejection Reason <span class="text-danger">*</span></label>
            <textarea
                name="rejection_reason"
                class="form-control form-control-solid"
                rows="4"
                placeholder="Please provide a reason for rejecting this withdrawal request..."
                required></textarea>
            <div class="form-text">This reason will be visible to the user.</div>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Admin Notes -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Admin Notes (Optional)</label>
            <textarea
                name="admin_notes"
                class="form-control form-control-solid"
                rows="3"
                placeholder="Internal notes (not visible to user)"></textarea>
            <div class="form-text">Internal notes for your reference only.</div>
        </div>

        <!-- Withdrawal Info -->
        <div class="card card-flush mb-10">
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="text-muted fs-7 mb-1">Amount</div>
                        <div class="text-gray-800 fw-bold fs-5">{{ number_format($withdrawal->amount, 2) }} SAR</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7 mb-1">Request Date</div>
                        <div class="text-gray-800">{{ $withdrawal->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">
                <i class="ki-outline ki-cross-circle fs-2 me-2"></i>
                Reject Request
            </button>
        </div>
    </form>
</div>














