@php
    $isDeposit = $transaction->type === 'deposit';
    $isWithdrawal = $transaction->type === 'withdraw';
    $isConfirmed = $transaction->confirmed;
    $isPending = !$transaction->confirmed;

    // Amount calculations
    $amountInSAR = $transaction->amount / 100;
    $amountInCents = $transaction->amount;

    // Payable information
    $payableType = $transaction->payable ? class_basename($transaction->payable_type) : 'Unknown';
    $payableName = $transaction->payable_name ?? 'Unknown';

    // Colors and icons based on transaction type and status
    $typeColor = $isDeposit ? 'success' : 'warning';
    $typeIcon = $isDeposit ? 'ki-arrow-down' : 'ki-arrow-up';
    $statusColor = $isConfirmed ? 'success' : 'warning';
    $amountColor = $isDeposit ? 'success' : 'danger';
    $amountSign = $isDeposit ? '+' : '-';
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Transaction Status Alert -->
    @if($isPending)
        <div class="alert alert-warning d-flex align-items-center mb-7">
            <i class="ki-outline ki-information-5 fs-2x text-warning me-4"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-warning">Pending Transaction</h4>
                <span>This transaction is awaiting confirmation. Review and confirm if appropriate.</span>
            </div>
        </div>
    @endif

    <!-- Transaction Header Card -->
    <div class="card mb-7">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between">
                <!-- Transaction Icon & Type -->
                <div class="d-flex align-items-center mb-5 mb-sm-0">
                    <div class="symbol symbol-circle symbol-75px bg-light-{{ $typeColor }} me-5">
                        <i class="ki-outline {{ $typeIcon }} fs-2x text-{{ $typeColor }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-900 fs-2 fw-bold me-3">{{ ucfirst($transaction->type) }}</span>
                            <span class="badge badge-light-{{ $typeColor }}">
                                <i class="ki-outline {{ $typeIcon }} fs-6 me-1"></i>
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </div>
                        <div class="text-muted fw-semibold fs-6">
                            Transaction ID: #{{ $transaction->id }}
                        </div>
                        <div class="text-muted fs-7">
                            <i class="ki-outline ki-calendar fs-6 me-1"></i>
                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                            <span class="text-gray-600 ms-2">({{ $transaction->created_at->diffForHumans() }})</span>
                        </div>
                    </div>
                </div>

                <!-- Amount Display -->
                <div class="text-center text-sm-end">
                    <div class="text-{{ $amountColor }} fw-bold fs-2x mb-2">
                        {{ $amountSign }} {{ number_format($amountInSAR, 2) }} <span class="fs-4">SAR</span>
                    </div>
                    <div class="text-muted fs-7">{{ number_format($amountInCents) }} cents</div>
                    @if($isConfirmed)
                        <span class="badge badge-light-success mt-2">
                            <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                            Confirmed
                        </span>
                    @else
                        <span class="badge badge-light-warning mt-2">
                            <i class="ki-outline ki-time fs-6 me-1"></i>
                            Pending
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Information Grid -->
    <div class="row g-5 mb-7">
        <!-- Account Holder Card -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-profile-circle fs-3 me-2"></i>
                        Account Holder
                    </h3>
                </div>
                <div class="card-body">
                    @if($transaction->payable)
                        <div class="d-flex align-items-center mb-5">
                            @php
                                $accountIcon = match($payableType) {
                                    'User' => 'ki-user',
                                    'InvestorProfile' => 'ki-chart-line-up',
                                    'OwnerProfile' => 'ki-briefcase',
                                    default => 'ki-profile-circle'
                                };
                                $accountColor = match($payableType) {
                                    'User' => 'primary',
                                    'InvestorProfile' => 'success',
                                    'OwnerProfile' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="symbol symbol-circle symbol-50px bg-light-{{ $accountColor }} me-4">
                                <i class="ki-outline {{ $accountIcon }} fs-2x text-{{ $accountColor }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-gray-800 fw-bold fs-5 mb-1">{{ $payableName }}</div>
                                <span class="badge badge-light-{{ $accountColor }}">{{ $payableType }}</span>
                            </div>
                        </div>

                        <div class="separator my-4"></div>

                        <div class="row">
                            <div class="col-6 mb-4">
                                <div class="text-muted fs-7 mb-1">Account Type</div>
                                <div class="text-gray-800 fw-semibold">{{ $payableType }}</div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="text-muted fs-7 mb-1">Account ID</div>
                                <div class="text-gray-800 fw-semibold">#{{ $transaction->payable_id }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fs-7 mb-1">Wallet ID</div>
                                <div class="text-gray-800 fw-semibold">#{{ $transaction->wallet_id }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fs-7 mb-1">Current Balance</div>
                                @if($transaction->payable && method_exists($transaction->payable, 'getWalletBalance'))
                                    <div class="text-success fw-bold">{{ number_format($transaction->payable->getWalletBalance(), 2) }} SAR</div>
                                @else
                                    <div class="text-muted">N/A</div>
                                @endif
                            </div>
                        </div>

                        @if($transaction->payable)
                            <div class="separator my-4"></div>
                            <a href="{{ $transaction->payable_type === 'App\\Models\\User' ? route('admin.users.show', $transaction->payable_id) : '#' }}"
                               class="btn btn-light-{{ $accountColor }} w-100">
                                <i class="ki-outline ki-eye fs-4 me-2"></i>
                                View Account Details
                            </a>
                        @endif
                    @else
                        <div class="text-center text-muted py-10">
                            <i class="ki-outline ki-information-5 fs-3x mb-5"></i>
                            <div>Account information not available</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Details Card -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-information fs-3 me-2"></i>
                        Transaction Details
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <i class="ki-outline ki-wallet fs-2 text-{{ $typeColor }} me-3"></i>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-7 mb-1">Transaction Type</div>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-{{ $typeColor }} me-2">
                                        <i class="ki-outline {{ $typeIcon }} fs-6 me-1"></i>
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <i class="ki-outline ki-shield-tick fs-2 text-{{ $statusColor }} me-3"></i>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-7 mb-1">Status</div>
                                @if($isConfirmed)
                                    <span class="badge badge-light-success">
                                        <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                                        Confirmed
                                    </span>
                                @else
                                    <span class="badge badge-light-warning">
                                        <i class="ki-outline ki-time fs-6 me-1"></i>
                                        Pending Confirmation
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="d-flex align-items-center">
                            <i class="ki-outline ki-barcode fs-2 text-primary me-3"></i>
                            <div class="flex-grow-1">
                                <div class="text-muted fs-7 mb-1">Transaction UUID</div>
                                <div class="d-flex align-items-center">
                                    <code class="text-gray-700 fs-7 me-2">{{ $transaction->uuid }}</code>
                                    <button class="btn btn-sm btn-icon btn-light-primary"
                                            onclick="navigator.clipboard.writeText('{{ $transaction->uuid }}')"
                                            data-bs-toggle="tooltip"
                                            title="Copy UUID">
                                        <i class="ki-outline ki-copy fs-6"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="separator my-4"></div>

                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="text-muted fs-7 mb-1">Description</div>
                            <div class="text-gray-800">
                                @if($transaction->meta && isset($transaction->meta['description']))
                                    {{ $transaction->meta['description'] }}
                                @else
                                    <span class="fst-italic text-muted">
                                        {{ $isDeposit ? 'Wallet deposit' : 'Wallet withdrawal' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amount Breakdown Card -->
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-financial-schedule fs-3 me-2"></i>
                Amount Breakdown
            </h3>
        </div>
        <div class="card-body">
            <div class="row g-5">
                <div class="col-md-4">
                    <div class="border border-dashed border-{{ $amountColor }} rounded p-5 text-center">
                        <i class="ki-outline ki-wallet fs-3x text-{{ $amountColor }} mb-3"></i>
                        <div class="text-muted fs-7 mb-2">Amount in SAR</div>
                        <div class="text-{{ $amountColor }} fw-bold fs-2">
                            {{ $amountSign }} {{ number_format($amountInSAR, 2) }}
                        </div>
                        <div class="text-muted fs-8 mt-1">Saudi Riyal</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border border-dashed border-gray-300 rounded p-5 text-center">
                        <i class="ki-outline ki-calculator fs-3x text-gray-600 mb-3"></i>
                        <div class="text-muted fs-7 mb-2">Amount in Cents</div>
                        <div class="text-gray-800 fw-bold fs-2">{{ number_format($amountInCents) }}</div>
                        <div class="text-muted fs-8 mt-1">Precision Units</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border border-dashed border-gray-300 rounded p-5 text-center">
                        <i class="ki-outline ki-information-4 fs-3x text-primary mb-3"></i>
                        <div class="text-muted fs-7 mb-2">Exchange Rate</div>
                        <div class="text-gray-800 fw-bold fs-2">1:100</div>
                        <div class="text-muted fs-8 mt-1">SAR to Cents</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metadata Card -->
    @if($transaction->meta && !empty($transaction->meta))
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-code fs-3 me-2"></i>
                    Transaction Metadata
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-info">{{ count($transaction->meta) }} Fields</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-200px">Key</th>
                                <th class="min-w-300px">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->meta as $key => $value)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-outline ki-abstract-26 fs-3 text-primary me-3"></i>
                                            <span class="text-gray-800 fw-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if(is_array($value))
                                            <pre class="bg-light p-3 rounded mb-0"><code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code></pre>
                                        @elseif(is_bool($value))
                                            <span class="badge badge-light-{{ $value ? 'success' : 'danger' }}">
                                                {{ $value ? 'True' : 'False' }}
                                            </span>
                                        @elseif(is_numeric($value))
                                            <span class="text-primary fw-semibold">{{ $value }}</span>
                                        @else
                                            <span class="text-gray-800">{{ $value }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Timeline Card -->
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-time fs-3 me-2"></i>
                Transaction Timeline
            </h3>
        </div>
        <div class="card-body">
            <div class="timeline-label">
                <!-- Created -->
                <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-6" style="width: 150px;">
                        {{ $transaction->created_at->format('H:i A') }}
                    </div>
                    <div class="timeline-badge">
                        <i class="ki-outline ki-plus-circle fs-1 text-success"></i>
                    </div>
                    <div class="fw-semibold text-gray-700 ps-3">
                        <div class="fs-6 fw-bold text-gray-800">Transaction Created</div>
                        <div class="fs-7 text-muted">
                            {{ $transaction->created_at->format('M d, Y') }}
                            <span class="text-gray-600">({{ $transaction->created_at->diffForHumans() }})</span>
                        </div>
                    </div>
                </div>

                <!-- Confirmed (if applicable) -->
                @if($isConfirmed)
                    <div class="timeline-item">
                        <div class="timeline-label fw-bold text-gray-800 fs-6" style="width: 150px;">
                            {{ $transaction->updated_at->format('H:i A') }}
                        </div>
                        <div class="timeline-badge">
                            <i class="ki-outline ki-check-circle fs-1 text-success"></i>
                        </div>
                        <div class="fw-semibold text-gray-700 ps-3">
                            <div class="fs-6 fw-bold text-gray-800">Transaction Confirmed</div>
                            <div class="fs-7 text-muted">
                                {{ $transaction->updated_at->format('M d, Y') }}
                                <span class="text-gray-600">({{ $transaction->updated_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="timeline-item">
                        <div class="timeline-label fw-bold text-gray-800 fs-6" style="width: 150px;">
                            Pending
                        </div>
                        <div class="timeline-badge">
                            <i class="ki-outline ki-time fs-1 text-warning"></i>
                        </div>
                        <div class="fw-semibold text-gray-700 ps-3">
                            <div class="fs-6 fw-bold text-warning">Awaiting Confirmation</div>
                            <div class="fs-7 text-muted">Transaction needs to be confirmed</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Action Buttons -->
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <!-- Primary Actions -->
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-light-primary close" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-4"></i>
                    Close
                </button>

                <button class="btn btn-light-info"
                        onclick="navigator.clipboard.writeText('{{ $transaction->uuid }}')"
                        data-bs-toggle="tooltip"
                        title="Copy transaction UUID">
                    <i class="ki-outline ki-copy fs-4"></i>
                    Copy UUID
                </button>
            </div>

            <!-- Secondary Actions -->
            <div class="d-flex flex-wrap gap-2">
                @if($isPending)
                    <button class="btn btn-success" onclick="confirmTransaction({{ $transaction->id }})">
                        <i class="ki-outline ki-check-circle fs-4"></i>
                        Confirm Transaction
                    </button>
                @endif

                <button class="btn btn-light-warning" onclick="exportTransaction({{ $transaction->id }})">
                    <i class="ki-outline ki-file-down fs-4"></i>
                    Export Details
                </button>

                @if($transaction->payable)
                    <a href="{{ $transaction->payable_type === 'App\\Models\\User' ? route('admin.users.show', $transaction->payable_id) : '#' }}"
                       class="btn btn-light-primary">
                        <i class="ki-outline ki-user fs-4"></i>
                        View Account
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function confirmTransaction(id) {
    if (confirm('Are you sure you want to confirm this transaction?')) {
        fetch(`/admin/transactions/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error confirming transaction');
            console.error('Error:', error);
        });
    }
}

function exportTransaction(id) {
    window.location.href = `/admin/transactions/${id}/export`;
}
</script>




