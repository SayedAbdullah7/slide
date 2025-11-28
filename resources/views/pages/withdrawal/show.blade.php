@php
    $statusConfig = [
        'pending' => ['color' => 'warning', 'icon' => 'ki-time', 'label' => 'Pending'],
        'processing' => ['color' => 'info', 'icon' => 'ki-arrows-circle', 'label' => 'Processing'],
        'completed' => ['color' => 'success', 'icon' => 'ki-check-circle', 'label' => 'Completed'],
        'rejected' => ['color' => 'danger', 'icon' => 'ki-cross-circle', 'label' => 'Rejected'],
        'cancelled' => ['color' => 'secondary', 'icon' => 'ki-cross', 'label' => 'Cancelled'],
    ];
    $currentStatus = $statusConfig[$withdrawal->status] ?? ['color' => 'secondary', 'icon' => 'ki-information', 'label' => ucfirst($withdrawal->status)];

    $canApprove = $withdrawal->status === \App\Models\WithdrawalRequest::STATUS_PENDING && $withdrawal->money_withdrawn;
    $canReject = in_array($withdrawal->status, [\App\Models\WithdrawalRequest::STATUS_PENDING, \App\Models\WithdrawalRequest::STATUS_PROCESSING]);
    $canComplete = $withdrawal->status === \App\Models\WithdrawalRequest::STATUS_PROCESSING && $withdrawal->money_withdrawn;
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

        <!-- Status Alert -->
        @if($withdrawal->status === \App\Models\WithdrawalRequest::STATUS_PENDING)
            <div class="alert alert-warning d-flex align-items-center mb-7">
                <i class="ki-outline ki-information-5 fs-2x text-warning me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Pending Withdrawal Request</h4>
                    <span>This withdrawal request is awaiting admin approval. Review and approve/reject if appropriate.</span>
                </div>
            </div>
        @elseif($withdrawal->status === \App\Models\WithdrawalRequest::STATUS_REJECTED)
            <div class="alert alert-danger d-flex align-items-center mb-7">
                <i class="ki-outline ki-information-5 fs-2x text-danger me-4"></i>
                <div class="d-flex flex-column flex-grow-1">
                    <h4 class="mb-1 text-danger">Rejected Withdrawal Request</h4>
                    @if($withdrawal->rejection_reason)
                        <span class="mb-2"><strong>Reason:</strong> {{ $withdrawal->rejection_reason }}</span>
                    @endif
                    <div class="d-flex align-items-center mt-2">
                        @php
                            // If money_withdrawn is false in rejected status, it means money was refunded (set to false after refund)
                            $wasRefunded = !$withdrawal->money_withdrawn;
                        @endphp
                        @if($wasRefunded)
                            <span class="badge badge-light-success me-2">
                                <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                                Money Refunded
                            </span>
                            <span class="text-muted fs-7">Money has been refunded to the user's wallet.</span>
                        @else
                            <span class="badge badge-light-warning me-2">
                                <i class="ki-outline ki-information fs-6 me-1"></i>
                                Money Not Refunded
                            </span>
                            <span class="text-muted fs-7">Money was not withdrawn from wallet, so no refund was processed.</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Withdrawal Header Card -->
        <div class="card mb-7">
            <div class="card-body">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between">
                    <!-- Withdrawal Icon & Info -->
                    <div class="d-flex align-items-center mb-5 mb-sm-0">
                        <div class="symbol symbol-circle symbol-75px bg-light-{{ $currentStatus['color'] }} me-5">
                            <i class="ki-outline {{ $currentStatus['icon'] }} fs-2x text-{{ $currentStatus['color'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-gray-900 fs-2 fw-bold me-3">Withdrawal Request</span>
                                <span class="badge badge-light-{{ $currentStatus['color'] }}">
                                    <i class="ki-outline {{ $currentStatus['icon'] }} fs-6 me-1"></i>
                                    {{ $currentStatus['label'] }}
                                </span>
                            </div>
                            <div class="text-muted fw-semibold fs-6">
                                Reference: <code>{{ $withdrawal->reference_number }}</code>
                            </div>
                            <div class="text-muted fs-7">
                                <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                {{ $withdrawal->created_at->format('M d, Y h:i A') }}
                                <span class="text-gray-600 ms-2">({{ $withdrawal->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Display -->
                    <div class="text-center text-sm-end">
                        <div class="text-danger fw-bold fs-2x mb-2">
                            - {{ number_format($withdrawal->amount, 2) }} <span class="fs-4">SAR</span>
                        </div>
                        <div class="text-muted fs-7 mb-2">Available: {{ number_format($withdrawal->available_balance, 2) }} SAR</div>
                        @if($withdrawal->money_withdrawn)
                            <span class="badge badge-light-success d-inline-flex align-items-center">
                                <i class="ki-outline ki-wallet fs-6 me-1"></i>
                                Money Withdrawn
                            </span>
                        @else
                            <span class="badge badge-light-warning d-inline-flex align-items-center">
                                <i class="ki-outline ki-wallet-cross fs-6 me-1"></i>
                                Money Not Withdrawn
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Information Grid -->
        <div class="row g-5 mb-7">
            <!-- User Information Card -->
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-outline ki-profile-circle fs-3 me-2"></i>
                            User Information
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($withdrawal->user)
                            <div class="d-flex flex-column">
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Full Name</div>
                                    <a href="#"
                                       class="text-gray-800 fw-bold text-hover-primary has_action"
                                       data-type="show"
                                       data-action="{{ route('admin.users.show', $withdrawal->user_id) }}">
                                        {{ $withdrawal->user->display_name }}
                                    </a>
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Email</div>
                                    <div class="text-gray-800">{{ $withdrawal->user->email ?? 'N/A' }}</div>
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Phone</div>
                                    <div class="text-gray-800">{{ $withdrawal->user->phone ?? 'N/A' }}</div>
                                </div>
                                @if($withdrawal->investor)
                                    <div class="mb-0">
                                        <div class="text-muted fs-7 mb-1">Investor Profile</div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-light-success">Investor ID: {{ $withdrawal->investor_id }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">User information not available</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bank Account Information Card -->
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-outline ki-bank fs-3 me-2"></i>
                            Bank Account Information
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($withdrawal->bankAccount)
                            @php $bank = $withdrawal->bankAccount->bank; @endphp
                            <div class="d-flex flex-column">
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Bank Name</div>
                                    <div class="text-gray-800 fw-semibold">{{ $bank->name_ar ?? 'N/A' }}</div>
                                    @if($bank->name_en)
                                        <div class="text-muted fs-8">{{ $bank->name_en }}</div>
                                    @endif
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Account Holder</div>
                                    <div class="text-gray-800">{{ $withdrawal->bankAccount->account_holder_name }}</div>
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Account Number</div>
                                    <div class="text-gray-800"><code>{{ $withdrawal->bankAccount->masked_account_number }}</code></div>
                                </div>
                                <div class="mb-0">
                                    <div class="text-muted fs-7 mb-1">IBAN</div>
                                    <div class="d-flex align-items-center">
                                        <code class="text-gray-800 me-2">{{ $withdrawal->bankAccount->iban }}</code>
                                        <button class="btn btn-sm btn-icon btn-light-primary"
                                                onclick="navigator.clipboard.writeText('{{ $withdrawal->bankAccount->iban }}')"
                                                data-bs-toggle="tooltip"
                                                title="Copy IBAN">
                                            <i class="ki-outline ki-copy fs-6"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @elseif($withdrawal->bank_details)
                            <div class="d-flex flex-column">
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Bank Name</div>
                                    <div class="text-gray-800 fw-semibold">{{ $withdrawal->bank_details['bank_name'] ?? 'N/A' }}</div>
                                    @if(isset($withdrawal->bank_details['bank_name_en']))
                                        <div class="text-muted fs-8">{{ $withdrawal->bank_details['bank_name_en'] }}</div>
                                    @endif
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Account Holder</div>
                                    <div class="text-gray-800">{{ $withdrawal->bank_details['account_holder_name'] ?? 'N/A' }}</div>
                                </div>
                                @if(isset($withdrawal->bank_details['masked_account_number']))
                                    <div class="mb-5">
                                        <div class="text-muted fs-7 mb-1">Account Number</div>
                                        <div class="text-gray-800"><code>{{ $withdrawal->bank_details['masked_account_number'] }}</code></div>
                                    </div>
                                @endif
                                <div class="mb-0">
                                    <div class="text-muted fs-7 mb-1">IBAN</div>
                                    <div class="d-flex align-items-center">
                                        <code class="text-gray-800 me-2">{{ $withdrawal->bank_details['iban'] ?? 'N/A' }}</code>
                                        @if(isset($withdrawal->bank_details['iban']))
                                            <button class="btn btn-sm btn-icon btn-light-primary"
                                                    onclick="navigator.clipboard.writeText('{{ $withdrawal->bank_details['iban'] }}')"
                                                    data-bs-toggle="tooltip"
                                                    title="Copy IBAN">
                                                <i class="ki-outline ki-copy fs-6"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">Bank account information not available</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal Details Card -->
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-information-4 fs-3 me-2"></i>
                    Withdrawal Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Request ID</div>
                            <div class="text-gray-800 fw-semibold">#{{ $withdrawal->id }}</div>
                        </div>
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Reference Number</div>
                            <div class="d-flex align-items-center">
                                <code class="text-gray-800 fw-bold fs-7 me-2">{{ $withdrawal->reference_number }}</code>
                                <button class="btn btn-sm btn-icon btn-light-primary"
                                        onclick="navigator.clipboard.writeText('{{ $withdrawal->reference_number }}')"
                                        data-bs-toggle="tooltip"
                                        title="Copy Reference">
                                    <i class="ki-outline ki-copy fs-6"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Amount</div>
                            <div class="text-danger fw-bold fs-4">{{ number_format($withdrawal->amount, 2) }} SAR</div>
                        </div>
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Available Balance (at time of request)</div>
                            <div class="text-gray-800 fw-semibold">{{ number_format($withdrawal->available_balance, 2) }} SAR</div>
                        </div>
                        <div class="mb-0">
                            <div class="text-muted fs-7 mb-1">Money Withdrawn Status</div>
                            @if($withdrawal->money_withdrawn)
                                <span class="badge badge-light-success d-inline-flex align-items-center">
                                    <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                                    Money Withdrawn
                                </span>
                                <div class="text-muted fs-8 mt-1">Money has been withdrawn from wallet</div>
                            @else
                                <span class="badge badge-light-warning d-inline-flex align-items-center">
                                    <i class="ki-outline ki-information fs-6 me-1"></i>
                                    Money Not Withdrawn
                                </span>
                                <div class="text-muted fs-8 mt-1">Money is still in wallet (or was refunded)</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Status</div>
                            <span class="badge badge-light-{{ $currentStatus['color'] }}">
                                <i class="ki-outline {{ $currentStatus['icon'] }} fs-6 me-1"></i>
                                {{ $currentStatus['label'] }}
                            </span>
                        </div>
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Created At</div>
                            <div class="text-gray-800">{{ $withdrawal->created_at->format('M d, Y h:i A') }}</div>
                            <div class="text-muted fs-8">{{ $withdrawal->created_at->diffForHumans() }}</div>
                        </div>
                        @if($withdrawal->completed_at)
                            <div class="mb-5">
                                <div class="text-muted fs-7 mb-1">Completed At</div>
                                <div class="text-gray-800">{{ $withdrawal->completed_at->format('M d, Y h:i A') }}</div>
                                <div class="text-muted fs-8">{{ $withdrawal->completed_at->diffForHumans() }}</div>
                            </div>
                        @endif
                        @if($withdrawal->processed_at)
                            <div class="mb-5">
                                <div class="text-muted fs-7 mb-1">Processed At</div>
                                <div class="text-gray-800">{{ $withdrawal->processed_at->format('M d, Y h:i A') }}</div>
                                <div class="text-muted fs-8">{{ $withdrawal->processed_at->diffForHumans() }}</div>
                            </div>
                        @endif
                        @if($withdrawal->action_by && $withdrawal->actionBy)
                            <div class="mb-0">
                                <div class="text-muted fs-7 mb-1">Action By</div>
                                <div class="text-gray-800">{{ $withdrawal->actionBy->name ?? 'Unknown Admin' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Notes & Rejection Reason Card -->
        @if($withdrawal->admin_notes || $withdrawal->rejection_reason)
            <div class="card mb-7">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-3 me-2"></i>
                        Admin Notes
                    </h3>
                </div>
                <div class="card-body">
                    @if($withdrawal->admin_notes)
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-2">Notes</div>
                            <div class="text-gray-800">{{ $withdrawal->admin_notes }}</div>
                        </div>
                    @endif
                    @if($withdrawal->rejection_reason)
                        <div class="mb-0">
                            <div class="text-muted fs-7 mb-2">Rejection Reason</div>
                            <div class="text-danger fw-semibold">{{ $withdrawal->rejection_reason }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="card mb-7">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    @if($withdrawal->status === \App\Models\WithdrawalRequest::STATUS_PENDING)
                        @if($withdrawal->money_withdrawn)
                            <a href="#"
                               class="btn btn-success admin-action-btn"
                               data-action="{{ route('admin.withdrawals.update-status', $withdrawal->id) }}?status=processing"
                               data-method="POST"
                               data-confirm="true"
                               data-confirm-text="Are you sure you want to approve and start processing this withdrawal request?">
                                <i class="ki-outline ki-check-circle fs-2 me-2"></i>
                                Approve & Process
                            </a>
                        @else
                            <button class="btn btn-success" disabled
                                    data-bs-toggle="tooltip"
                                    title="Cannot process: Money was not withdrawn from wallet">
                                <i class="ki-outline ki-check-circle fs-2 me-2"></i>
                                Approve & Process (Disabled)
                            </button>
                        @endif
                    @endif

                    @if($withdrawal->status === \App\Models\WithdrawalRequest::STATUS_PROCESSING)
                        @if($withdrawal->money_withdrawn)
                            <a href="#"
                               class="btn btn-success admin-action-btn"
                               data-action="{{ route('admin.withdrawals.update-status', $withdrawal->id) }}?status=completed"
                               data-method="POST"
                               data-confirm="true"
                               data-confirm-text="Are you sure you want to mark this withdrawal as completed?">
                                <i class="ki-outline ki-check fs-2 me-2"></i>
                                Mark as Completed
                            </a>
                        @else
                            <button class="btn btn-success" disabled
                                    data-bs-toggle="tooltip"
                                    title="Cannot complete: Money was not withdrawn from wallet">
                                <i class="ki-outline ki-check fs-2 me-2"></i>
                                Mark as Completed (Disabled)
                            </button>
                        @endif
                    @endif

                    @if($canReject)
                        <a href="#"
                           class="btn btn-danger has_action"
                           data-type="reject"
                           data-action="{{ route('admin.withdrawals.reject-form', $withdrawal->id) }}">
                            <i class="ki-outline ki-cross-circle fs-2 me-2"></i>
                            Reject Request
                        </a>
                    @endif


                    <a href="#"
                       class="btn btn-light has_action"
                       data-type="index"
                       data-action="{{ route('admin.withdrawals.index') }}">
                        <i class="ki-outline ki-arrow-left fs-2 me-2"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
