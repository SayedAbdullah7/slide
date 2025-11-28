@props([
    'statistics' => []
])

<div class="col-xl-12">
    <div class="card card-flush">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <div class="d-flex align-items-center">
                    <i class="ki-duotone ki-chart-pie fs-2x text-info me-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    <span class="fs-4 fw-semibold text-gray-900">إحصائيات الاستثمارات</span>
                </div>
            </div>
        </div>
        <div class="card-body pt-2 pb-4">
            <div class="row g-5">
                @foreach($statistics as $stat)
                    <div class="col-md-3">
                        <div class="d-flex flex-column">
                            <span class="fs-6 text-gray-600 mb-1">{{ $stat['label'] }}</span>
                            <span class="fs-2x fw-bold text-{{ $stat['textColor'] ?? 'gray-900' }}">{{ $stat['value'] }}</span>
                            @if(isset($stat['badge']))
                                <div class="d-flex align-items-center mt-2">
                                    <span class="badge badge-light-{{ $stat['badge']['color'] ?? 'primary' }} me-2">
                                        {{ $stat['badge']['text'] }}
                                    </span>
                                    @if(isset($stat['badge']['subtext']))
                                        <span class="fs-7 text-gray-600">{{ $stat['badge']['subtext'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
