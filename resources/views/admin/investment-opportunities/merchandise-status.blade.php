<div class="mb-7">
    <h3 class="fw-bold text-gray-900 mb-4">حالة البضائع للفرصة: {{ $opportunity->name }}</h3>
</div>

<div class="row g-5 mb-7">
    <div class="col-md-6">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h4 class="fw-bold text-gray-900">إجمالي الاستثمارات</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="fs-2x fw-bold text-primary me-3">{{ $opportunity->investments()->where('investment_type', 'myself')->count() }}</span>
                    <div>
                        <div class="fs-6 text-gray-600">استثمارات البيع بنفسي</div>
                        <div class="fs-7 text-gray-500">تحتاج تسليم بضائع</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title">
                    <h4 class="fw-bold text-gray-900">حالة التسليم</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if($opportunity->all_merchandise_delivered)
                        <span class="badge badge-success fs-6 me-3">تم التسليم</span>
                    @else
                        <span class="badge badge-warning fs-6 me-3">في الانتظار</span>
                    @endif
                    <div>
                        <div class="fs-6 text-gray-600">حالة البضائع</div>
                        <div class="fs-7 text-gray-500">آخر تحديث: {{ $opportunity->updated_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($opportunity->investments()->where('investment_type', 'myself')->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>المستثمر</th>
                    <th>عدد الأسهم</th>
                    <th>حالة البضائع</th>
                    <th>تاريخ الاستثمار</th>
                </tr>
            </thead>
            <tbody>
                @foreach($opportunity->investments()->where('investment_type', 'myself')->get() as $investment)
                    <tr>
                        <td>{{ $investment->investorProfile->user->display_name ?? 'N/A' }}</td>
                        <td>{{ number_format($investment->shares) }}</td>
                        <td>
                            @if($investment->merchandise_status === 'arrived')
                                <span class="badge badge-success">وصلت</span>
                            @else
                                <span class="badge badge-warning">في الانتظار</span>
                            @endif
                        </td>
                        <td>{{ $investment->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
