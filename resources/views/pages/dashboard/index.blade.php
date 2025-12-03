<x-app-layout>
    <div id="kt_app_content_container" class="app-container container-xxl">

    <!--begin::Page header-->
    <div class="d-flex flex-wrap flex-stack mb-5 mb-xl-8">
        <h3 class="fw-bold my-2">Dashboard
            <span class="text-muted fw-semibold fs-6 ms-1">/ Overview & Statistics</span>
        </h3>
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            @if(array_sum(array_values($pendingActions)) > 0)
            <div class="position-relative">
                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-primary">
                    <i class="ki-duotone ki-notification-status fs-3 me-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    Pending Actions
                    <span class="badge badge-circle badge-danger position-absolute translate-middle top-0 start-100 border border-white">
                        {{ array_sum(array_values($pendingActions)) }}
                    </span>
                </a>
            </div>
            @endif
        </div>
    </div>
    <!--end::Page header-->

    <!--begin::Pending Actions Alert-->
    @if(array_sum(array_values($pendingActions)) > 0)
    <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5 mb-5 mb-xl-10">
        <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4 mb-5 mb-sm-0">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        <div class="d-flex flex-column pe-0 pe-sm-10">
            <h4 class="fw-semibold mb-1">Action Required</h4>
            <span>You have <strong class="text-warning">{{ array_sum(array_values($pendingActions)) }} pending item(s)</strong> that need your attention:</span>
            <div class="d-flex flex-wrap gap-2 mt-3">
                @if($pendingActions['pending_withdrawals'] > 0)
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-warning">
                        <i class="ki-duotone ki-arrow-up fs-5 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ $pendingActions['pending_withdrawals'] }} Withdrawal(s)
                    </a>
                @endif
                @if($pendingActions['pending_bank_transfers'] > 0)
                    <a href="{{ route('admin.bank-transfers.index') }}" class="btn btn-sm btn-warning">
                        <i class="ki-duotone ki-bank fs-5 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ $pendingActions['pending_bank_transfers'] }} Bank Transfer(s)
                    </a>
                @endif
                @if($pendingActions['pending_deletion_requests'] > 0)
                    <a href="{{ route('admin.user-deletion-requests.index') }}" class="btn btn-sm btn-warning">
                        <i class="ki-duotone ki-trash fs-5 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ $pendingActions['pending_deletion_requests'] }} Deletion Request(s)
                    </a>
                @endif
                @if($pendingActions['pending_contact_messages'] > 0)
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-sm btn-warning">
                        <i class="ki-duotone ki-message-text fs-5 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ $pendingActions['pending_contact_messages'] }} Contact Message(s)
                    </a>
                @endif
                @if($pendingActions['pending_transactions'] > 0)
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-warning">
                        <i class="ki-duotone ki-dollar fs-5 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ $pendingActions['pending_transactions'] }} Transaction(s)
                    </a>
                @endif
            </div>
        </div>
        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="alert">
            <i class="ki-duotone ki-cross fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </button>
    </div>
    @endif
    <!--end::Pending Actions Alert-->

    <!--begin::Row - Main Statistics-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
        <!--begin::Col - Users-->
        <div class="col-xl-3">
            <!--begin::Card widget 16-->
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-center border-0 h-xl-100" style="background-color: #f1416c">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($usersStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-white opacity-50 pt-1 fw-semibold fs-6">Total Users</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex align-items-end pt-0">
                    <!--begin::Progress-->
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-50 w-100 mt-auto mb-2">
                            <span>{{ number_format($usersStats['active']) }} Active</span>
                            <span>{{ $usersStats['total'] > 0 ? number_format(($usersStats['active'] / $usersStats['total']) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-primary rounded">
                            <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $usersStats['total'] > 0 ? ($usersStats['active'] / $usersStats['total']) * 100 : 0 }}%;" aria-valuenow="{{ $usersStats['active'] }}" aria-valuemin="0" aria-valuemax="{{ $usersStats['total'] }}"></div>
                        </div>
                        <div class="d-flex justify-content-between w-100 mt-4">
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Investors</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($usersStats['with_investor_profile']) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Owners</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($usersStats['with_owner_profile']) }}</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Progress-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 16-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Opportunities-->
        <div class="col-xl-3">
            <!--begin::Card widget 16-->
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-center border-0 h-xl-100" style="background-color: #50cd89">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($opportunitiesStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-white opacity-50 pt-1 fw-semibold fs-6">Opportunities</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex align-items-end pt-0">
                    <!--begin::Progress-->
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-50 w-100 mt-auto mb-2">
                            <span>{{ number_format($opportunitiesStats['active']) }} Active</span>
                            <span>{{ $opportunitiesStats['total'] > 0 ? number_format(($opportunitiesStats['active'] / $opportunitiesStats['total']) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-success rounded">
                            <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $opportunitiesStats['total'] > 0 ? ($opportunitiesStats['active'] / $opportunitiesStats['total']) * 100 : 0 }}%;" aria-valuenow="{{ $opportunitiesStats['active'] }}" aria-valuemin="0" aria-valuemax="{{ $opportunitiesStats['total'] }}"></div>
                        </div>
                        <div class="d-flex justify-content-between w-100 mt-4">
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Pending</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($opportunitiesStats['pending']) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Completed</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($opportunitiesStats['completed']) }}</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Progress-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 16-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Investments-->
        <div class="col-xl-3">
            <!--begin::Card widget 16-->
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-center border-0 h-xl-100" style="background-color: #7239ea">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($investmentsStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-white opacity-50 pt-1 fw-semibold fs-6">Investments</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex align-items-end pt-0">
                    <!--begin::Progress-->
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-50 w-100 mt-auto mb-2">
                            <span>{{ number_format($investmentsStats['total_amount'], 0) }} SAR</span>
                            <span>{{ number_format($investmentsStats['active']) }} Active</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-primary rounded">
                            <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $investmentsStats['total'] > 0 ? ($investmentsStats['active'] / $investmentsStats['total']) * 100 : 0 }}%;" aria-valuenow="{{ $investmentsStats['active'] }}" aria-valuemin="0" aria-valuemax="{{ $investmentsStats['total'] }}"></div>
                        </div>
                        <div class="d-flex justify-content-between w-100 mt-4">
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Pending Merch.</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($investmentsStats['pending_merchandise']) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Pending Dist.</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($investmentsStats['pending_distribution']) }}</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Progress-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 16-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Transactions-->
        <div class="col-xl-3">
            <!--begin::Card widget 16-->
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-center border-0 h-xl-100" style="background-color: #ffc700">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($transactionsStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-white opacity-50 pt-1 fw-semibold fs-6">Transactions</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex align-items-end pt-0">
                    <!--begin::Progress-->
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-50 w-100 mt-auto mb-2">
                            <span>{{ number_format($transactionsStats['pending']) }} Pending</span>
                            <span>{{ $transactionsStats['this_month'] }} This Month</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-warning rounded">
                            <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $transactionsStats['total'] > 0 ? ($transactionsStats['confirmed'] / $transactionsStats['total']) * 100 : 0 }}%;" aria-valuenow="{{ $transactionsStats['confirmed'] }}" aria-valuemin="0" aria-valuemax="{{ $transactionsStats['total'] }}"></div>
                        </div>
                        <div class="d-flex justify-content-between w-100 mt-4">
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Deposits</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($transactionsStats['deposits']) }}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-white opacity-75 fw-semibold fs-7">Withdrawals</span>
                                <span class="text-white fw-bold fs-6">{{ number_format($transactionsStats['withdrawals']) }}</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Progress-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 16-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row - Financial Overview-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
        <!--begin::Col - Deposits-->
        <div class="col-xl-4">
            <!--begin::Card widget 7-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($transactionsStats['total_deposits_amount'], 0) }}</span>
                            <!--end::Amount-->
                            <!--begin::Currency-->
                            <span class="fs-4 fw-semibold text-gray-500 align-self-start me-1">SAR</span>
                            <!--end::Currency-->
                        </div>
                        <!--end::Info-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Deposits</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mb-2">
                        <i class="ki-duotone ki-arrow-down fs-2x text-success me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <span class="text-gray-700 fw-semibold fs-6">All confirmed deposits</span>
                    </div>
                    <!--end::Statistics-->
                    <!--begin::Actions-->
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-light-success align-self-start">View Transactions</a>
                    <!--end::Actions-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 7-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Withdrawals-->
        <div class="col-xl-4">
            <!--begin::Card widget 7-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($transactionsStats['total_withdrawals_amount'], 0) }}</span>
                            <!--end::Amount-->
                            <!--begin::Currency-->
                            <span class="fs-4 fw-semibold text-gray-500 align-self-start me-1">SAR</span>
                            <!--end::Currency-->
                        </div>
                        <!--end::Info-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Withdrawals</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mb-2">
                        <i class="ki-duotone ki-arrow-up fs-2x text-danger me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <span class="text-gray-700 fw-semibold fs-6">All confirmed withdrawals</span>
                    </div>
                    <!--end::Statistics-->
                    <!--begin::Actions-->
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-light-danger align-self-start">View Withdrawals</a>
                    <!--end::Actions-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 7-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Pending Withdrawals-->
        <div class="col-xl-4">
            <!--begin::Card widget 7-->
            <div class="card card-flush border border-warning h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5 border-bottom border-warning">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Info-->
                        <div class="d-flex align-items-center">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($withdrawalsStats['total_pending_amount'], 0) }}</span>
                            <!--end::Amount-->
                            <!--begin::Currency-->
                            <span class="fs-4 fw-semibold text-gray-500 align-self-start me-1">SAR</span>
                            <!--end::Currency-->
                        </div>
                        <!--end::Info-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Pending Withdrawals</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mb-2">
                        <i class="ki-duotone ki-clock fs-2x text-warning me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <span class="text-gray-700 fw-semibold fs-6">{{ $withdrawalsStats['pending'] }} Request(s) Awaiting Approval</span>
                    </div>
                    <!--end::Statistics-->
                    <!--begin::Actions-->
                    @if($withdrawalsStats['pending'] > 0)
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-warning align-self-start">Review Now</a>
                    @endif
                    <!--end::Actions-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 7-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row - Performance Chart-->
    <x-performance-chart-widget
        :investment-performance="$investmentPerformance"
        :api-url="route('admin.dashboard.investment-performance')"
        chart-id="kt_charts_widget_36"
        :series="[
            [
                'key' => 'expected_profit',
                'name' => 'Expected Profit (SAR)',
                'color' => 'primary',
                'scale' => 1,
                'unit' => 'SAR'
            ],
            [
                'key' => 'actual_profit',
                'name' => 'Actual Profit (SAR)',
                'color' => 'success',
                'scale' => 1,
                'unit' => 'SAR'
            ],
            // [
            //     'key' => 'short_investments',
            //     'name' => 'Short Investments (Count)',
            //     'color' => 'warning',
            //     'scale' => 100,
            //     'unit' => 'Investments'
            // ],
            [
                'key' => 'investment_amount',
                'name' => 'Investment Amount (SAR)',
                'color' => 'info',
                'scale' => 1,
                'unit' => 'SAR'
            ],
        ]"
    />

    <!--begin::Row - Status Overview-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
        <!--begin::Col - Withdrawal Requests-->
        <div class="col-xl-3">
            <!--begin::Card widget 5-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($withdrawalsStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Withdrawal Requests</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body pt-6">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Pending</div>
                        <span class="badge badge-light-warning">{{ number_format($withdrawalsStats['pending']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Processing</div>
                        <span class="badge badge-light-info">{{ number_format($withdrawalsStats['processing']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Completed</div>
                        <span class="badge badge-light-success">{{ number_format($withdrawalsStats['completed']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Rejected</div>
                        <span class="badge badge-light-danger">{{ number_format($withdrawalsStats['rejected']) }}</span>
                    </div>
                    <!--end::Item-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 5-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Bank Transfers-->
        <div class="col-xl-3">
            <!--begin::Card widget 5-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($bankTransfersStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Bank Transfers</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body pt-6">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Pending</div>
                        <span class="badge badge-light-warning">{{ number_format($bankTransfersStats['pending']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Approved</div>
                        <span class="badge badge-light-success">{{ number_format($bankTransfersStats['approved']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Rejected</div>
                        <span class="badge badge-light-danger">{{ number_format($bankTransfersStats['rejected']) }}</span>
                    </div>
                    <!--end::Item-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 5-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Deletion Requests-->
        <div class="col-xl-3">
            <!--begin::Card widget 5-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($deletionRequestsStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Deletion Requests</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body pt-6">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Pending</div>
                        <span class="badge badge-light-warning">{{ number_format($deletionRequestsStats['pending']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Approved</div>
                        <span class="badge badge-light-success">{{ number_format($deletionRequestsStats['approved']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Rejected</div>
                        <span class="badge badge-light-danger">{{ number_format($deletionRequestsStats['rejected']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Cancelled</div>
                        <span class="badge badge-light-secondary">{{ number_format($deletionRequestsStats['cancelled']) }}</span>
                    </div>
                    <!--end::Item-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 5-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Contact Messages-->
        <div class="col-xl-3">
            <!--begin::Card widget 5-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-5">
                    <!--begin::Title-->
                    <div class="card-title d-flex flex-column">
                        <!--begin::Amount-->
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($contactMessagesStats['total']) }}</span>
                        <!--end::Amount-->
                        <!--begin::Subtitle-->
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Contact Messages</span>
                        <!--end::Subtitle-->
                    </div>
                    <!--end::Title-->
                </div>
                <!--end::Header-->
                <!--begin::Card body-->
                <div class="card-body pt-6">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Pending</div>
                        <span class="badge badge-light-warning">{{ number_format($contactMessagesStats['pending']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">In Progress</div>
                        <span class="badge badge-light-info">{{ number_format($contactMessagesStats['in_progress']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Resolved</div>
                        <span class="badge badge-light-success">{{ number_format($contactMessagesStats['resolved']) }}</span>
                    </div>
                    <!--end::Item-->
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--end::Separator-->
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <div class="text-gray-700 fw-semibold fs-6 me-2">Closed</div>
                        <span class="badge badge-light-secondary">{{ number_format($contactMessagesStats['closed']) }}</span>
                    </div>
                    <!--end::Item-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 5-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row - Recent Activity-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col - Recent Users-->
        <div class="col-xl-6">
            <!--begin::Table widget 14-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-7">
                    <!--begin::Title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Recent Users</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Latest registered users</span>
                    </h3>
                    <!--end::Title-->
                    <!--begin::Toolbar-->
                    <div class="card-toolbar">
                        <a href="{{ route('user.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-6">
                    <!--begin::Table container-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table class="table table-row-dashed align-middle gs-0 gy-4">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px">User</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-100px text-end">Actions</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody>
                                @forelse($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-45px me-5">
                                                <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                    {{ strtoupper(substr($user->display_name ?? $user->email ?? 'N/A', 0, 2)) }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-start flex-column">
                                                <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6 has_action"
                                                   data-type="show"
                                                   data-action="{{ route('user.show', $user->id) }}">
                                                    {{ $user->display_name ?? $user->email ?? 'N/A' }}
                                                </a>
                                                <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                    {{ $user->email ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="#"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm has_action"
                                           data-type="show"
                                           data-action="{{ route('user.show', $user->id) }}">
                                            <i class="ki-duotone ki-eye fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-10">
                                        <i class="ki-duotone ki-information-5 fs-3x text-muted mb-5">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        <div>No recent users</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table container-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Table widget 14-->
        </div>
        <!--end::Col-->

        <!--begin::Col - Recent Investments-->
        <div class="col-xl-6">
            <!--begin::Table widget 14-->
            <div class="card card-flush h-xl-100">
                <!--begin::Header-->
                <div class="card-header pt-7">
                    <!--begin::Title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Recent Investments</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Latest investment activities</span>
                    </h3>
                    <!--end::Title-->
                    <!--begin::Toolbar-->
                    <div class="card-toolbar">
                        <a href="{{ route('admin.investments.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-6">
                    <!--begin::Table container-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table class="table table-row-dashed align-middle gs-0 gy-4">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px">User / Opportunity</th>
                                    <th class="min-w-100px">Amount</th>
                                    <th class="min-w-100px text-end">Actions</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody>
                                @forelse($recentInvestments as $investment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-45px me-5">
                                                <span class="symbol-label bg-light-success text-success fw-bold">
                                                    {{ strtoupper(substr($investment->user->display_name ?? 'N/A', 0, 2)) }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-start flex-column">
                                                <a href="#" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6 has_action"
                                                   data-type="show"
                                                   data-action="{{ route('admin.investments.show', $investment->id) }}">
                                                    {{ $investment->user->display_name ?? 'N/A' }}
                                                </a>
                                                <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                    {{ $investment->investmentOpportunity ? \Illuminate\Support\Str::limit($investment->investmentOpportunity->name, 30) : 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-gray-900 fw-bold fs-6">
                                            {{ number_format($investment->total_investment ?? 0, 0) }} <span class="text-muted fs-7">SAR</span>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="#"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm has_action"
                                           data-type="show"
                                           data-action="{{ route('admin.investments.show', $investment->id) }}">
                                            <i class="ki-duotone ki-eye fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-10">
                                        <i class="ki-duotone ki-information-5 fs-3x text-muted mb-5">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        <div>No recent investments</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table container-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Table widget 14-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
</div>

{{-- Performance Chart Widget scripts are loaded via the component --}}
</x-app-layout>
