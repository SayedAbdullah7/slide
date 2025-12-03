@props([
    'withdrawal',
])

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Header Info -->
    <div class="alert alert-danger d-flex align-items-center mb-7">
        <div class="symbol symbol-circle symbol-50px bg-light-danger me-4">
            <i class="ki-outline ki-cross-circle fs-2x text-danger"></i>
        </div>
        <div class="flex-grow-1">
            <h4 class="mb-1 text-danger">Reject Withdrawal Request</h4>
            <span class="text-gray-700">Reference: <code>{{ $withdrawal->reference_number }}</code></span>
        </div>
    </div>

    <!-- Withdrawal Info -->
    <div class="card mb-7">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-5">
                    <div class="text-muted fs-7 mb-1">Amount</div>
                    <div class="text-danger fw-bold fs-4">{{ number_format($withdrawal->amount, 2) }} SAR</div>
                </div>
                <div class="col-md-6 mb-5">
                    <div class="text-muted fs-7 mb-1">User</div>
                    <div class="text-gray-800 fw-semibold">{{ $withdrawal->user->display_name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form id="kt_modal_form" action="{{ route('admin.withdrawals.update-status', $withdrawal->id) }}" data-method="POST">
        @csrf
        <input type="hidden" name="status" value="rejected">

        <!-- Rejection Reason (Required) -->
        <div class="mb-7">
            <label class="form-label fw-bold required fs-5">Rejection Reason</label>
            <textarea class="form-control"
                      name="rejection_reason"
                      rows="3"
                      placeholder="Please provide a clear reason for rejecting this withdrawal request..."
                      required></textarea>
            <div class="form-text">This reason will be shown to the user</div>
        </div>

        <!-- Admin Notes (Optional) -->
        <div class="mb-7">
            <label class="form-label fw-bold fs-5">Admin Notes (Optional)</label>
            <textarea class="form-control"
                      name="admin_notes"
                      rows="3"
                      placeholder="Internal notes (not visible to user)..."></textarea>
            <div class="form-text">Internal notes only</div>
        </div>

        <!-- Warning -->
        <div class="alert alert-warning d-flex align-items-center mb-7">
            <i class="ki-outline ki-information-5 fs-2x text-warning me-3"></i>
            <div>
                <div class="fw-bold mb-1">Rejection Notice</div>
                <div class="fs-7">The rejection reason will be sent to the user. This action cannot be undone easily.</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center pt-5">
            <button type="button" class="btn btn-light me-3 close" data-bs-dismiss="modal">
                <i class="ki-outline ki-cross fs-4"></i>
                Cancel
            </button>
            <button type="submit" class="btn btn-danger">
                <i class="ki-outline ki-cross-circle fs-4 me-1"></i>
                Reject Request
            </button>
        </div>
    </form>
</div>

