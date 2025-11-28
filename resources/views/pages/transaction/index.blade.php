<x-app-layout>
    {{-- User Header Card - Only show when filtering by specific user --}}
    @if(isset($user) && $user)
    <div class="app-container container-xxl">
        <div class="card card-flush mb-7 shadow-sm">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title flex-column">
                    <h2 class="fw-bold text-gray-900 mb-1">
                        <i class="ki-outline ki-user fs-1 text-primary me-2"></i>
                        {{ $user->display_name }}
                    </h2>
                    <span class="text-muted fs-6">Wallet Transactions</span>
                </div>
                <div class="card-toolbar flex-row-fluid justify-content-end gap-2">
                    @if($hasInvestor || $hasOwner)
                        <a href="#"
                           class="btn btn-sm btn-success has_action"
                           data-type="deposit"
                           data-action="{{ route('admin.users.deposit-form', $user->id) }}">
                            <i class="ki-outline ki-arrow-down fs-2"></i>
                            Deposit
                        </a>
                        <a href="#"
                           class="btn btn-sm btn-warning has_action"
                           data-type="withdraw"
                           data-action="{{ route('admin.users.withdraw-form', $user->id) }}">
                            <i class="ki-outline ki-arrow-up fs-2"></i>
                            Withdraw
                        </a>
                    @endif
                    <a href="#" data-type="show" data-action="{{ route('admin.users.show', $user->id) }}" class="has_action btn btn-sm btn-light-info">
                        <i class="ki-outline ki-eye fs-2"></i>
                        View User
                    </a>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-light-primary">
                        <i class="ki-outline ki-arrow-left fs-2"></i>
                        All Transactions
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                {{-- User Transaction Summary --}}
                @php
                    // Variables $hasInvestor, $hasOwner, $investorBalance, $ownerBalance
                    // are already passed from controller
                    $totalBalance = $investorBalance + $ownerBalance;

                    // Build transaction query
                    $allTransactions = \App\Models\Transaction::query();

                    // Get transaction statistics for this user
                    $allTransactions->where(function($q) use ($user, $hasInvestor, $hasOwner) {
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
                    });

                    $totalTransactions = $allTransactions->count();
                    $totalDeposits = $allTransactions->clone()->where('type', 'deposit')->count();
                    $totalWithdrawals = $allTransactions->clone()->where('type', 'withdraw')->count();
                    $pendingCount = $allTransactions->clone()->where('confirmed', false)->count();

                    $totalDepositAmount = $allTransactions->clone()->where('type', 'deposit')->where('confirmed', true)->sum('amount') / 100;
                    $totalWithdrawalAmount = $allTransactions->clone()->where('type', 'withdraw')->where('confirmed', true)->sum('amount') / 100;
                @endphp

                <div class="row g-5 g-xl-8">
                    {{-- Total Balance --}}
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-outline ki-wallet fs-2x text-success me-3"></i>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Total Balance</span>
                                        <span class="text-gray-800 fw-bold fs-3">{{ number_format($totalBalance, 2) }}</span>
                                    </div>
                                </div>
                                <div class="fs-8 text-gray-600">
                                    @if($hasInvestor)
                                        <div>Investor: {{ number_format($investorBalance, 2) }} SAR</div>
                                    @endif
                                    @if($hasOwner)
                                        <div>Owner: {{ number_format($ownerBalance, 2) }} SAR</div>
                                    @endif
                                    @if(!$hasInvestor && !$hasOwner)
                                        <span class="text-muted">No wallets</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Deposits --}}
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card card-flush h-100 bg-light-success">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-outline ki-arrow-down fs-2x text-success me-3"></i>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Deposits</span>
                                        <span class="text-gray-800 fw-bold fs-3">{{ $totalDeposits }}</span>
                                    </div>
                                </div>
                                <div class="fs-7 text-gray-600">
                                    Total: {{ number_format($totalDepositAmount, 2) }} SAR
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Withdrawals --}}
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card card-flush h-100 bg-light-warning">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-outline ki-arrow-up fs-2x text-warning me-3"></i>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Withdrawals</span>
                                        <span class="text-gray-800 fw-bold fs-3">{{ $totalWithdrawals }}</span>
                                    </div>
                                </div>
                                <div class="fs-7 text-gray-600">
                                    Total: {{ number_format($totalWithdrawalAmount, 2) }} SAR
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Transactions --}}
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card card-flush h-100 {{ $pendingCount > 0 ? 'bg-light-danger' : '' }}">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-outline ki-time fs-2x text-{{ $pendingCount > 0 ? 'danger' : 'gray-500' }} me-3"></i>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-400 fw-semibold d-block fs-8 mb-1">Pending</span>
                                        <span class="text-gray-800 fw-bold fs-3">{{ $pendingCount }}</span>
                                    </div>
                                </div>
                                <div class="fs-7 text-gray-600">
                                    @if($pendingCount > 0)
                                        <span class="badge badge-light-danger">Requires attention</span>
                                    @else
                                        <span class="text-muted">All confirmed</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Header for All Transactions View --}}
    <div class="app-container container-xxl">
        <div class="card card-flush mb-7 shadow-sm">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h2 class="fw-bold text-gray-900 mb-0">
                        <i class="ki-outline ki-financial-schedule fs-1 text-primary me-2"></i>
                        All Transactions
                    </h2>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Transactions DataTable --}}
    <x-dynamic-table
        table-id="transactions_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
    />

</x-app-layout>
