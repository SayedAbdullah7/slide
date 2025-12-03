@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'iconColor' => 'primary',
    'progress' => 0,
    'progressColor' => 'primary',
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
        <div class="card-body pt-2 pb-4 d-flex flex-column">
            <!-- Main Value Display -->
            <div class="d-flex flex-center mb-3">
                <div class="symbol symbol-75px symbol-circle">
                    <span class="symbol-label fs-2x fw-bold text-{{ $iconColor }} bg-light-{{ $iconColor }}">
                        {{ $value }}
                    </span>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="d-flex flex-column">
                @if($subtitle)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-7 text-gray-600">{{ $subtitle }}</span>
                        <span class="fs-7 fw-bold text-{{ $progressColor }}">{{ number_format($progress, 1) }}%</span>
                    </div>
                @endif

                <div class="progress w-100 mb-2" style="height: 8px;">
                    <div class="progress-bar bg-{{ $progressColor }} rounded"
                         style="width: {{ $progress }}%"
                         role="progressbar"
                         aria-valuenow="{{ $progress }}"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>

                @if($subtitle)
                    <div class="text-center">
                        <span class="fs-8 text-gray-500">{{ $subtitle }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
