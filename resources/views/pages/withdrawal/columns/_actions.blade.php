@php
    $isPending = $model->status === \App\Models\WithdrawalRequest::STATUS_PENDING;
    $isProcessing = $model->status === \App\Models\WithdrawalRequest::STATUS_PROCESSING;
    $isCompleted = $model->status === \App\Models\WithdrawalRequest::STATUS_COMPLETED;
    $isRejected = $model->status === \App\Models\WithdrawalRequest::STATUS_REJECTED;
    $canApprove = $isPending && $model->money_withdrawn;
    $canReject = $isPending || $isProcessing;
    $canProcess = $isPending && $model->money_withdrawn;
    $canComplete = $isProcessing && $model->money_withdrawn;
@endphp

<div class="d-flex justify-content-end gap-2">
    <!-- View Details -->
    <a href="#"
       class="btn btn-icon btn-light-primary btn-sm has_action"
       data-type="show"
       data-action="{{ route('admin.withdrawals.show', $model->id) }}"
       data-bs-toggle="tooltip"
       title="View withdrawal details">
        <i class="ki-outline ki-eye fs-4"></i>
    </a>

    <!-- Copy Reference -->
    <button class="btn btn-icon btn-light-info btn-sm"
            onclick="navigator.clipboard.writeText('{{ $model->reference_number }}')"
            data-bs-toggle="tooltip"
            title="Copy Reference Number">
        <i class="ki-outline ki-copy fs-4"></i>
    </button>

    <!-- More Actions Dropdown -->
    <div class="dropdown">
        <button class="btn btn-icon btn-light btn-sm"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="ki-outline ki-dots-vertical fs-4"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <!-- View User -->
            @if($model->user)
                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="show"
                       data-action="{{ route('admin.users.show', $model->user_id) }}">
                        <i class="ki-outline ki-user fs-5 me-2"></i>
                        View User
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
            @endif

            <!-- Approve / Start Processing -->
            @if($canApprove)
                <li>
                    <a class="dropdown-item text-success admin-action-btn"
                       href="#"
                       data-action="{{ route('admin.withdrawals.update-status', $model->id) }}?status=processing"
                       data-method="POST"
                       data-confirm="true"
                       data-confirm-text="Are you sure you want to approve and start processing this withdrawal request?">
                        <i class="ki-outline ki-check-circle fs-5 me-2"></i>
                        Approve & Process
                    </a>
                </li>
            @endif

            <!-- Complete -->
            @if($canComplete)
                <li>
                    <a class="dropdown-item text-success admin-action-btn"
                       href="#"
                       data-action="{{ route('admin.withdrawals.update-status', $model->id) }}?status=completed"
                       data-method="POST"
                       data-confirm="true"
                       data-confirm-text="Are you sure you want to mark this withdrawal as completed? This will deduct the amount from the wallet.">
                        <i class="ki-outline ki-check fs-5 me-2"></i>
                        Mark as Completed
                    </a>
                </li>
            @endif

            <!-- Reject -->
            @if($canReject)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger has_action"
                       href="#"
                       data-type="reject"
                       data-action="{{ route('admin.withdrawals.reject-form', $model->id) }}">
                        <i class="ki-outline ki-cross-circle fs-5 me-2"></i>
                        Reject Request
                    </a>
                </li>
            @endif

        </ul>
    </div>
</div>
