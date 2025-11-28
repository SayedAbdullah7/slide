@php
    $statusConfig = [
        'pending' => ['color' => 'warning', 'icon' => 'ki-time', 'label' => 'Pending'],
        'approved' => ['color' => 'success', 'icon' => 'ki-check-circle', 'label' => 'Approved'],
        'rejected' => ['color' => 'danger', 'icon' => 'ki-cross-circle', 'label' => 'Rejected'],
    ];
    $currentStatus = $statusConfig[$bankTransfer->status] ?? ['color' => 'secondary', 'icon' => 'ki-information', 'label' => ucfirst($bankTransfer->status)];
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Header Info -->
    <div class="alert alert-danger d-flex align-items-center mb-7">
        <i class="ki-outline ki-information-5 fs-2x text-danger me-4"></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-danger">Reject Bank Transfer Request</h4>
            <span>Please provide a reason for rejecting this bank transfer request.</span>
        </div>
    </div>

    <!-- Form -->
    <form id="kt_modal_form" action="{{ route('admin.bank-transfers.update-status', $bankTransfer->id) }}" data-method="POST">
        <input type="hidden" name="status" value="rejected">

        <!-- Rejection Reason -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Rejection Reason <span class="text-danger">*</span></label>
            <textarea
                name="rejection_reason"
                class="form-control form-control-solid"
                rows="4"
                placeholder="Please provide a reason for rejecting this bank transfer request..."
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

        <!-- Request Info -->
        <div class="card card-flush mb-10">
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="text-muted fs-7 mb-1">Request ID</div>
                        <div class="text-gray-800 fw-bold fs-5">#{{ $bankTransfer->id }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7 mb-1">Request Date</div>
                        <div class="text-gray-800">{{ $bankTransfer->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($bankTransfer->amount)
                        <div class="col-md-6">
                            <div class="text-muted fs-7 mb-1">Amount</div>
                            <div class="text-gray-800 fw-bold fs-5">{{ number_format($bankTransfer->amount, 2) }} SAR</div>
                        </div>
                    @endif
                    @if($bankTransfer->bank)
                        <div class="col-md-6">
                            <div class="text-muted fs-7 mb-1">Bank</div>
                            <div class="text-gray-800 fw-bold">{{ $bankTransfer->bank->name_ar }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">
                <i class="ki-outline ki-cross fs-3 me-2"></i>
                Reject Request
            </button>
        </div>
    </form>
</div>

