<x-app-layout>
    {{-- Opportunity Header Card - Only show when filtering by specific opportunity --}}
    @if(isset($opportunity) && $opportunity)
    <div class="app-container container-xxl">
        <div class="card card-flush mb-7 shadow-sm">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title flex-column">
                    <h2 class="fw-bold text-gray-900 mb-1">
                        <i class="ki-duotone ki-chart-line-up fs-1 text-primary me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ $opportunity->name }}
                    </h2>
                    <span class="text-muted fs-6">Investment Details & Performance</span>
                </div>
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    <a href="#" data-type="show" data-action="{{ route('investment-opportunity.show', $opportunity->id) }}" class="has_action btn btn-sm btn-light-info">
                        <i class="ki-duotone ki-eye fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        View Opportunity
                    </a>
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
                {{-- Key Metrics Row --}}
                <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                    <x-metric-card-bg
                        value="${{ number_format($opportunity->target_amount, 0) }}"
                        label="Target Amount"
                        subtitle="Total Goal"
                        bgColor="#F1416C"
                    />

                    <x-metric-card-bg
                        value="${{ number_format($opportunity->share_price, 2) }}"
                        label="Share Price"
                        subtitle="Price Per Share"
                        bgColor="#50CD89"
                    />

                    <x-metric-card-bg
                        :value="number_format($opportunity->reserved_shares)"
                        label="Reserved Shares"
                        :subtitle="'of ' . number_format($opportunity->total_shares) . ' Total'"
                        bgColor="#FFC700"
                    />

                    <x-metric-card-bg
                        :value="number_format($opportunity->completion_rate, 1) . '%'"
                        label="Completion"
                        :subtitle="\App\InvestmentStatusEnum::label($opportunity->status)"
                        :bgColor="$opportunity->completion_rate >= 100 ? '#50CD89' : ($opportunity->completion_rate >= 75 ? '#FFC700' : '#7239EA')"
                        :showProgress="true"
                        :progress="$opportunity->completion_rate"
                    />
                </div>

                {{-- Additional Info Row --}}
                <div class="row g-5 g-xl-8">
                    <x-metric-card-simple
                        :value="number_format($opportunity->available_shares)"
                        label="Available Shares"
                        icon="ki-chart-pie-simple"
                        :iconPaths="2"
                        color="primary"
                    />

                    <x-metric-card-simple
                        :value="$opportunity->investments()->distinct('investor_id')->count()"
                        label="Total Investors"
                        icon="ki-people"
                        :iconPaths="5"
                        color="success"
                    />

                    <x-metric-card-simple
                        :value="$opportunity->investments()->count()"
                        label="Total Investments"
                        icon="ki-chart-simple"
                        :iconPaths="4"
                        color="warning"
                    />

                    <x-metric-card-simple
                        value="${{ number_format($opportunity->expected_net_profit ?? 0, 2) }}"
                        label="Expected Profit"
                        icon="ki-dollar"
                        :iconPaths="3"
                        color="info"
                        class="col-xl-3 col-lg-6 col-md-6 col-sm-6"
                    />
                </div>
            </div>
        </div>

        {{-- Investment Summary Cards - Only show when viewing specific opportunity --}}
        @php
            // Calculate metrics using Model relationships and scopes
            $totalInvested = $opportunity->investments()->sum('total_investment');

            // Authorize Type metrics - using Model relationships and scopes
            $authorizeCount = $opportunity->investmentsAuthorize()->count();
            $authorizeShares = $opportunity->investmentsAuthorize()->sum('shares');
            $authorizeDistributed = $opportunity->investmentsAuthorize()->statusDistributed()->count();
            $authorizeDistributedShares = $opportunity->investmentsAuthorize()->statusDistributed()->sum('shares');
            $authorizeNotDistributed = $opportunity->countInvestmentsNotDistributedAuthorize();
            $authorizeNotDistributedShares = $authorizeShares - $authorizeDistributedShares;

            // Myself Type metrics - using Model relationships and scopes
            $myselfCount = $opportunity->investmentsMyself()->count();
            $myselfShares = $opportunity->investmentsMyself()->sum('shares');
            $myselfArrived = $opportunity->investmentsMyself()->statusArrived()->count();
            $myselfArrivedShares = $opportunity->investmentsMyself()->statusArrived()->sum('shares');
            $myselfNotArrived = $opportunity->countInvestmentsNotArrivedMyself();
            $myselfNotArrivedShares = $myselfShares - $myselfArrivedShares;

            // Financial calculations
            $totalExpectedProfit = $opportunity->reserved_shares * ($opportunity->expected_net_profit ?? 0);
            $totalActualProfit = $opportunity->reserved_shares * ($opportunity->actual_net_profit_per_share ?? 0);
            $averageInvestment = $opportunity->investments()->count() > 0
                ? $totalInvested / $opportunity->investments()->count()
                : 0;
        @endphp

        <div class="row g-5 g-xl-8 mb-7">
            {{-- Authorize Investments --}}
            <x-card-header title="Authorize Investments" subtitle="Platform-managed investments">
                <x-list-item-icon
                    icon="ki-verify"
                    :iconPaths="2"
                    color="primary"
                    title="Total Authorize"
                    subtitle="All platform-managed"
                    :value="$authorizeCount"
                >
                    <x-slot:badge>
                        <span class="text-gray-800 fw-bold fs-4 d-block">{{ $authorizeCount }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ number_format($authorizeShares) }} shares</span>
                    </x-slot:badge>
                </x-list-item-icon>

                <x-list-item-icon
                    icon="ki-check-circle"
                    :iconPaths="2"
                    color="success"
                    title="Distributed"
                    subtitle="Profits distributed"
                    :value="$authorizeDistributed"
                >
                    <x-slot:badge>
                        <span class="text-gray-800 fw-bold fs-4 d-block">{{ $authorizeDistributed }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ number_format($authorizeDistributedShares) }} shares</span>
                    </x-slot:badge>
                </x-list-item-icon>

                <x-list-item-icon
                    icon="ki-time"
                    :iconPaths="3"
                    color="warning"
                    title="Not Distributed"
                    subtitle="Pending distribution"
                    :value="$authorizeNotDistributed"
                    :isLast="true"
                >
                    <x-slot:badge>
                        <span class="text-gray-800 fw-bold fs-4 d-block">{{ $authorizeNotDistributed }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ number_format($authorizeNotDistributedShares) }} shares</span>
                    </x-slot:badge>
                </x-list-item-icon>
            </x-card-header>

            {{-- Myself Investments --}}
            <x-card-header title="Myself Investments" subtitle="Self-managed investments">
                <x-list-item-icon
                    icon="ki-user"
                    :iconPaths="2"
                    color="primary"
                    title="Total Myself"
                    subtitle="All self-managed"
                    :value="$myselfCount"
                >
                    <x-slot:badge>
                        <span class="text-gray-800 fw-bold fs-4 d-block">{{ $myselfCount }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ number_format($myselfShares) }} shares</span>
                    </x-slot:badge>
                </x-list-item-icon>

                <x-list-item-icon
                    icon="ki-package"
                    :iconPaths="3"
                    color="success"
                    title="Arrived"
                    subtitle="Merchandise delivered"
                    :value="$myselfArrived"
                >
                    <x-slot:badge>
                        <span class="text-gray-800 fw-bold fs-4 d-block">{{ $myselfArrived }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ number_format($myselfArrivedShares) }} shares</span>
                    </x-slot:badge>
                </x-list-item-icon>

                <x-list-item-icon
                    icon="ki-delivery"
                    :iconPaths="5"
                    color="warning"
                    title="Not Arrived"
                    subtitle="Pending delivery"
                    :value="$myselfNotArrived"
                    :isLast="true"
                >
                    <x-slot:badge>
                        <span class="text-gray-800 fw-bold fs-4 d-block">{{ $myselfNotArrived }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ number_format($myselfNotArrivedShares) }} shares</span>
                    </x-slot:badge>
                </x-list-item-icon>
            </x-card-header>

            {{-- Financial Overview --}}
            <x-card-header title="Financial Overview" subtitle="Profit & returns">
                <x-list-item-value
                    icon="ki-dollar"
                    :iconPaths="3"
                    color="success"
                    title="Total Invested"
                    subtitle="Cumulative amount"
                >
                    <span class="text-gray-800 fw-bold fs-4 d-block">${{ number_format($totalInvested, 2) }}</span>
                    <span class="text-muted fw-semibold fs-7">
                        {{ $opportunity->target_amount > 0 ? number_format(($totalInvested / $opportunity->target_amount) * 100, 1) : 0 }}% of target
                    </span>
                </x-list-item-value>

                <x-list-item-value
                    icon="ki-chart-line-up"
                    :iconPaths="2"
                    color="primary"
                    title="Expected Profit"
                    subtitle="Projected returns"
                >
                    <span class="text-primary fw-bold fs-4 d-block">${{ number_format($totalExpectedProfit, 2) }}</span>
                    <span class="badge badge-light-primary">
                        {{ $totalInvested > 0 ? number_format(($totalExpectedProfit / $totalInvested) * 100, 1) : 0 }}% ROI
                    </span>
                </x-list-item-value>

                <x-list-item-value
                    icon="ki-finance-calculator"
                    :iconPaths="7"
                    :color="$opportunity->actual_net_profit_per_share ? 'success' : 'warning'"
                    title="Actual Profit"
                    subtitle="Realized returns"
                    :isLast="true"
                >
                    @if($opportunity->actual_net_profit_per_share)
                        <span class="text-success fw-bold fs-4 d-block">${{ number_format($totalActualProfit, 2) }}</span>
                        <span class="badge badge-light-success">
                            {{ $totalInvested > 0 ? number_format(($totalActualProfit / $totalInvested) * 100, 1) : 0 }}% ROI
                        </span>
                    @else
                        <span class="text-warning fw-bold fs-6 d-block">Not Set</span>
                        <span class="text-muted fw-semibold fs-7">Pending</span>
                    @endif
                </x-list-item-value>
            </x-card-header>
        </div>
    </div>
    @else
    {{-- Header for All Investments View --}}
    <div class="app-container container-xxl">
        <div class="card card-flush mb-7 shadow-sm">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h2 class="fw-bold text-gray-900 mb-0">
                        <i class="ki-duotone ki-chart-simple fs-1 text-primary me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        All Investments
                    </h2>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('investment-opportunity.index') }}" class="btn btn-sm btn-light-info">
                        <i class="ki-duotone ki-rocket fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        View Opportunities
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Investments DataTable --}}
    <x-dynamic-table
        table-id="investments_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="isset($opportunityId) && $opportunityId ? route('admin.investments.create', ['opportunity_id' => $opportunityId]) : route('admin.investments.create')"
    />
</x-app-layout>
