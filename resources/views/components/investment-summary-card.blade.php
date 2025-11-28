@props([
    'title' => '',
    'totalAmount' => 0,
    'investmentCount' => 0,
    'distributedAmount' => 0,
    'color' => 'primary',
    'icon' => 'ki-chart-simple',
    'class' => 'col-xl-4',
    'height' => 'h-md-50'
])

<div class="{{ $class }}">
    <div class="card card-flush {{ $height }} mb-5 mb-xl-10">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <div class="d-flex align-items-center">
                    @if($icon)
                        <i class="ki-duotone {{ $icon }} fs-2x text-{{ $color }} me-3">
                            @foreach(explode(' ', $icon) as $path)
                                <span class="path{{ $loop->iteration }}"></span>
                            @endforeach
                        </i>
                    @endif
                    <span class="fs-4 fw-semibold text-gray-900 me-1">{{ $title }}</span>
                </div>
            </div>
        </div>
        <div class="card-body pt-2 pb-4 d-flex flex-column">
            <!-- Main Amount Display -->
            <div class="d-flex flex-center mb-4">
                <div class="symbol symbol-75px symbol-circle">
                    <span class="symbol-label fs-2x fw-bold text-{{ $color }} bg-light-{{ $color }}">
                        ${{ number_format($totalAmount, 0) }}
                    </span>
                </div>
            </div>

            <!-- Investment Details -->
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fs-7 text-gray-600">عدد الاستثمارات</span>
                    <span class="fs-7 fw-bold text-gray-900">{{ number_format($investmentCount) }}</span>
                </div>

                @if($distributedAmount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-7 text-gray-600">المبلغ الموزع</span>
                        <span class="fs-7 fw-bold text-success">${{ number_format($distributedAmount, 0) }}</span>
                    </div>
                @endif

                @if($investmentCount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-7 text-gray-600">متوسط الاستثمار</span>
                        <span class="fs-7 fw-bold text-{{ $color }}">${{ number_format($totalAmount / $investmentCount, 0) }}</span>
                    </div>
                @endif
            </div>

            <!-- Progress Bar for Distribution -->
            @if($totalAmount > 0 && $distributedAmount >= 0)
                @php
                    $distributionPercentage = ($distributedAmount / $totalAmount) * 100;
                @endphp
                <div class="d-flex flex-column mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-7 text-gray-600">نسبة التوزيع</span>
                        <span class="fs-7 fw-bold text-info">{{ number_format($distributionPercentage, 1) }}%</span>
                    </div>
                    <div class="progress w-100" style="height: 8px;">
                        <div class="progress-bar bg-info rounded"
                             style="width: {{ $distributionPercentage }}%"
                             role="progressbar"
                             aria-valuenow="{{ $distributionPercentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>



