@php
    $isEdit = isset($model);
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    {{-- Header Cards - Quick Stats --}}
    <div class="row g-5 g-xl-8 mb-7 mb-xl-8">
        <x-metric-card-bg
            :value="number_format($investment->shares)"
            label="Shares"
            subtitle="Total shares purchased"
            bgColor="#7239EA"
            class="col-md-4"
        />

        <x-metric-card-bg
            value="${{ number_format($investment->total_investment, 2) }}"
            label="Investment"
            subtitle="Total amount invested"
            bgColor="#50CD89"
            class="col-md-4"
        />

        <x-metric-card-bg
            value="${{ number_format($investment->getTotal(), 2) }}"
            label="Total Payment"
            subtitle="Including fees"
            bgColor="#F1416C"
            class="col-md-4"
        />
    </div>

    {{-- Basic Information --}}
    <x-card-header title="Basic Information" subtitle="Investment overview" class="col-md-12 mb-7">
        <x-list-item-value
            icon="ki-chart-line-up"
            :iconPaths="2"
            color="primary"
            title="Opportunity"
            subtitle="Investment opportunity"
        >
            <span class="text-gray-800 fw-bold fs-5">{{ $investment->opportunity->name ?? 'N/A' }}</span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-profile-user"
            :iconPaths="4"
            color="success"
            title="Investor"
            subtitle="Account holder"
        >
            <span class="text-gray-800 fw-bold fs-5">{{ $investment->investorProfile->user->display_name ?? 'N/A' }}</span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-{{ $investment->investment_type === 'myself' ? 'user' : 'verify' }}"
            :iconPaths="2"
            :color="$investment->investment_type === 'myself' ? 'primary' : 'info'"
            title="Investment Type"
            subtitle="Management type"
        >
            <span class="badge badge-light-{{ $investment->investment_type === 'myself' ? 'primary' : 'info' }} fs-5 px-4 py-2">
                        {{ $investment->investment_type_arabic }}
                    </span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-status"
            :iconPaths="4"
            :color="$investment->status === 'active' ? 'success' : ($investment->status === 'completed' ? 'info' : ($investment->status === 'cancelled' ? 'danger' : 'warning'))"
            title="Status"
            subtitle="Current status"
            :isLast="true"
        >
            <span class="badge badge-{{ $investment->status === 'active' ? 'success' : ($investment->status === 'completed' ? 'info' : ($investment->status === 'cancelled' ? 'danger' : 'warning')) }} fs-5 px-4 py-2">
                        {{ $investment->status_arabic }}
                    </span>
        </x-list-item-value>
    </x-card-header>

    {{-- Investment Details --}}
    <x-card-header
        title="Investment Details"
        :subtitle="$investment->isMyselfType() ? 'Shares, pricing & shipping' : 'Shares & pricing'"
        class="col-md-12 mb-7"
    >
        <x-list-item-value
            icon="ki-chart-pie-simple"
            :iconPaths="2"
            color="primary"
            title="Shares"
            subtitle="Number of shares"
        >
            <span class="text-gray-800 fw-bold fs-4">{{ number_format($investment->shares) }}</span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-dollar"
            :iconPaths="3"
            color="success"
            title="Share Price"
            subtitle="Price per share"
        >
            <span class="text-gray-800 fw-bold fs-4">${{ number_format($investment->share_price, 2) }}</span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-wallet"
            :iconPaths="4"
            color="info"
            title="Total Investment"
            subtitle="Shares × Price"
        >
            <span class="text-gray-800 fw-bold fs-4">${{ number_format($investment->total_investment, 2) }}</span>
        </x-list-item-value>

        {{-- Shipping Fee (only for Myself type) --}}
        @if($investment->isMyselfType() && $investment->shipping_fee_per_share)
            <x-list-item-value
                icon="ki-delivery"
                :iconPaths="5"
                color="warning"
                title="Shipping Fee"
                subtitle="Total shipping cost"
            >
                <span class="text-gray-800 fw-bold fs-4">${{ number_format($investment->getTotalShippingAndServiceFee(), 2) }}</span>
                <span class="text-muted fw-semibold fs-7 d-block">${{ number_format($investment->shipping_fee_per_share, 2) }} per share</span>
            </x-list-item-value>
                    @endif

        <x-list-item-value
            icon="ki-calendar"
            :iconPaths="3"
            color="dark"
            title="Investment Date"
            subtitle="Transaction date"
            :isLast="true"
        >
            <span class="text-gray-800 fw-bold fs-6">{{ $investment->investment_date ? $investment->investment_date->format('Y-m-d') : 'N/A' }}</span>
            <span class="text-muted fw-semibold fs-7 d-block">{{ $investment->investment_date ? $investment->investment_date->format('h:i A') : '' }}</span>
        </x-list-item-value>
    </x-card-header>

    {{-- Expected Returns --}}
    <x-card-header title="Expected Returns" subtitle="Projected profits" class="col-md-12 mb-7">
        <x-list-item-value
            icon="ki-chart-line-up"
            :iconPaths="2"
            color="primary"
            title="Expected Profit Per Share"
            subtitle="Gross profit per share"
        >
            <span class="text-primary fw-bold fs-4">${{ number_format($investment->expected_profit_per_share ?? 0, 2) }}</span>
            <span class="badge badge-light-primary">{{ number_format($investment->getExpectedProfitPercentage(), 1) }}%</span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-chart-simple"
            :iconPaths="4"
            color="success"
            title="Expected Net Profit Per Share"
            subtitle="Net profit per share"
        >
            <span class="text-success fw-bold fs-4">${{ number_format($investment->expected_net_profit_per_share ?? 0, 2) }}</span>
            <span class="badge badge-light-success">{{ number_format($investment->getExpectedNetProfitPercentage(), 1) }}%</span>
        </x-list-item-value>

        <x-list-item-value
            icon="ki-finance-calculator"
            :iconPaths="7"
            color="info"
            title="Total Expected Net Profit"
            subtitle="Total net returns"
            :isLast="true"
        >
            <span class="text-info fw-bold fs-3">${{ number_format($investment->getTotalExpectedNetProfit(), 2) }}</span>
        </x-list-item-value>
    </x-card-header>

    {{-- Actual Returns (if available) --}}
    @if($investment->hasActualReturns())
        <x-card-header title="Actual Returns" subtitle="Realized profits" class="col-md-12 mb-7">
            <x-list-item-value
                icon="ki-chart-line-up"
                :iconPaths="2"
                color="primary"
                title="Actual Profit Per Share"
                subtitle="Gross profit per share"
            >
                <span class="text-primary fw-bold fs-4">${{ number_format($investment->actual_profit_per_share, 2) }}</span>
                <span class="badge badge-light-primary">{{ number_format($investment->getActualProfitPercentage(), 1) }}%</span>
            </x-list-item-value>

            <x-list-item-value
                icon="ki-chart-simple"
                :iconPaths="4"
                color="success"
                title="Actual Net Profit Per Share"
                subtitle="Net profit per share"
            >
                <span class="text-success fw-bold fs-4">${{ number_format($investment->actual_net_profit_per_share, 2) }}</span>
                <span class="badge badge-light-success">{{ number_format($investment->getActualNetProfitPercentage(), 1) }}%</span>
            </x-list-item-value>

            <x-list-item-value
                icon="ki-finance-calculator"
                :iconPaths="7"
                color="info"
                title="Total Actual Net Profit"
                subtitle="Total realized returns"
            >
                <span class="text-info fw-bold fs-3">${{ number_format($investment->getTotalActualNetProfit(), 2) }}</span>
            </x-list-item-value>

            <x-list-item-value
                icon="ki-calendar-tick"
                :iconPaths="5"
                color="dark"
                title="Returns Recorded At"
                subtitle="Recording date"
                :isLast="true"
            >
                <span class="text-gray-800 fw-bold fs-6">{{ $investment->actual_returns_recorded_at ? $investment->actual_returns_recorded_at->format('Y-m-d h:i A') : 'N/A' }}</span>
            </x-list-item-value>
        </x-card-header>
    @endif

    {{-- Tracking Status --}}
    <div class="row g-5 mb-7">
        {{-- Merchandise Tracking (for Myself type) --}}
        @if($investment->isMyselfType())
            <x-card-header title="Merchandise Tracking" subtitle="Physical delivery status" class="col-md-6">
                <x-list-item-value
                    icon="ki-package"
                    :iconPaths="3"
                    :color="$investment->isMerchandiseArrived() ? 'success' : 'warning'"
                    title="Merchandise Status"
                    subtitle="Current delivery status"
                >
                    <span class="badge badge-{{ $investment->isMerchandiseArrived() ? 'success' : 'warning' }} fs-5 px-4 py-2">
                        {{ $investment->isMerchandiseArrived() ? 'Arrived' : 'Pending' }}
                    </span>
                </x-list-item-value>

                <x-list-item-value
                    icon="ki-calendar"
                    :iconPaths="3"
                    color="info"
                    title="Expected Delivery"
                    subtitle="Estimated arrival date"
                >
                    <span class="text-gray-800 fw-bold fs-6">{{ $investment->expected_delivery_date ? $investment->expected_delivery_date->format('Y-m-d') : 'N/A' }}</span>
                </x-list-item-value>

                @if($investment->merchandise_arrived_at)
                    <x-list-item-value
                        icon="ki-check-circle"
                        :iconPaths="2"
                        color="success"
                        title="Arrived At"
                        subtitle="Actual delivery date"
                        :isLast="true"
                    >
                        <span class="text-success fw-bold fs-6">{{ $investment->merchandise_arrived_at->format('Y-m-d h:i A') }}</span>
                    </x-list-item-value>
                @endif
            </x-card-header>
        @endif

        {{-- Distribution Status --}}
        <x-card-header
            title="Distribution Status"
            :subtitle="$investment->isMyselfType() ? 'Profit distribution after delivery' : 'Profit distribution'"
            :class="$investment->isMyselfType() ? 'col-md-6' : 'col-md-12'"
        >
            <x-list-item-value
                icon="ki-{{ $investment->isDistributed() ? 'check-circle' : 'time' }}"
                :iconPaths="$investment->isDistributed() ? 2 : 3"
                :color="$investment->isDistributed() ? 'success' : 'warning'"
                title="Distribution Status"
                :subtitle="$investment->isMyselfType() ? 'After merchandise arrival' : 'After actual returns'"
            >
                <span class="badge badge-{{ $investment->isDistributed() ? 'success' : 'warning' }} fs-5 px-4 py-2">
                    {{ $investment->isDistributed() ? 'Distributed' : 'Pending' }}
                </span>
            </x-list-item-value>

            @if($investment->expected_distribution_date)
                <x-list-item-value
                    icon="ki-calendar"
                    :iconPaths="3"
                    color="info"
                    title="Expected Distribution"
                    subtitle="Estimated distribution date"
                >
                    <span class="text-gray-800 fw-bold fs-6">{{ $investment->expected_distribution_date->format('Y-m-d') }}</span>
                </x-list-item-value>
            @endif

            @if($investment->distributed_profit)
                <x-list-item-value
                    icon="ki-dollar"
                    :iconPaths="3"
                    color="success"
                    title="Distributed Amount"
                    subtitle="Amount paid to investor"
                >
                    <span class="text-success fw-bold fs-4">${{ number_format($investment->distributed_profit, 2) }}</span>
                </x-list-item-value>
                    @endif

            @if($investment->distributed_at)
                <x-list-item-value
                    icon="ki-calendar-tick"
                    :iconPaths="5"
                    color="success"
                    title="Distributed At"
                    subtitle="Distribution completion date"
                    :isLast="true"
                >
                    <span class="text-success fw-bold fs-6">{{ $investment->distributed_at->format('Y-m-d h:i A') }}</span>
                </x-list-item-value>
            @endif
        </x-card-header>
    </div>

    {{-- Performance Summary Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-4">
            <div class="card card-flush h-100 shadow-sm">
                <div class="card-body text-center py-8">
                    <div class="symbol symbol-75px mx-auto mb-5">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-wallet fs-3x text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </span>
                    </div>
                    <div class="fs-2hx fw-bold text-gray-800 mb-2">${{ number_format($investment->getTotalInvestmentCost(), 2) }}</div>
                    <div class="fs-6 fw-semibold text-gray-600 mb-1">Investment Cost</div>
                    <div class="fs-7 text-muted">
                        {{ $investment->isMyselfType() ? 'Including shipping fees' : 'Total payment required' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-flush h-100 shadow-sm">
                <div class="card-body text-center py-8">
                    <div class="symbol symbol-75px mx-auto mb-5">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-duotone ki-chart-line-up fs-3x text-success">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="fs-2hx fw-bold text-success mb-2">${{ number_format($investment->getExpectedNetProfit(), 2) }}</div>
                    <div class="fs-6 fw-semibold text-gray-600 mb-1">Expected Returns</div>
                    <div class="badge badge-light-success fs-7">{{ number_format($investment->getExpectedNetProfitPercentage(), 1) }}% ROI</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-flush h-100 shadow-sm">
                <div class="card-body text-center py-8">
                    <div class="symbol symbol-75px mx-auto mb-5">
                        <span class="symbol-label bg-light-{{ $investment->hasActualReturns() ? 'info' : 'warning' }}">
                            <i class="ki-duotone ki-finance-calculator fs-3x text-{{ $investment->hasActualReturns() ? 'info' : 'warning' }}">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                                <span class="path6"></span>
                                <span class="path7"></span>
                            </i>
                        </span>
                    </div>
                        @if($investment->hasActualReturns())
                        <div class="fs-2hx fw-bold text-info mb-2">${{ number_format($investment->getActualNetProfit(), 2) }}</div>
                        <div class="fs-6 fw-semibold text-gray-600 mb-1">Actual Returns</div>
                        <div class="badge badge-light-info fs-7">{{ number_format($investment->getActualNetProfitPercentage(), 1) }}% ROI</div>
                        @else
                        <div class="fs-2x fw-bold text-warning mb-2">Not Set</div>
                        <div class="fs-6 fw-semibold text-gray-600 mb-1">Actual Returns</div>
                        <div class="fs-7 text-muted">
                            {{ $investment->isMyselfType() ? 'After merchandise arrival' : 'Pending recording' }}
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <x-card-header title="Investment Timeline" subtitle="Activity history" class="col-md-12 mb-7">
        <div class="timeline-label">
            {{-- Created --}}
            <div class="timeline-item">
                <div class="timeline-label fw-bold text-gray-800 fs-6">
                    {{ $investment->created_at->format('M d, Y') }}
                </div>
                <div class="timeline-badge">
                    <i class="ki-duotone ki-abstract-8 fs-2 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <div class="timeline-content fw-bold text-gray-800 ps-3">
                    Investment Created
                    <span class="text-muted fw-normal fs-7 d-block">{{ $investment->created_at->format('h:i A') }}</span>
                </div>
            </div>

            {{-- Investment Date --}}
            @if($investment->investment_date)
            <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-6">
                        {{ $investment->investment_date->format('M d, Y') }}
                    </div>
                    <div class="timeline-badge">
                        <i class="ki-duotone ki-check fs-2 text-success">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <div class="timeline-content fw-bold text-gray-800 ps-3">
                        Investment Confirmed
                        <span class="text-muted fw-normal fs-7 d-block">{{ $investment->investment_date->format('h:i A') }}</span>
                    </div>
                </div>
            @endif

            {{-- Merchandise Arrived (only for Myself type) --}}
            @if($investment->isMyselfType() && $investment->merchandise_arrived_at)
                <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-6">
                        {{ $investment->merchandise_arrived_at->format('M d, Y') }}
                    </div>
                    <div class="timeline-badge">
                        <i class="ki-duotone ki-package fs-2 text-success">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </div>
                    <div class="timeline-content fw-bold text-gray-800 ps-3">
                        Merchandise Arrived
                        <span class="text-success fw-semibold fs-7 d-block">Physical delivery completed</span>
                        <span class="text-muted fw-normal fs-7 d-block">{{ $investment->merchandise_arrived_at->format('h:i A') }}</span>
                    </div>
            </div>
        @endif

            {{-- Actual Returns Recorded --}}
        @if($investment->actual_returns_recorded_at)
            <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-6">
                        {{ $investment->actual_returns_recorded_at->format('M d, Y') }}
                    </div>
                    <div class="timeline-badge">
                        <i class="ki-duotone ki-chart-simple fs-2 text-info">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </div>
                    <div class="timeline-content fw-bold text-gray-800 ps-3">
                        Actual Returns Recorded
                        <span class="text-info fw-semibold fs-7 d-block">
                            {{ $investment->isMyselfType() ? 'After merchandise sale' : 'After investment period' }}
                        </span>
                        <span class="text-muted fw-normal fs-7 d-block">{{ $investment->actual_returns_recorded_at->format('h:i A') }}</span>
                </div>
            </div>
        @endif

            {{-- Distributed --}}
        @if($investment->distributed_at)
            <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-6">
                        {{ $investment->distributed_at->format('M d, Y') }}
                    </div>
                    <div class="timeline-badge">
                        <i class="ki-duotone ki-dollar fs-2 text-success">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </div>
                    <div class="timeline-content fw-bold text-gray-800 ps-3">
                        Profits Distributed
                        <span class="text-success fw-semibold fs-7 d-block">${{ number_format($investment->distributed_profit, 2) }}</span>
                        <span class="text-info fw-semibold fs-7 d-block">
                            {{ $investment->isMyselfType() ? 'After merchandise delivery' : 'After actual returns' }}
                        </span>
                        <span class="text-muted fw-normal fs-7 d-block">{{ $investment->distributed_at->format('h:i A') }}</span>
                </div>
            </div>
        @endif
    </div>
    </x-card-header>

</div>

{{-- Actions --}}
<div class="text-center pt-10 pb-5">
    <button type="button" class="btn btn-light me-3 close" data-bs-dismiss="modal">
        <i class="ki-duotone ki-cross fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        Close
    </button>

    {{-- Merchandise Delivery Action (only for Myself type, not arrived yet) --}}
    @if($investment->isMyselfType() && $investment->isReadyForMerchandiseArrival())
        <button type="button"
                class="btn btn-success me-3 admin-action-btn"
                data-action="{{ route('admin.investments.mark-merchandise-arrived', $investment->id) }}"
                data-method="POST"
                data-confirm="true"
                data-confirm-text="Are you sure you want to mark merchandise as arrived for this investment?">
            <i class="ki-duotone ki-package fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Mark Merchandise Arrived
        </button>
    @endif

    {{-- Distribute Profit Action (only for Authorize type, ready for distribution) --}}
    @if($investment->isAuthorizeType() && $investment->isReadyForDistribution())
        <button type="button"
                class="btn btn-warning me-3 admin-action-btn"
                data-action="{{ route('admin.investments.distribute-profit', $investment->id) }}"
                data-method="POST"
                data-confirm="true"
                data-confirm-text="Are you sure you want to distribute profit for this investment?">
            <i class="ki-duotone ki-dollar fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Distribute Profit
        </button>
    @endif

    <a href="#"
       class="btn btn-primary has_action me-3"
       data-type="edit"
       data-action="{{ route('admin.investments.edit', $investment->id) }}">
        <i class="ki-duotone ki-pencil fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        Edit Investment
    </a>
</div>
