@php
    $isEdit = isset($model);
    $hasInvestor = $user->investorProfile !== null;
    $hasOwner = $user->ownerProfile !== null;
    $investorBalance = $hasInvestor ? $user->investorProfile->getWalletBalance() : 0;
    $ownerBalance = $hasOwner ? $user->ownerProfile->getWalletBalance() : 0;
    $totalInvestments = $hasInvestor ? $user->investorProfile->investments()->count() : 0;
    $activeInvestments = $hasInvestor ? $user->investorProfile->investments()->where('status', 'active')->count() : 0;

    // Additional metrics
    $totalInvested = $hasInvestor ? $user->investorProfile->investments()->sum('total_investment') : 0;
    $completedInvestments = $hasInvestor ? $user->investorProfile->investments()->where('status', 'completed')->count() : 0;
    $totalProfitEarned = $hasInvestor ? $user->investorProfile->investments()->whereNotNull('distributed_profit')->sum('distributed_profit') : 0;
    $recentInvestments = $hasInvestor ? $user->investorProfile->investments()->with('opportunity')->latest()->take(5)->get() : collect();

    // Verification status
    $isEmailVerified = $user->email_verified_at !== null;
    $isPhoneVerified = $user->phone_verified_at !== null;

    // Account age
    $accountAge = $user->created_at->diffForHumans(null, true);
    $daysSinceRegistration = $user->created_at->diffInDays(now());

    // Notifications and tokens
    $hasFcmTokens = $user->fcmTokens()->exists();
    $fcmTokenCount = $user->fcmTokens()->count();

    // Deletion requests
    $hasPendingDeletion = $user->hasPendingDeleteRequest();

    // Survey completion
    $surveyCompletionRate = $user->surveyAnswers->count();

    // Transaction count
    $totalTransactionCount = 0;
    if ($hasInvestor || $hasOwner) {
        $totalTransactionCount = \App\Models\Transaction::where(function($q) use ($user, $hasInvestor, $hasOwner) {
            $q->where('payable_type', 'App\\Models\\User')
              ->where('payable_id', $user->id);

            if ($hasInvestor) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('payable_type', 'App\\Models\\InvestorProfile')
                         ->where('payable_id', $user->investorProfile->id);
                });
            }

            if ($hasOwner) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('payable_type', 'App\\Models\\OwnerProfile')
                         ->where('payable_id', $user->ownerProfile->id);
                });
            }
        })->count();
    }
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Critical Alerts -->
    @if($hasPendingDeletion)
        <div class="alert alert-danger d-flex align-items-center mb-7">
            <i class="ki-outline ki-information-5 fs-2x text-danger me-4"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Pending Account Deletion Request</h4>
                <span>This user has requested account deletion. Review and process the request.</span>
        </div>
        </div>
    @endif

    @if(!$user->is_active)
        <div class="alert alert-warning d-flex align-items-center mb-7">
            <i class="ki-outline ki-shield-cross fs-2x text-warning me-4"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-warning">Account Inactive</h4>
                <span>This user account is currently inactive and cannot access the platform.</span>
    </div>
        </div>
    @endif

    <!-- User Profile Header -->
    <div class="card mb-7">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row align-items-center">
                <!-- Avatar -->
                <div class="symbol symbol-100px symbol-lg-150px symbol-fixed position-relative me-5 mb-5 mb-sm-0">
                    @if($user->hasMedia('avatar'))
                        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="{{ $user->display_name }}" class="rounded-circle"/>
                    @else
                        <div class="symbol-label fs-2 fw-bold text-primary bg-light-primary rounded-circle">
                            {{ strtoupper(substr($user->display_name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                    <!-- Status Indicator -->
                    <div class="position-absolute bottom-0 end-0 bg-{{ $user->is_active ? 'success' : 'danger' }} w-20px h-20px rounded-circle border border-4 border-white"></div>
        </div>

                <!-- User Info -->
                <div class="flex-grow-1">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-900 text-hover-primary fs-2 fw-bold me-3">{{ $user->display_name }}</span>
                            @if($isEmailVerified)
                                <span class="badge badge-light-success" data-bs-toggle="tooltip" title="Email Verified">
                                    <i class="ki-outline ki-verify fs-5"></i>
                                </span>
                            @endif
                            @if($isPhoneVerified)
                                <span class="badge badge-light-info ms-2" data-bs-toggle="tooltip" title="Phone Verified">
                                    <i class="ki-outline ki-phone fs-5"></i>
                                </span>
                            @endif
    </div>

                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                            <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                <i class="ki-outline ki-sms fs-4 me-1"></i>
                                {{ $user->email }}
                            </span>
                            @if($user->phone)
                                <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                    <i class="ki-outline ki-phone fs-4 me-1"></i>
                                    {{ $user->phone }}
                                </span>
                            @endif
                            <span class="d-flex align-items-center text-gray-500 mb-2">
                                <i class="ki-outline ki-calendar fs-4 me-1"></i>
                                Member for {{ $accountAge }}
                            </span>
        </div>

                        <!-- Badges -->
                        <div class="d-flex flex-wrap gap-2">
                    @if($user->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif

                            @if($user->is_registered)
                                <span class="badge badge-primary">Registered</span>
                            @endif

                            @if($hasInvestor)
                                <span class="badge badge-light-primary">
                                    <i class="ki-outline ki-chart-line fs-7 me-1"></i>
                                    Investor
                                </span>
                            @endif

                            @if($hasOwner)
                                <span class="badge badge-light-info">
                                    <i class="ki-outline ki-briefcase fs-7 me-1"></i>
                                    Owner
                                </span>
                            @endif

                            @if($hasFcmTokens)
                                <span class="badge badge-light-success" data-bs-toggle="tooltip" title="{{ $fcmTokenCount }} device(s) registered">
                                    <i class="ki-outline ki-notification fs-7 me-1"></i>
                                    Push Enabled
                                </span>
                            @endif

                            @if($surveyCompletionRate > 0)
                                <span class="badge badge-light-warning" data-bs-toggle="tooltip" title="Survey completed">
                                    <i class="ki-outline ki-questionnaire-tablet fs-7 me-1"></i>
                                    Survey: {{ $surveyCompletionRate }}
                                </span>
                            @endif

                            @if($totalTransactionCount > 0)
                                <a href="{{ route('admin.transactions.by-user', $user->id) }}"
                                   class="badge badge-light-success text-hover-primary"
                                   data-bs-toggle="tooltip"
                                   title="View all transactions">
                                    <i class="ki-outline ki-financial-schedule fs-7 me-1"></i>
                                    Transactions: {{ $totalTransactionCount }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Summary Cards -->
    <div class="row g-5 g-xl-8 mb-8">
        <!-- Verification Status Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex flex-column justify-content-between p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-shield-tick fs-2x text-{{ $isEmailVerified && $isPhoneVerified ? 'success' : 'warning' }} me-3"></i>
                        <div class="flex-grow-1">
                            <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Verification</span>
                            <span class="text-gray-800 fw-bold fs-4">
                                @if($isEmailVerified && $isPhoneVerified)
                                    Fully Verified
                                @elseif($isEmailVerified || $isPhoneVerified)
                                    Partially Verified
                                @else
                                    Not Verified
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-{{ $isEmailVerified ? 'success' : 'light-danger' }} fs-8">
                            <i class="ki-outline ki-{{ $isEmailVerified ? 'check' : 'cross' }} fs-8"></i> Email
                        </span>
                        <span class="badge badge-{{ $isPhoneVerified ? 'success' : 'light-danger' }} fs-8">
                            <i class="ki-outline ki-{{ $isPhoneVerified ? 'check' : 'cross' }} fs-8"></i> Phone
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wallet Balance Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100 {{ $investorBalance + $ownerBalance > 0 ? 'bg-light-success' : '' }}">
                <div class="card-body d-flex flex-column justify-content-between p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-wallet fs-2x text-success me-3"></i>
                        <div class="flex-grow-1">
                            <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Wallet Balance</span>
                            <span class="text-gray-800 fw-bold fs-4">{{ number_format($investorBalance + $ownerBalance, 2) }}</span>
                        </div>
                    </div>
                    @if($hasInvestor || $hasOwner)
                        <div class="fs-8 text-gray-600">
                            @if($hasInvestor)
                                <div>Investor: {{ number_format($investorBalance, 2) }} SAR</div>
                            @endif
                            @if($hasOwner)
                                <div>Owner: {{ number_format($ownerBalance, 2) }} SAR</div>
                            @endif
                        </div>
                    @else
                        <span class="text-muted fs-8">No wallet</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Investments Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100 {{ $totalInvestments > 0 ? 'bg-light-primary' : '' }}">
                <div class="card-body d-flex flex-column justify-content-between p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-chart-simple fs-2x text-primary me-3"></i>
                        <div class="flex-grow-1">
                            <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Investments</span>
                            <span class="text-gray-800 fw-bold fs-4">{{ $totalInvestments }}</span>
                        </div>
                    </div>
                    @if($totalInvestments > 0)
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge badge-light-success fs-8">{{ $activeInvestments }} Active</span>
                            <span class="badge badge-light-primary fs-8">{{ $completedInvestments }} Completed</span>
                        </div>
                    @else
                        <span class="text-muted fs-8">No investments yet</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Profit Earned Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100 {{ $totalProfitEarned > 0 ? 'bg-light-warning' : '' }}">
                <div class="card-body d-flex flex-column justify-content-between p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-financial-schedule fs-2x text-warning me-3"></i>
                        <div class="flex-grow-1">
                            <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Total Profit</span>
                            <span class="text-gray-800 fw-bold fs-4">{{ number_format($totalProfitEarned, 2) }}</span>
                        </div>
                    </div>
                    @if($totalInvested > 0)
                        <div class="fs-8 text-gray-600">
                            Invested: {{ number_format($totalInvested, 2) }} SAR
                        </div>
                    @else
                        <span class="text-muted fs-8">No returns yet</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Information Section -->
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ki-outline ki-user fs-3 me-2"></i>
                Personal Information
            </h3>
        </div>
        <div class="card-body">
            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-7">
                        <i class="ki-outline ki-profile-user fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Full Name</label>
                            <div class="fw-bold fs-5 text-gray-800">{{ $user->investorProfile?->full_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-7">
                        <i class="ki-outline ki-sms fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Email</label>
                            <div class="fw-bold fs-6 text-gray-800">{{ $user->email }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-7">
                        <i class="ki-outline ki-phone fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Phone</label>
                            <div class="fw-bold fs-6 text-gray-800">{{ $user->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-7">
                        <i class="ki-outline ki-badge fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">National ID</label>
                            <div class="fw-bold fs-6 text-gray-800">{{ $user->investorProfile?->national_id ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-7">
                        <i class="ki-outline ki-calendar fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Birth Date</label>
                            <div class="fw-bold fs-6 text-gray-800">{{ $user->investorProfile?->birth_date?->format('Y-m-d') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
        <div class="col-md-6">
                    <div class="d-flex align-items-center mb-7">
                        <i class="ki-outline ki-shield-tick fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Registration Status</label>
                            <div class="mt-1">
                                @if($user->is_registered)
                                    <span class="badge badge-light-success">Registered</span>
                                @else
                                    <span class="badge badge-light-warning">Not Registered</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-5">
                        <i class="ki-outline ki-time fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Member Since</label>
                            <div class="fw-bold fs-6 text-gray-800">{{ $user->created_at->format('M d, Y') }}</div>
                            <div class="text-muted fs-7">{{ $user->created_at->diffForHumans() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
                    <div class="d-flex align-items-center mb-5">
                        <i class="ki-outline ki-notepad-edit fs-2 text-gray-600 me-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold fs-7 text-gray-600 mb-1">Last Updated</label>
                            <div class="fw-bold fs-6 text-gray-800">{{ $user->updated_at->format('M d, Y') }}</div>
                            <div class="text-muted fs-7">{{ $user->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Investor Profile Section -->
    @if($hasInvestor)
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-chart-line-up fs-3 text-primary me-2"></i>
                    Investor Profile
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary">Active</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-5 bg-light-success rounded mb-5">
                            <i class="ki-outline ki-wallet fs-2x text-success me-4"></i>
                            <div class="flex-grow-1">
                                <label class="fw-semibold fs-7 text-gray-600 mb-1">Wallet Balance</label>
                                <div class="fw-bold fs-3 text-success">{{ number_format($investorBalance, 2) }} SAR</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-5 bg-light-primary rounded mb-5">
                            <i class="ki-outline ki-chart-simple fs-2x text-primary me-4"></i>
                            <div class="flex-grow-1">
                                <label class="fw-semibold fs-7 text-gray-600 mb-1">Total Investments</label>
                                <div class="fw-bold fs-3 text-primary">{{ $totalInvestments }}</div>
                                <div class="text-muted fs-7">{{ $activeInvestments }} Active</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($totalInvestments > 0)
                    <div class="separator my-5"></div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="text-gray-600 fw-semibold fs-7 mb-1">Total Invested</div>
                                <div class="fw-bold fs-4 text-gray-800">{{ number_format($totalInvested, 2) }} SAR</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="text-gray-600 fw-semibold fs-7 mb-1">Completed</div>
                                <div class="fw-bold fs-4 text-gray-800">{{ $completedInvestments }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="text-gray-600 fw-semibold fs-7 mb-1">Active</div>
                                <div class="fw-bold fs-4 text-gray-800">{{ $activeInvestments }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Investments -->
                    @if($recentInvestments->count() > 0)
                        <div class="separator my-5"></div>
                        <div class="mb-3">
                            <h5 class="text-gray-700 fw-bold mb-3">Recent Investments</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light fs-7">
                                        <th class="ps-4">Opportunity</th>
                                        <th class="text-center">Shares</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-4">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentInvestments as $investment)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-briefcase fs-3 text-primary me-2"></i>
                                                    <span class="text-gray-800 fw-semibold text-hover-primary fs-7">
                                                        {{ $investment->opportunity->name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light-primary">{{ $investment->shares }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-gray-800 fw-bold fs-7">{{ number_format($investment->total_investment, 2) }} SAR</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'active' => 'success',
                                                        'completed' => 'primary',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $statusColors[$investment->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-light-{{ $color }}">{{ ucfirst($investment->status) }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="text-gray-600 fs-8">{{ $investment->investment_date->format('M d, Y') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($totalInvestments > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('admin.investments.index', ['opportunity_id' => $user->id]) }}" class="btn btn-sm btn-light-primary">
                                    View All {{ $totalInvestments }} Investments
                                </a>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    @endif

    <!-- Owner Profile Section -->
    @if($hasOwner)
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-briefcase fs-3 text-info me-2"></i>
                    Owner Profile
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-info">Active</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-5">
                            <i class="ki-outline ki-shop fs-2 text-gray-600 me-3"></i>
                            <div class="flex-grow-1">
                                <label class="fw-semibold fs-7 text-gray-600 mb-1">Business Name</label>
                                <div class="fw-bold fs-6 text-gray-800">{{ $user->ownerProfile->business_name ?? 'N/A' }}</div>
                            </div>
                        </div>
            </div>
            <div class="col-md-6">
                        <div class="d-flex align-items-center mb-5">
                            <i class="ki-outline ki-barcode fs-2 text-gray-600 me-3"></i>
                            <div class="flex-grow-1">
                                <label class="fw-semibold fs-7 text-gray-600 mb-1">Tax Number</label>
                                <div class="fw-bold fs-6 text-gray-800">{{ $user->ownerProfile->tax_number ?? 'N/A' }}</div>
                            </div>
                        </div>
            </div>
        </div>

                <div class="row">
            <div class="col-12">
                        <div class="d-flex align-items-center p-5 bg-light-success rounded">
                            <i class="ki-outline ki-wallet fs-2x text-success me-4"></i>
                            <div class="flex-grow-1">
                                <label class="fw-semibold fs-7 text-gray-600 mb-1">Wallet Balance</label>
                                <div class="fw-bold fs-3 text-success">{{ number_format($ownerBalance, 2) }} SAR</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Transactions Section -->
    @if($hasInvestor || $hasOwner)
        @php
            // Get recent transactions for this user
            $recentTransactions = \App\Models\Transaction::where(function($q) use ($user, $hasInvestor, $hasOwner) {
                $q->where('payable_type', 'App\\Models\\User')
                  ->where('payable_id', $user->id);

                if ($hasInvestor) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('payable_type', 'App\\Models\\InvestorProfile')
                             ->where('payable_id', $user->investorProfile->id);
                    });
                }

                if ($hasOwner) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('payable_type', 'App\\Models\\OwnerProfile')
                             ->where('payable_id', $user->ownerProfile->id);
                    });
                }
            })->orderBy('created_at', 'desc')->take(10)->get();

            // Use already calculated $totalTransactionCount from top of page
        @endphp

        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-financial-schedule fs-3 me-2"></i>
                    Recent Transactions
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary">{{ $totalTransactionCount }} Total</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3 mb-0">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-5 min-w-200px">Type</th>
                                    <th class="min-w-150px text-end">Amount</th>
                                    <th class="min-w-100px text-center">Status</th>
                                    <th class="min-w-150px text-end pe-5">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td class="ps-5">
                                            @php
                                                $txType = $transaction->type;
                                                $txColor = $txType === 'deposit' ? 'success' : 'warning';
                                                $txIcon = $txType === 'deposit' ? 'ki-arrow-down' : 'ki-arrow-up';
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <i class="ki-outline {{ $txIcon }} fs-3 text-{{ $txColor }} me-2"></i>
                                                <span class="text-gray-800 fw-semibold">{{ ucfirst($txType) }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $amount = $transaction->amount / 100;
                                                $amountColor = $txType === 'deposit' ? 'success' : 'danger';
                                                $sign = $txType === 'deposit' ? '+' : '-';
                                            @endphp
                                            <span class="text-{{ $amountColor }} fw-bold">{{ $sign }} {{ number_format($amount, 2) }} SAR</span>
                                        </td>
                                        <td class="text-center">
                                            @if($transaction->confirmed)
                                                <span class="badge badge-light-success">
                                                    <i class="ki-outline ki-check-circle fs-7"></i>
                                                    Confirmed
                                                </span>
                                            @else
                                                <span class="badge badge-light-warning">
                                                    <i class="ki-outline ki-time fs-7"></i>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-5">
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="text-gray-700 fw-semibold fs-7">{{ $transaction->created_at->format('M d, Y') }}</span>
                                                <span class="text-muted fs-8">{{ $transaction->created_at->format('h:i A') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($totalTransactionCount > 10)
                        <div class="card-footer text-center py-4">
                            <a href="{{ route('admin.transactions.by-user', $user->id) }}" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-financial-schedule fs-5 me-1"></i>
                                View All {{ $totalTransactionCount }} Transactions
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-10">
                        <i class="ki-outline ki-information-5 fs-3x mb-5 d-block"></i>
                        <div class="fw-semibold">No transactions yet</div>
                        <div class="fs-7">This user has not made any wallet transactions</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Survey Answers Section -->
    @if($user->surveyAnswers->count() > 0)
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-outline ki-questionnaire-tablet fs-3 me-2"></i>
                    Survey Answers
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary">{{ $user->surveyAnswers->count() }} Answers</span>
                </div>
            </div>
            <div class="card-body p-0">
        <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3 mb-0">
                <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-5 min-w-300px">Question</th>
                                <th class="min-w-200px">Answer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->surveyAnswers as $answer)
                        <tr>
                                    <td class="ps-5">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-outline ki-questionnaire-tablet fs-3 text-primary me-3"></i>
                                            <span class="text-gray-800 fw-semibold">{{ $answer->question->question }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge badge-light-success">
                                @if($answer->survey_option_id)
                                    {{ $answer->option->option_text }}
                                @else
                                    {{ $answer->answer_text }}
                                @endif
                                        </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                </div>
            </div>
        </div>
    @endif

</div>

<!-- Quick Actions Section -->
<div class="card mb-7">
    <div class="card-header">
        <h3 class="card-title">
            <i class="ki-outline ki-rocket fs-3 me-2"></i>
            Quick Actions
        </h3>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- User Management -->
            <div class="col-md-6">
                <div class="border border-gray-300 border-dashed rounded p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-user-edit fs-2x text-primary me-3"></i>
                        <h5 class="mb-0">User Management</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
    <a href="#"
                           class="btn btn-sm btn-primary has_action"
       data-type="edit"
       data-action="{{ route('user.edit', $user->id) }}">
                            <i class="ki-outline ki-pencil fs-5"></i>
                            Edit User
                        </a>
                        @if($user->is_active)
                            <button class="btn btn-sm btn-light-danger" data-bs-toggle="tooltip" title="Deactivate account">
                                <i class="ki-outline ki-shield-cross fs-5"></i>
                                Deactivate
                            </button>
                        @else
                            <button class="btn btn-sm btn-light-success" data-bs-toggle="tooltip" title="Activate account">
                                <i class="ki-outline ki-shield-tick fs-5"></i>
                                Activate
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Management -->
            <div class="col-md-6">
                <div class="border border-gray-300 border-dashed rounded p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-profile-user fs-2x text-success me-3"></i>
                        <h5 class="mb-0">Profile Management</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
    @if(!$user->hasInvestor())
        <a href="#"
                               class="btn btn-sm btn-success has_action"
           data-type="create"
           data-action="{{ route('user.investor-profile.create', $user->id) }}">
                                <i class="ki-outline ki-plus fs-5"></i>
                                Add Investor
        </a>
    @else
        <a href="#"
                               class="btn btn-sm btn-light-success has_action"
           data-type="edit"
           data-action="{{ route('user.investor-profile.edit', $user->id) }}">
                                <i class="ki-outline ki-pencil fs-5"></i>
                                Edit Investor
        </a>
    @endif

    @if(!$user->hasOwner())
        <a href="#"
                               class="btn btn-sm btn-info has_action"
           data-type="create"
           data-action="{{ route('user.owner-profile.create', $user->id) }}">
                                <i class="ki-outline ki-plus fs-5"></i>
                                Add Owner
        </a>
    @else
        <a href="#"
                               class="btn btn-sm btn-light-info has_action"
           data-type="edit"
           data-action="{{ route('user.owner-profile.edit', $user->id) }}">
                                <i class="ki-outline ki-pencil fs-5"></i>
                                Edit Owner
        </a>
    @endif
</div>
                </div>
            </div>

            <!-- Verification -->
            <div class="col-md-6">
                <div class="border border-gray-300 border-dashed rounded p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-shield-tick fs-2x text-warning me-3"></i>
                        <h5 class="mb-0">Verification</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if(!$isEmailVerified)
                            <button class="btn btn-sm btn-light-warning" data-bs-toggle="tooltip" title="Manually verify email">
                                <i class="ki-outline ki-sms fs-5"></i>
                                Verify Email
                            </button>
                        @endif
                        @if(!$isPhoneVerified)
                            <button class="btn btn-sm btn-light-warning" data-bs-toggle="tooltip" title="Manually verify phone">
                                <i class="ki-outline ki-phone fs-5"></i>
                                Verify Phone
                            </button>
                        @endif
                        @if($isEmailVerified && $isPhoneVerified)
                            <span class="badge badge-light-success fs-6 py-2 px-3">
                                <i class="ki-outline ki-verify fs-5 me-1"></i>
                                Fully Verified
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Actions -->
            <div class="col-md-6">
                <div class="border border-gray-300 border-dashed rounded p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-outline ki-setting-2 fs-2x text-info me-3"></i>
                        <h5 class="mb-0">Additional Actions</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($hasInvestor && $totalInvestments > 0)
                            <a href="{{ route('admin.investments.index', ['opportunity_id' => $user->id]) }}"
                               class="btn btn-sm btn-light-primary"
                               data-bs-toggle="tooltip" title="View all investments">
                                <i class="ki-outline ki-chart-simple fs-5"></i>
                                View Investments
                            </a>
                        @endif
                        @if($hasInvestor || $hasOwner)
                            <a href="{{ route('admin.transactions.by-user', $user->id) }}"
                               class="btn btn-sm btn-light-success"
                               data-bs-toggle="tooltip" title="View wallet transactions">
                                <i class="ki-outline ki-wallet fs-5"></i>
                                View Transactions
                            </a>
                        @endif
                        <button class="btn btn-sm btn-light-info" data-bs-toggle="tooltip" title="Send notification to user">
                            <i class="ki-outline ki-notification fs-5"></i>
                            Send Notification
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Close Button -->
<div class="text-center">
    <button type="button" class="btn btn-lg btn-light-primary close" data-bs-dismiss="modal">
        <i class="ki-outline ki-cross fs-3"></i>
        Close
    </button>
</div>

<!-- Initialize Tooltips -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
