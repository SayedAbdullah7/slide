{{-- Investor Header Card - Only show when filtering by specific investor --}}
<div class="app-container container-xxl">
    <div class="card card-flush mb-7 shadow-sm">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title flex-column">
                <h2 class="fw-bold text-gray-900 mb-1">
                    <i class="ki-duotone ki-user fs-1 text-primary me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ $investor->user->display_name ?? $investor->full_name ?? 'Investor #' . $investor->id }}
                </h2>
                <span class="text-muted fs-6">Investment Portfolio & Performance</span>
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                @if($investor->user)
                    <a href="{{ route('admin.users.show', $investor->user->id) }}" class="btn btn-sm btn-light-info">
                        <i class="ki-duotone ki-eye fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        View User Profile
                    </a>
                @endif
                <a href="{{ route('admin.investments.index') }}" class="btn btn-sm btn-light-primary">
                    <i class="ki-duotone ki-arrow-left fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    All Investments
                </a>
            </div>
        </div>
        <div class="card-body pt-0">
            @php
                $investorInvestments = $investor->investments()->with('opportunity');
                $totalInvestments = $investorInvestments->count();
                $totalInvested = $investorInvestments->sum('total_investment');
                $totalShares = $investorInvestments->sum('shares');
                $activeInvestments = $investorInvestments->where('status', 'active')->count();
                $completedInvestments = $investorInvestments->where('status', 'completed')->count();
                $totalExpectedProfit = $investorInvestments->get()->sum(function($inv) {
                    return $inv->shares * ($inv->expected_net_profit_per_share ?? 0);
                });
                $totalActualProfit = $investorInvestments->get()->sum(function($inv) {
                    return $inv->shares * ($inv->actual_net_profit_per_share ?? 0);
                });
                $totalDistributedProfit = $investorInvestments->sum('distributed_profit');
            @endphp

            {{-- Key Metrics Row --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                <x-metric-card-bg
                    value="${{ number_format($totalInvested, 2) }}"
                    label="Total Invested"
                    subtitle="All Investments"
                    bgColor="#F1416C"
                />

                <x-metric-card-bg
                    :value="number_format($totalShares)"
                    label="Total Shares"
                    subtitle="Across All Opportunities"
                    bgColor="#50CD89"
                />

                <x-metric-card-bg
                    :value="$totalInvestments"
                    label="Total Investments"
                    subtitle="Investment Count"
                    bgColor="#FFC700"
                />

                <x-metric-card-bg
                    :value="$activeInvestments"
                    label="Active Investments"
                    :subtitle="$completedInvestments . ' Completed'"
                    :bgColor="$activeInvestments > 0 ? '#7239EA' : '#50CD89'"
                />
            </div>

            {{-- Additional Info Row --}}
            <div class="row g-5 g-xl-8">
                <x-metric-card-simple
                    value="${{ number_format($totalExpectedProfit, 2) }}"
                    label="Expected Profit"
                    icon="ki-chart-line-up"
                    :iconPaths="2"
                    color="primary"
                />

                <x-metric-card-simple
                    value="${{ number_format($totalActualProfit, 2) }}"
                    label="Actual Profit"
                    icon="ki-dollar"
                    :iconPaths="3"
                    color="success"
                />

                <x-metric-card-simple
                    value="${{ number_format($totalDistributedProfit, 2) }}"
                    label="Distributed Profit"
                    icon="ki-check-circle"
                    :iconPaths="2"
                    color="info"
                />

                <x-metric-card-simple
                    :value="$investor->user->phone ?? 'N/A'"
                    label="Contact"
                    icon="ki-phone"
                    :iconPaths="2"
                    color="warning"
                    class="col-xl-3 col-lg-6 col-md-6 col-sm-6"
                />
            </div>
        </div>
    </div>
</div>
