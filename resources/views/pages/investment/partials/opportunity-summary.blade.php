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
