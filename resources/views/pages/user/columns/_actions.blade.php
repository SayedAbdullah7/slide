@php
    $hasInvestor = $model->investorProfile !== null;
    $hasOwner = $model->ownerProfile !== null;
    $hasWallet = $hasInvestor || $hasOwner;
    $investorBalance = $hasInvestor ? $model->investorProfile->getWalletBalance() : 0;
    $ownerBalance = $hasOwner ? $model->ownerProfile->getWalletBalance() : 0;
    $totalInvestments = $hasInvestor ? $model->investorProfile->investments()->count() : 0;
@endphp

<div class="d-flex justify-content-end gap-2">
    <!-- Quick View Button -->
    <a href="#"
       class="btn btn-icon btn-light-primary btn-sm has_action"
       data-type="show"
       data-action="{{ route('user.show', $model->id) }}"
       data-bs-toggle="tooltip"
       title="View user details">
        <i class="ki-outline ki-eye fs-4"></i>
    </a>

    <!-- Quick Edit Button -->
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('user.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Edit user">
        <i class="ki-outline ki-pencil fs-4"></i>
    </a>

    <!-- Actions Dropdown -->
    <div class="dropdown">
        <button class="btn btn-icon btn-light btn-sm"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="More actions">
            <i class="ki-outline ki-dots-vertical fs-4"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
            <!-- User Management Section -->
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-user-edit fs-6 me-1"></i>
                User Management
            </li>

            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="show"
                   data-action="{{ route('user.show', $model->id) }}">
                    <i class="ki-outline ki-eye fs-5 me-2 text-primary"></i>
                    View Details
                </a>
            </li>

            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="edit"
                   data-action="{{ route('user.edit', $model->id) }}">
                    <i class="ki-outline ki-pencil fs-5 me-2 text-warning"></i>
                    Edit User
                </a>
            </li>

            @if($model->is_active)
                <li>
                    <a class="dropdown-item" href="#" onclick="toggleUserStatus({{ $model->id }}, false)">
                        <i class="ki-outline ki-shield-cross fs-5 me-2 text-danger"></i>
                        Deactivate Account
                    </a>
                </li>
            @else
                <li>
                    <a class="dropdown-item" href="#" onclick="toggleUserStatus({{ $model->id }}, true)">
                        <i class="ki-outline ki-shield-tick fs-5 me-2 text-success"></i>
                        Activate Account
                    </a>
                </li>
            @endif

            <li><hr class="dropdown-divider"></li>

            <!-- Profile Management Section -->
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-profile-user fs-6 me-1"></i>
                Profile Management
            </li>

            @if(!$hasInvestor)
                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="create"
                       data-action="{{ route('user.investor-profile.create', $model->id) }}">
                        <i class="ki-outline ki-plus-circle fs-5 me-2 text-success"></i>
                        Add Investor Profile
                    </a>
                </li>
            @else
                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="edit"
                       data-action="{{ route('user.investor-profile.edit', $model->id) }}">
                        <i class="ki-outline ki-chart-line-up fs-5 me-2 text-primary"></i>
                        Edit Investor Profile
                    </a>
                </li>
            @endif

            @if(!$hasOwner)
                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="create"
                       data-action="{{ route('user.owner-profile.create', $model->id) }}">
                        <i class="ki-outline ki-plus-circle fs-5 me-2 text-success"></i>
                        Add Owner Profile
                    </a>
                </li>
            @else
                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="edit"
                       data-action="{{ route('user.owner-profile.edit', $model->id) }}">
                        <i class="ki-outline ki-briefcase fs-5 me-2 text-info"></i>
                        Edit Owner Profile
                    </a>
                </li>
            @endif

            <!-- Wallet & Transactions Section -->
            @if($hasWallet)
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                    <i class="ki-outline ki-wallet fs-6 me-1"></i>
                    Wallet & Transactions
                </li>

                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="deposit"
                       data-action="{{ route('admin.users.deposit-form', $model->id) }}">
                        <i class="ki-outline ki-arrow-down fs-5 me-2 text-success"></i>
                        Deposit Balance
                    </a>
                </li>

                <li>
                    <a class="dropdown-item has_action"
                       href="#"
                       data-type="withdraw"
                       data-action="{{ route('admin.users.withdraw-form', $model->id) }}">
                        <i class="ki-outline ki-arrow-up fs-5 me-2 text-warning"></i>
                        Withdraw Balance
                    </a>
                </li>

                <li><hr class="dropdown-divider my-2"></li>

                <li>
                    <a class="dropdown-item" href="{{ route('admin.transactions.by-user', $model->id) }}">
                        <i class="ki-outline ki-financial-schedule fs-5 me-2 text-primary"></i>
                        View Transactions
                        @php
                            $txCount = \App\Models\Transaction::where(function($q) use ($model, $hasInvestor, $hasOwner) {
                                $q->where('payable_type', 'App\\Models\\User')
                                  ->where('payable_id', $model->id);
                                if ($hasInvestor) {
                                    $q->orWhere(function($subQ) use ($model) {
                                        $subQ->where('payable_type', 'App\\Models\\InvestorProfile')
                                             ->where('payable_id', $model->investorProfile->id);
                                    });
                                }
                                if ($hasOwner) {
                                    $q->orWhere(function($subQ) use ($model) {
                                        $subQ->where('payable_type', 'App\\Models\\OwnerProfile')
                                             ->where('payable_id', $model->ownerProfile->id);
                                    });
                                }
                            })->count();
                        @endphp
                        @if($txCount > 0)
                            <span class="badge badge-light-success badge-sm ms-1">{{ $txCount }}</span>
                        @endif
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#walletBalanceModal{{ $model->id }}">
                        <i class="ki-outline ki-wallet fs-5 me-2 text-info"></i>
                        Wallet Balance
                        <span class="badge badge-light-info badge-sm ms-1">{{ number_format($investorBalance + $ownerBalance, 2) }} SAR</span>
                    </a>
                </li>
            @endif

            <!-- Investments Section -->
            @if($hasInvestor && $totalInvestments > 0)
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                    <i class="ki-outline ki-chart-simple fs-6 me-1"></i>
                    Investments
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('admin.investments.index', ['user_id' => $model->id]) }}">
                        <i class="ki-outline ki-chart-line-up fs-5 me-2 text-primary"></i>
                        View Investments
                        <span class="badge badge-light-primary badge-sm ms-1">{{ $totalInvestments }}</span>
                    </a>
                </li>
            @endif

            <!-- Verification Section -->
            {{-- @if(!$model->email_verified_at || !$model->phone_verified_at)
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                    <i class="ki-outline ki-shield-tick fs-6 me-1"></i>
                    Verification
                </li>

                @if(!$model->email_verified_at)
                    <li>
                        <a class="dropdown-item" href="#" onclick="verifyEmail({{ $model->id }})">
                            <i class="ki-outline ki-sms fs-5 me-2 text-warning"></i>
                            Verify Email
                        </a>
                    </li>
                @endif

                @if(!$model->phone_verified_at)
                    <li>
                        <a class="dropdown-item" href="#" onclick="verifyPhone({{ $model->id }})">
                            <i class="ki-outline ki-phone fs-5 me-2 text-warning"></i>
                            Verify Phone
                        </a>
                    </li>
                @endif
            @endif --}}

            {{-- <!-- Communication Section -->
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-notification fs-6 me-1"></i>
                Communication
            </li>

            <li>
                <a class="dropdown-item" href="#" onclick="sendNotification({{ $model->id }})">
                    <i class="ki-outline ki-notification-on fs-5 me-2 text-info"></i>
                    Send Notification
                </a>
            </li>

            <li>
                <a class="dropdown-item" href="mailto:{{ $model->email }}">
                    <i class="ki-outline ki-sms fs-5 me-2 text-primary"></i>
                    Send Email
                </a>
            </li>

            @if($model->phone)
                <li>
                    <a class="dropdown-item" href="tel:{{ $model->phone }}">
                        <i class="ki-outline ki-phone fs-5 me-2 text-success"></i>
                        Call User
                    </a>
                </li>
            @endif --}}

            <!-- Danger Zone -->
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-information-5 fs-6 me-1"></i>
                Danger Zone
            </li>

            <li>
                <a class="dropdown-item delete_btn text-danger"
                   href="#"
                   data-type="delete"
                   data-action="{{ route('user.destroy', $model->id) }}">
                    <i class="ki-outline ki-trash fs-5 me-2"></i>
                    Delete User
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Wallet Balance Modal -->
@if($hasWallet)
<div class="modal fade" id="walletBalanceModal{{ $model->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ki-outline ki-wallet fs-3 me-2"></i>
                    Wallet Balance - {{ $model->display_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column gap-4">
                    <!-- Total Balance -->
                    <div class="border border-success border-dashed rounded p-4 text-center bg-light-success">
                        <div class="text-muted fs-7 mb-2">Total Balance</div>
                        <div class="text-success fw-bold fs-2x">{{ number_format($investorBalance + $ownerBalance, 2) }} SAR</div>
                    </div>

                    <!-- Breakdown -->
                    @if($hasInvestor)
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <div>
                                <i class="ki-outline ki-chart-line-up fs-2x text-primary me-3"></i>
                                <span class="fw-semibold">Investor Wallet</span>
                            </div>
                            <span class="fw-bold fs-5">{{ number_format($investorBalance, 2) }} SAR</span>
                        </div>
                    @endif

                    @if($hasOwner)
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <div>
                                <i class="ki-outline ki-briefcase fs-2x text-info me-3"></i>
                                <span class="fw-semibold">Owner Wallet</span>
                            </div>
                            <span class="fw-bold fs-5">{{ number_format($ownerBalance, 2) }} SAR</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.transactions.by-user', $model->id) }}" class="btn btn-primary">
                    <i class="ki-outline ki-financial-schedule fs-5 me-1"></i>
                    View Transactions
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript Functions -->
<script>
function toggleUserStatus(userId, activate) {
    const action = activate ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ is_active: activate })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error toggling user status');
            console.error('Error:', error);
        });
    }
}

function verifyEmail(userId) {
    if (confirm('Manually verify this user\'s email?')) {
        fetch(`/admin/users/${userId}/verify-email`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email verified successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error verifying email');
            console.error('Error:', error);
        });
    }
}

function verifyPhone(userId) {
    if (confirm('Manually verify this user\'s phone?')) {
        fetch(`/admin/users/${userId}/verify-phone`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Phone verified successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error verifying phone');
            console.error('Error:', error);
        });
    }
}

function sendNotification(userId) {
    // Implement notification sending logic
    alert('Notification feature - To be implemented');
}
</script>
