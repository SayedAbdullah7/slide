<x-app-layout>
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Page header-->
            <div class="d-flex flex-wrap flex-stack mb-5 mb-xl-8">
                <h3 class="fw-bold my-2">لوحة التحكم
                    <span class="text-muted fw-semibold fs-6 ms-1">/ نظرة عامة</span>
                </h3>
            </div>
            <!--end::Page header-->

            <!--begin::Row - Main Stats Cards-->
            <div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
                <!--begin::Col - Opportunities Stats-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget - Opportunities Total-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $opportunitiesStats['total'] }}</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">الفرص الاستثمارية</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex align-items-end pt-0">
                            <!--begin::Progress-->
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                                    <span>{{ $opportunitiesStats['open'] }} مفتوحة</span>
                                    <span>{{ number_format($opportunitiesStats['avg_completion_rate'] ?? 0, 1) }}%</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ min($opportunitiesStats['avg_completion_rate'] ?? 0, 100) }}%;" aria-valuenow="{{ $opportunitiesStats['avg_completion_rate'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <!--end::Progress-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->

                    <!--begin::Card widget - Opportunities Quick Stats-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($opportunitiesStats['total_raised'] ?? 0, 0) }}</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">إجمالي المبلغ المُجمع (ر.س)</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex flex-column justify-content-end pe-0">
                            <!--begin::Stats-->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-600 fw-semibold fs-7">الهدف:</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ number_format($opportunitiesStats['total_target_amount'] ?? 0, 0) }} ر.س</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-600 fw-semibold fs-7">الأسهم المحجوزة:</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ number_format($opportunitiesStats['total_reserved_shares'] ?? 0, 0) }}</span>
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->

                <!--begin::Col - Investments Stats-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget - Investments Total-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #009EF7;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $investmentsStats['total'] }}</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">الاستثمارات</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex align-items-end pt-0">
                            <!--begin::Progress-->
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                                    <span>{{ $investmentsStats['active'] }} نشطة</span>
                                    <span>{{ $investmentsStats['completed'] }} مكتملة</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $investmentsStats['total'] > 0 ? ($investmentsStats['completed'] / $investmentsStats['total'] * 100) : 0 }}%;" aria-valuenow="{{ $investmentsStats['completed'] }}" aria-valuemin="0" aria-valuemax="{{ $investmentsStats['total'] }}"></div>
                                </div>
                            </div>
                            <!--end::Progress-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->

                    <!--begin::Card widget - Investments Amount-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($investmentsStats['total_amount'] ?? 0, 0) }}</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">إجمالي الاستثمارات (ر.س)</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex flex-column justify-content-end pe-0">
                            <!--begin::Stats-->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-600 fw-semibold fs-7">الربح المتوقع:</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ number_format($investmentsStats['total_expected_profit'] ?? 0, 0) }} ر.س</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-600 fw-semibold fs-7">الربح الفعلي:</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ number_format($investmentsStats['total_actual_profit'] ?? 0, 0) }} ر.س</span>
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->

                <!--begin::Col - Financial Stats-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget - Financial Total-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #50CD89;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($financialsStats['total_wallet_balance'] ?? 0, 0) }}</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">الرصيد الإجمالي (ر.س)</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex align-items-end pt-0">
                            <!--begin::Progress-->
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                                    <span>إيداعات</span>
                                    <span>{{ number_format($financialsStats['total_deposits_amount'] ?? 0, 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mb-2">
                                    <span>سحوبات</span>
                                    <span>{{ number_format($financialsStats['total_withdrawals_amount'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                            <!--end::Progress-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->

                    <!--begin::Card widget - Transactions Count-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $financialsStats['total_transactions'] }}</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">إجمالي المعاملات</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex flex-column justify-content-end pe-0">
                            <!--begin::Stats-->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-gray-600 fw-semibold fs-7">مؤكدة:</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ $financialsStats['confirmed_transactions'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-gray-600 fw-semibold fs-7">معلقة:</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ $financialsStats['pending_transactions'] }}</span>
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->

                <!--begin::Col - Quick Links-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget - Quick Actions-->
                    <div class="card card-flush h-lg-50">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title text-gray-800 fw-bold">روابط سريعة</h3>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-5">
                            <!--begin::Item-->
                            <div class="d-flex flex-stack mb-3">
                                <!--begin::Section-->
                                <a href="{{ route('admin.investment-opportunities.index') }}" class="text-primary fw-semibold fs-6 me-2">الفرص الاستثمارية</a>
                                <!--end::Section-->
                                <!--begin::Action-->
                                <i class="ki-duotone ki-exit-right-corner fs-2 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <!--end::Action-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack mb-3">
                                <!--begin::Section-->
                                <a href="{{ route('admin.investments.index') }}" class="text-primary fw-semibold fs-6 me-2">الاستثمارات</a>
                                <!--end::Section-->
                                <!--begin::Action-->
                                <i class="ki-duotone ki-exit-right-corner fs-2 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <!--end::Action-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <a href="{{ route('admin.transactions.index') }}" class="text-primary fw-semibold fs-6 me-2">المعاملات المالية</a>
                                <!--end::Section-->
                                <!--begin::Action-->
                                <i class="ki-duotone ki-exit-right-corner fs-2 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <!--end::Action-->
                            </div>
                            <!--end::Item-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row - Charts and Tables-->
            <div class="row gx-5 gx-xl-10">
                <!--begin::Col - Opportunities Table-->
                <div class="col-xxl-6 mb-5 mb-xl-10">
                    <!--begin::Table widget - Recent Opportunities-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">أحدث الفرص الاستثمارية</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">آخر {{ $recentOpportunities->count() }} فرصة</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="{{ route('admin.investment-opportunities.index') }}" class="btn btn-sm btn-light">عرض الكل</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th class="p-0 pb-3 min-w-150px text-start">الاسم</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">الحالة</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">المبلغ المُجمع</th>
                                            <th class="p-0 pb-3 w-50px text-end">عرض</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                        @forelse($recentOpportunities as $opportunity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="{{ route('admin.investment-opportunities.show', $opportunity->id) }}" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">{{ $opportunity->name }}</a>
                                                        <span class="text-gray-500 fw-semibold d-block fs-7">{{ $opportunity->category->name ?? 'بدون فئة' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end pe-0">
                                                @if($opportunity->status == 'open')
                                                    <span class="badge badge-light-success">مفتوحة</span>
                                                @elseif($opportunity->status == 'completed')
                                                    <span class="badge badge-light-primary">مكتملة</span>
                                                @else
                                                    <span class="badge badge-light-warning">{{ $opportunity->status }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="text-gray-600 fw-bold fs-6">{{ number_format($opportunity->completion_rate ?? 0, 1) }}%</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.investment-opportunities.show', $opportunity->id) }}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                    <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">لا توجد فرص استثمارية</td>
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
                    <!--end::Table widget-->
                </div>
                <!--end::Col-->

                <!--begin::Col - Investments Table-->
                <div class="col-xxl-6 mb-5 mb-xl-10">
                    <!--begin::Table widget - Recent Investments-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">أحدث الاستثمارات</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">آخر {{ $recentInvestments->count() }} استثمار</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="{{ route('admin.investments.index') }}" class="btn btn-sm btn-light">عرض الكل</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th class="p-0 pb-3 min-w-150px text-start">المستثمر</th>
                                            <th class="p-0 pb-3 min-w-150px text-start">الفرصة</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">المبلغ</th>
                                            <th class="p-0 pb-3 w-50px text-end">عرض</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                        @forelse($recentInvestments as $investment)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-gray-800 fw-bold mb-1 fs-6">{{ $investment->user->full_name ?? $investment->user->email ?? 'غير معروف' }}</span>
                                                        <span class="text-gray-500 fw-semibold d-block fs-7">{{ number_format($investment->shares ?? 0, 0) }} سهم</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-semibold fs-7">{{ $investment->investmentOpportunity->name ?? 'غير معروف' }}</span>
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="text-gray-600 fw-bold fs-6">{{ number_format($investment->total_investment ?? 0, 0) }} ر.س</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.investments.show', $investment->id) }}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                    <i class="ki-duotone ki-black-right fs-2 text-gray-500"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">لا توجد استثمارات</td>
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
                    <!--end::Table widget-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row - Financial Details-->
            <div class="row gx-5 gx-xl-10 mb-5 mb-xl-10">
                <!--begin::Col - Withdrawal Requests-->
                <div class="col-xl-6 mb-5 mb-xl-10">
                    <!--begin::Card widget - Withdrawal Requests-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">طلبات السحب</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">إجمالي {{ $withdrawalRequestsStats['total'] }} طلب</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-light">عرض الكل</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Stats-->
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-5">
                                    <span class="fw-semibold text-gray-600 fs-6">معلقة</span>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800 fs-3">{{ $withdrawalRequestsStats['pending'] }}</span>
                                        <span class="fw-semibold text-gray-500 fs-7 ms-2">{{ number_format($withdrawalRequestsStats['total_pending_amount'] ?? 0, 0) }} ر.س</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-5">
                                    <span class="fw-semibold text-gray-600 fs-6">قيد المعالجة</span>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800 fs-3">{{ $withdrawalRequestsStats['processing'] }}</span>
                                        <span class="fw-semibold text-gray-500 fs-7 ms-2">{{ number_format($withdrawalRequestsStats['total_processing_amount'] ?? 0, 0) }} ر.س</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-gray-600 fs-6">مكتملة</span>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800 fs-3">{{ $withdrawalRequestsStats['completed'] }}</span>
                                        <span class="fw-semibold text-gray-500 fs-7 ms-2">{{ number_format($withdrawalRequestsStats['total_completed_amount'] ?? 0, 0) }} ر.س</span>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->

                <!--begin::Col - Bank Transfers-->
                <div class="col-xl-6 mb-5 mb-xl-10">
                    <!--begin::Card widget - Bank Transfers-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">التحويلات البنكية</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">إجمالي {{ $bankTransferRequestsStats['total'] }} طلب</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="{{ route('admin.bank-transfers.index') }}" class="btn btn-sm btn-light">عرض الكل</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Stats-->
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-5">
                                    <span class="fw-semibold text-gray-600 fs-6">معلقة</span>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800 fs-3">{{ $bankTransferRequestsStats['pending'] }}</span>
                                        <span class="fw-semibold text-gray-500 fs-7 ms-2">{{ number_format($bankTransferRequestsStats['total_pending_amount'] ?? 0, 0) }} ر.س</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-5">
                                    <span class="fw-semibold text-gray-600 fs-6">مقبولة</span>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800 fs-3">{{ $bankTransferRequestsStats['approved'] }}</span>
                                        <span class="fw-semibold text-gray-500 fs-7 ms-2">{{ number_format($bankTransferRequestsStats['total_approved_amount'] ?? 0, 0) }} ر.س</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-gray-600 fs-6">مرفوضة</span>
                                    <div class="text-end">
                                        <span class="fw-bold text-gray-800 fs-3">{{ $bankTransferRequestsStats['rejected'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row - Recent Transactions-->
            <div class="row gx-5 gx-xl-10">
                <!--begin::Col - Recent Transactions-->
                <div class="col-xxl-12">
                    <!--begin::Table widget - Recent Transactions-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">أحدث المعاملات المالية</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">آخر {{ $recentTransactions->count() }} معاملة</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-light">عرض الكل</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th class="p-0 pb-3 min-w-150px text-start">النوع</th>
                                            <th class="p-0 pb-3 min-w-150px text-start">المستخدم</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">المبلغ</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">الحالة</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                        @forelse($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                @if($transaction->type == 'deposit')
                                                    <span class="badge badge-light-success">إيداع</span>
                                                @else
                                                    <span class="badge badge-light-danger">سحب</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-semibold fs-7">{{ $transaction->payable_name ?? 'غير معروف' }}</span>
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="text-gray-600 fw-bold fs-6">{{ number_format(floatval($transaction->amount) / 100, 2) }} ر.س</span>
                                            </td>
                                            <td class="text-end pe-0">
                                                @if($transaction->confirmed)
                                                    <span class="badge badge-light-success">مؤكدة</span>
                                                @else
                                                    <span class="badge badge-light-warning">معلقة</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="text-gray-500 fw-semibold fs-7">{{ $transaction->created_at->format('Y-m-d H:i') }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-gray-500">لا توجد معاملات</td>
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
                    <!--end::Table widget-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
</x-app-layout>

