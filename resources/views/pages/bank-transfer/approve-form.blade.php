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
    <div class="alert alert-success d-flex align-items-center mb-7">
        <i class="ki-outline ki-information-5 fs-2x text-success me-4"></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-success">Approve Bank Transfer</h4>
            <span>Fill in the transfer details below to approve this request.</span>
        </div>
    </div>

    <!-- Form -->
    <form id="kt_modal_form" action="{{ route('admin.bank-transfers.update-status', $bankTransfer->id) }}" data-method="POST">
        <input type="hidden" name="status" value="approved">

        <!-- Bank Selection -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Bank Used <span class="text-danger">*</span></label>
            <select
                name="bank_id"
                class="form-select form-select-solid"
                data-control="select2"
                data-placeholder="Select bank"
                required>
                <option></option>
                @foreach($banks as $bank)
                    <option value="{{ $bank->id }}" {{ old('bank_id', $bankTransfer->bank_id) == $bank->id ? 'selected' : '' }}>
                        {{ $bank->name_ar }}
                        @if($bank->name_en)
                            - {{ $bank->name_en }}
                        @endif
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Transfer Reference -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Transfer Reference Number <span class="text-danger">*</span></label>
            <input
                type="text"
                name="transfer_reference"
                class="form-control form-control-solid"
                placeholder="Enter unique transfer reference number"
                value="{{ old('transfer_reference', $bankTransfer->transfer_reference) }}"
                required>
            <div class="form-text">Enter the unique reference number from the bank transfer receipt.</div>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Amount -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Transfer Amount (SAR) <span class="text-danger">*</span></label>
            <input
                type="number"
                name="amount"
                class="form-control form-control-solid"
                placeholder="0.00"
                step="0.01"
                min="0.01"
                value="{{ old('amount', $bankTransfer->amount) }}"
                required>
            <div class="form-text">Enter the amount that was transferred.</div>
            <div class="invalid-feedback"></div>
        </div>

        <!-- Admin Notes -->
        <div class="mb-10">
            <label class="form-label fw-semibold text-gray-900">Admin Notes (Optional)</label>
            <textarea
                name="admin_notes"
                class="form-control form-control-solid"
                rows="3"
                placeholder="Internal notes (not visible to user)">{{ old('admin_notes', $bankTransfer->admin_notes) }}</textarea>
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
                    <div class="col-md-6">
                        <div class="text-muted fs-7 mb-1">User</div>
                        <div class="text-gray-800 fw-bold">{{ $bankTransfer->user->display_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7 mb-1">Receipt</div>
                        <div class="text-gray-800">{{ $bankTransfer->receipt_file_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-3">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
                <i class="ki-outline ki-check fs-3 me-2"></i>
                Approve & Add to Wallet
            </button>
        </div>
    </form>
</div>

