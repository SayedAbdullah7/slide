@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'iconColor' => 'primary',
    'valueColor' => 'gray-800',
    'currency' => '$',
    'trend' => null,
    'trendValue' => null,
    'trendColor' => 'success',
    'height' => 'h-md-50',
    'class' => ''
])

<div class="{{ $class ?: 'col-xl-4' }}">
    <div class="card card-flush {{ $height }} mb-5 mb-xl-10">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <div class="d-flex align-items-center">
                    @if($icon)
                        <i class="ki-duotone {{ $icon }} fs-2x text-{{ $iconColor }} me-3">
                            @foreach(explode(' ', $icon) as $path)
                                <span class="path{{ $loop->iteration }}"></span>
                            @endforeach
                        </i>
                    @endif
                    <span class="fs-4 fw-semibold text-gray-900 me-1">{{ $title }}</span>
                </div>
                @if($subtitle)
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $subtitle }}</span>
                @endif
            </div>
        </div>
        <div class="card-body pt-2 pb-4 d-flex flex-wrap align-items-center">
            <div class="d-flex flex-center me-5 pt-2">
                <div class="symbol symbol-65px symbol-circle">
                    <span class="symbol-label fs-2x fw-semibold text-{{ $valueColor }} bg-light-{{ $iconColor }}">
                        {{ $currency }}{{ number_format((float)$value, 0) }}
                    </span>
                </div>
            </div>
            <div class="d-flex flex-column content-justify-center flex-row-fluid">
                <div class="d-flex fw-semibold align-items-center text-gray-900 mb-2">
                    <span class="fs-6">{{ $title }}</span>
                    @if($trend && $trendValue)
                        <span class="badge badge-light-{{ $trendColor }} ms-2">
                            <i class="ki-duotone ki-{{ $trend === 'up' ? 'arrow-up' : 'arrow-down' }} fs-7">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            {{ $trendValue }}%
                        </span>
                    @endif
                </div>
                <div class="d-flex align-items-center">
                    <span class="fs-7 text-gray-600">{{ $subtitle ?? 'Current Value' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
