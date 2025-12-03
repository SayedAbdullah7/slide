@php
    $isPending = $model->status === \App\Models\BankTransferRequest::STATUS_PENDING;
    $isApproved = $model->status === \App\Models\BankTransferRequest::STATUS_APPROVED;
    $isRejected = $model->status === \App\Models\BankTransferRequest::STATUS_REJECTED;
    $canApprove = $isPending;
    $canReject = $isPending;
@endphp

<div class="d-flex justify-content-end gap-2">
    <!-- View Details -->
    <a href="#"
       class="btn btn-icon btn-light-primary btn-sm has_action"
       data-type="show"
       data-action="{{ route('admin.bank-transfers.show', $model->id) }}"
       data-bs-toggle="tooltip"
       title="View transfer details">
        <i class="ki-outline ki-eye fs-4"></i>
    </a>

    <!-- Copy Reference -->
    @if($model->transfer_reference)
        <button class="btn btn-icon btn-light-info btn-sm"
                onclick="navigator.clipboard.writeText('{{ $model->transfer_reference }}')"
                data-bs-toggle="tooltip"
                title="Copy Reference Number">
            <i class="ki-outline ki-copy fs-4"></i>
        </button>
    @endif

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

            <!-- Approve -->
            @if($canApprove)
                <li>
                    <a class="dropdown-item text-success has_action"
                       href="#"
                       data-type="approve"
                       data-action="{{ route('admin.bank-transfers.approve-form', $model->id) }}">
                        <i class="ki-outline ki-check-circle fs-5 me-2"></i>
                        Approve Transfer
                    </a>
                </li>
            @endif

            <!-- Reject -->
            @if($canReject)
                <li>
                    <a class="dropdown-item text-danger has_action"
                       href="#"
                       data-type="reject"
                       data-action="{{ route('admin.bank-transfers.reject-form', $model->id) }}">
                        <i class="ki-outline ki-cross-circle fs-5 me-2"></i>
                        Reject Request
                    </a>
                </li>
            @endif

        </ul>
    </div>
</div>

