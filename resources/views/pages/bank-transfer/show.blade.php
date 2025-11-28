@php
    $statusConfig = [
        'pending' => ['color' => 'warning', 'icon' => 'ki-time', 'label' => 'Pending'],
        'approved' => ['color' => 'success', 'icon' => 'ki-check-circle', 'label' => 'Approved'],
        'rejected' => ['color' => 'danger', 'icon' => 'ki-cross-circle', 'label' => 'Rejected'],
    ];
    $currentStatus = $statusConfig[$bankTransfer->status] ?? ['color' => 'secondary', 'icon' => 'ki-information', 'label' => ucfirst($bankTransfer->status)];

    $canApprove = $bankTransfer->status === \App\Models\BankTransferRequest::STATUS_PENDING;
    $canReject = $bankTransfer->status === \App\Models\BankTransferRequest::STATUS_PENDING;
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

        <!-- Status Alert -->
        @if($bankTransfer->status === \App\Models\BankTransferRequest::STATUS_PENDING)
            <div class="alert alert-warning d-flex align-items-center mb-7">
                <i class="ki-outline ki-information-5 fs-2x text-warning me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Pending Bank Transfer Request</h4>
                    <span>Review the receipt, verify transfer details, and approve or reject the request.</span>
                </div>
            </div>
        @elseif($bankTransfer->status === \App\Models\BankTransferRequest::STATUS_APPROVED)
            <div class="alert alert-success d-flex align-items-center mb-7">
                <i class="ki-outline ki-check-circle fs-2x text-success me-4"></i>
                <div class="d-flex flex-column flex-grow-1">
                    <h4 class="mb-1 text-success">Approved Bank Transfer</h4>
                    <span>This transfer has been approved and the amount has been added to the investor's wallet.</span>
                </div>
            </div>
        @elseif($bankTransfer->status === \App\Models\BankTransferRequest::STATUS_REJECTED)
            <div class="alert alert-danger d-flex align-items-center mb-7">
                <i class="ki-outline ki-information-5 fs-2x text-danger me-4"></i>
                <div class="d-flex flex-column flex-grow-1">
                    <h4 class="mb-1 text-danger">Rejected Bank Transfer Request</h4>
                    @if($bankTransfer->rejection_reason)
                        <span class="mb-2"><strong>Reason:</strong> {{ $bankTransfer->rejection_reason }}</span>
                    @endif
                </div>
            </div>
        @endif

        <!-- Bank Transfer Header Card -->
        <div class="card mb-7">
            <div class="card-body">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between">
                    <!-- Transfer Icon & Info -->
                    <div class="d-flex align-items-center mb-5 mb-sm-0">
                        <div class="symbol symbol-circle symbol-75px bg-light-{{ $currentStatus['color'] }} me-5">
                            <i class="ki-outline {{ $currentStatus['icon'] }} fs-2x text-{{ $currentStatus['color'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-gray-900 fs-2 fw-bold me-3">Bank Transfer Request</span>
                                <span class="badge badge-light-{{ $currentStatus['color'] }}">
                                    <i class="ki-outline {{ $currentStatus['icon'] }} fs-6 me-1"></i>
                                    {{ $currentStatus['label'] }}
                                </span>
                            </div>
                            @if($bankTransfer->transfer_reference)
                                <div class="text-muted fw-semibold fs-6">
                                    Reference: <code>{{ $bankTransfer->transfer_reference }}</code>
                                </div>
                            @else
                                <div class="text-muted fs-7">
                                    <span class="badge badge-light-warning">Reference not set yet</span>
                                </div>
                            @endif
                            <div class="text-muted fs-7">
                                <i class="ki-outline ki-calendar fs-6 me-1"></i>
                                {{ $bankTransfer->created_at->format('M d, Y h:i A') }}
                                <span class="text-gray-600 ms-2">({{ $bankTransfer->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Display -->
                    <div class="text-center text-sm-end">
                        @if($bankTransfer->amount)
                            <div class="text-primary fw-bold fs-2x mb-2">
                                + {{ number_format($bankTransfer->amount, 2) }} <span class="fs-4">SAR</span>
                            </div>
                        @else
                            <div class="text-muted fw-bold fs-2x mb-2">
                                Amount: <span class="fs-4">Not Set</span>
                            </div>
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
                        @if($bankTransfer->user)
                            <div class="d-flex flex-column">
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Full Name</div>
                                    <a href="#"
                                       class="text-gray-800 fw-bold text-hover-primary has_action"
                                       data-type="show"
                                       data-action="{{ route('admin.users.show', $bankTransfer->user_id) }}">
                                        {{ $bankTransfer->user->display_name }}
                                    </a>
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Email</div>
                                    <div class="text-gray-800">{{ $bankTransfer->user->email ?? 'N/A' }}</div>
                                </div>
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Phone</div>
                                    <div class="text-gray-800">{{ $bankTransfer->user->phone ?? 'N/A' }}</div>
                                </div>
                                @if($bankTransfer->investor)
                                    <div class="mb-0">
                                        <div class="text-muted fs-7 mb-1">Investor Profile</div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-light-success">Investor ID: {{ $bankTransfer->investor_id }}</span>
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

            <!-- Transfer Details Card -->
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-outline ki-bank fs-3 me-2"></i>
                            Transfer Details
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($bankTransfer->bank)
                            <div class="d-flex flex-column">
                                <div class="mb-5">
                                    <div class="text-muted fs-7 mb-1">Bank Name</div>
                                    <div class="text-gray-800 fw-semibold">{{ $bankTransfer->bank->name_ar }}</div>
                                    @if($bankTransfer->bank->name_en)
                                        <div class="text-muted fs-8">{{ $bankTransfer->bank->name_en }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-muted">Transfer details not entered yet</div>
                        @endif

                        @if($bankTransfer->transfer_reference)
                            <div class="mt-5">
                                <div class="text-muted fs-7 mb-1">Transfer Reference</div>
                                <div class="d-flex align-items-center">
                                    <code class="text-gray-800 fw-bold me-2">{{ $bankTransfer->transfer_reference }}</code>
                                    <button class="btn btn-sm btn-icon btn-light-primary"
                                            onclick="navigator.clipboard.writeText('{{ $bankTransfer->transfer_reference }}')"
                                            data-bs-toggle="tooltip"
                                            title="Copy Reference">
                                        <i class="ki-outline ki-copy fs-6"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($bankTransfer->amount)
                            <div class="mt-5">
                                <div class="text-muted fs-7 mb-1">Transfer Amount</div>
                                <div class="text-primary fw-bold fs-4">{{ number_format($bankTransfer->amount, 2) }} SAR</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Card -->
        @if($bankTransfer->receipt_file)
            <div class="card mb-7">
                <div class="card-header flex-wrap gap-2">
                    <div class="card-title d-flex align-items-center">
                        <i class="ki-outline ki-file fs-3 me-2"></i>
                        Transfer Receipt
                    </div>
                    <div class="card-toolbar ms-auto gap-2">
                        <a href="{{ $bankTransfer->receipt_url }}" download class="btn btn-sm btn-light-primary">
                            <i class="ki-outline ki-file-down fs-5 me-1"></i>
                            Download
                        </a>
                        <a href="{{ $bankTransfer->receipt_url }}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="ki-outline ki-eye fs-5 me-1"></i>
                            Open Full Size
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <div class="text-muted fs-7 mb-2">File Name</div>
                        <div class="text-gray-800">{{ $bankTransfer->receipt_file_name }}</div>
                    </div>

                    @if($bankTransfer->receipt_type === 'image')
                        <div class="text-center bg-light rounded p-5">
                            <img src="{{ $bankTransfer->receipt_url }}"
                                 alt="Receipt"
                                 class="img-fluid rounded shadow-sm"
                                 style="max-height: 700px; cursor: pointer;"
                                 onclick="window.open('{{ $bankTransfer->receipt_url }}', '_blank')">
                        </div>
                    @elseif($bankTransfer->receipt_type === 'pdf')
                        <div class="text-center bg-light rounded p-3">
                            <iframe src="{{ $bankTransfer->receipt_url }}"
                                    frameborder="0"
                                    width="100%"
                                    height="700px"
                                    class="rounded shadow-sm"></iframe>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="ki-outline ki-information-5 fs-2x me-3"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">File type not previewable</span>
                                <span class="fs-7">Please download the file to view it</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Bank Transfer Details Card -->
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-information-4 fs-3 me-2"></i>
                    Request Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-1">Request ID</div>
                            <div class="text-gray-800 fw-semibold">#{{ $bankTransfer->id }}</div>
                        </div>
                        @if($bankTransfer->transfer_reference)
                            <div class="mb-5">
                                <div class="text-muted fs-7 mb-1">Transfer Reference</div>
                                <div class="d-flex align-items-center">
                                    <code class="text-gray-800 fw-bold fs-7 me-2">{{ $bankTransfer->transfer_reference }}</code>
                                    <button class="btn btn-sm btn-icon btn-light-primary"
                                            onclick="navigator.clipboard.writeText('{{ $bankTransfer->transfer_reference }}')"
                                            data-bs-toggle="tooltip"
                                            title="Copy Reference">
                                        <i class="ki-outline ki-copy fs-6"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                        @if($bankTransfer->amount)
                            <div class="mb-0">
                                <div class="text-muted fs-7 mb-1">Amount</div>
                                <div class="text-primary fw-bold fs-4">{{ number_format($bankTransfer->amount, 2) }} SAR</div>
                            </div>
                        @endif
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
                            <div class="text-gray-800">{{ $bankTransfer->created_at->format('M d, Y h:i A') }}</div>
                            <div class="text-muted fs-8">{{ $bankTransfer->created_at->diffForHumans() }}</div>
                        </div>
                        @if($bankTransfer->processed_at)
                            <div class="mb-5">
                                <div class="text-muted fs-7 mb-1">Processed At</div>
                                <div class="text-gray-800">{{ $bankTransfer->processed_at->format('M d, Y h:i A') }}</div>
                                <div class="text-muted fs-8">{{ $bankTransfer->processed_at->diffForHumans() }}</div>
                            </div>
                        @endif
                        @if($bankTransfer->action_by && $bankTransfer->actionBy)
                            <div class="mb-0">
                                <div class="text-muted fs-7 mb-1">Action By</div>
                                <div class="text-gray-800">{{ $bankTransfer->actionBy->name ?? 'Unknown Admin' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Notes & Rejection Reason Card -->
        @if($bankTransfer->admin_notes || $bankTransfer->rejection_reason)
            <div class="card mb-7">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-document fs-3 me-2"></i>
                        Admin Notes
                    </h3>
                </div>
                <div class="card-body">
                    @if($bankTransfer->admin_notes)
                        <div class="mb-5">
                            <div class="text-muted fs-7 mb-2">Notes</div>
                            <div class="text-gray-800">{{ $bankTransfer->admin_notes }}</div>
                        </div>
                    @endif
                    @if($bankTransfer->rejection_reason)
                        <div class="mb-0">
                            <div class="text-muted fs-7 mb-2">Rejection Reason</div>
                            <div class="text-danger fw-semibold">{{ $bankTransfer->rejection_reason }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="card mb-7">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    @if($canApprove)
                        <a href="#"
                           class="btn btn-success has_action"
                           data-type="approve"
                           data-action="{{ route('admin.bank-transfers.approve-form', $bankTransfer->id) }}">
                            <i class="ki-outline ki-check-circle fs-2 me-2"></i>
                            Approve Transfer
                        </a>
                    @endif

                    @if($canReject)
                        <a href="#"
                           class="btn btn-danger has_action"
                           data-type="reject"
                           data-action="{{ route('admin.bank-transfers.reject-form', $bankTransfer->id) }}">
                            <i class="ki-outline ki-cross-circle fs-2 me-2"></i>
                            Reject Request
                        </a>
                    @endif

                    <a href="#"
                       class="btn btn-light has_action"
                       data-type="index"
                       data-action="{{ route('admin.bank-transfers.index') }}">
                        <i class="ki-outline ki-arrow-left fs-2 me-2"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

