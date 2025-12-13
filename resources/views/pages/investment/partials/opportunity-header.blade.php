{{-- Opportunity Header Card - Only show when filtering by specific opportunity --}}
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
</div>
