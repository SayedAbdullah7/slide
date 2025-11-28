<div class="mb-7">
    <h3 class="fw-bold text-gray-900 mb-4">حالة العوائد للفرصة: {{ $opportunity->name }}</h3>
</div>

<div class="row g-5 mb-7">
    <div class="col-md-4">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h4 class="fw-bold text-gray-900">الاستثمارات المفوضة</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="fs-2x fw-bold text-primary me-3">{{ $opportunity->investments()->where('investment_type', 'authorize')->count() }}</span>
                    <div>
                        <div class="fs-6 text-gray-600">إجمالي الاستثمارات</div>
                        <div class="fs-7 text-gray-500">تحتاج تسجيل عوائد</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h4 class="fw-bold text-gray-900">العوائد المسجلة</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="fs-2x fw-bold text-success me-3">{{ $opportunity->investments()->where('investment_type', 'authorize')->whereNotNull('actual_profit_per_share')->count() }}</span>
                    <div>
                        <div class="fs-6 text-gray-600">تم تسجيل العوائد</div>
                        <div class="fs-7 text-gray-500">من إجمالي {{ $opportunity->investments()->where('investment_type', 'authorize')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h4 class="fw-bold text-gray-900">حالة التوزيع</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if($opportunity->all_returns_distributed)
                        <span class="badge badge-success fs-6 me-3">تم التوزيع</span>
                    @else
                        <span class="badge badge-warning fs-6 me-3">في الانتظار</span>
                    @endif
                    <div>
                        <div class="fs-6 text-gray-600">حالة التوزيع</div>
                        <div class="fs-7 text-gray-500">آخر تحديث: {{ $opportunity->updated_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($opportunity->investments()->where('investment_type', 'authorize')->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>المستثمر</th>
                    <th>عدد الأسهم</th>
                    <th>الربح الفعلي لكل سهم</th>
                    <th>صافي الربح لكل سهم</th>
                    <th>حالة التوزيع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($opportunity->investments()->where('investment_type', 'authorize')->get() as $investment)
                    <tr>
                        <td>{{ $investment->investorProfile->user->display_name ?? 'N/A' }}</td>
                        <td>{{ number_format($investment->shares) }}</td>
                        <td>
                            @if($investment->actual_profit_per_share)
                                ${{ number_format($investment->actual_profit_per_share, 2) }}
                            @else
                                <span class="text-muted">لم يتم التسجيل</span>
                            @endif
                        </td>
                        <td>
                            @if($investment->actual_net_profit_per_share)
                                ${{ number_format($investment->actual_net_profit_per_share, 2) }}
                            @else
                                <span class="text-muted">لم يتم التسجيل</span>
                            @endif
                        </td>
                        <td>
                            @if($investment->distribution_status === 'distributed')
                                <span class="badge badge-success">تم التوزيع</span>
                            @else
                                <span class="badge badge-warning">في الانتظار</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
