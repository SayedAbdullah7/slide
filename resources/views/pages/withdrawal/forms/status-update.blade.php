@props([
    'withdrawal',
    'status' => 'processing', // processing, completed, cancelled
])

@php
    $statusConfig = [
        'processing' => ['color' => 'success', 'icon' => 'ki-check-circle', 'label' => 'Approve & Process', 'title' => 'Approve and Process Withdrawal'],
        'completed' => ['color' => 'success', 'icon' => 'ki-check', 'label' => 'Mark as Completed', 'title' => 'Complete Withdrawal Request'],
        'cancelled' => ['color' => 'secondary', 'icon' => 'ki-cross', 'label' => 'Cancel Request', 'title' => 'Cancel Withdrawal Request'],
    ];
    $config = $statusConfig[$status] ?? $statusConfig['processing'];
    $action = route('admin.withdrawals.update-status', $withdrawal->id);
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Header Info -->
    <div class="alert alert-{{ $config['color'] }} d-flex align-items-center mb-7">
        <div class="symbol symbol-circle symbol-50px bg-light-{{ $config['color'] }} me-4">
            <i class="ki-outline {{ $config['icon'] }} fs-2x text-{{ $config['color'] }}"></i>
        </div>
        <div class="flex-grow-1">
            <h4 class="mb-1 text-{{ $config['color'] }}">{{ $config['title'] }}</h4>
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
    <form id="kt_modal_form" action="{{ $action }}" data-method="POST">
        @csrf
        <input type="hidden" name="status" value="{{ $status }}">

        @if($status === 'processing' || $status === 'completed')
            <!-- Admin Notes -->
            <div class="mb-7">
                <label class="form-label fw-bold fs-5">Admin Notes (Optional)</label>
                <textarea class="form-control"
                          name="admin_notes"
                          rows="3"
                          placeholder="Add any notes about this action..."></textarea>
                <div class="form-text">Optional notes for this status update</div>
            </div>
        @endif

        @if($status === 'cancelled')
            <!-- Admin Notes for Cancel -->
            <div class="mb-7">
                <label class="form-label fw-bold fs-5">Admin Notes (Optional)</label>
                <textarea class="form-control"
                          name="admin_notes"
                          rows="3"
                          placeholder="Add any notes about why this is being cancelled..."></textarea>
                <div class="form-text">Optional notes for cancellation</div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="text-center pt-5">
            <button type="button" class="btn btn-light me-3 close" data-bs-dismiss="modal">
                <i class="ki-outline ki-cross fs-4"></i>
                Cancel
            </button>
            <button type="submit" class="btn btn-{{ $config['color'] }}">
                <i class="ki-outline {{ $config['icon'] }} fs-4 me-1"></i>
                {{ $config['label'] }}
            </button>
        </div>
    </form>
</div>

