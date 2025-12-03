@props([
    'title' => '',
    'value' => '',
    'subtitle' => '',
    'badges' => [],
    'color' => 'primary',
    'icon' => 'ki-chart-simple',
    'progress' => null,
    'progressColor' => 'success',
    'class' => 'col-xl-6',
    // 'height' => '',
    'height' => 'h-md-80'
])

<div class="{{ $class }}">
    <div class="card card-flush {{ $height }} mb-5 mb-xl-10" style="min-height: 220px;">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <div class="d-flex align-items-center">
                    @if($icon)
                        <span class="me-3 d-flex align-items-center" style="min-width: 40px; min-height: 40px;">
                            <i class="ki-duotone {{ $icon }} fs-2x text-{{ $color }}">
                                @for($i = 1; $i <= 4; $i++)
                                    <span class="path{{ $i }}"></span>
                                @endfor
                            </i>
                        </span>
                    @endif
                    <span class="fs-4 fw-semibold text-gray-900 me-1">{{ $title }}</span>
                </div>
                @if($subtitle)
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $subtitle }}</span>
                @endif
            </div>
        </div>
        <div class="card-body pt-2 pb-4 d-flex flex-column justify-content-center">
            <!-- Main Value Display -->
            <div class="d-flex flex-center mb-3">
                <div class="symbol symbol-75px symbol-circle">
                    <span class="symbol-label fs-2x fw-bold text-{{ $color }} bg-light-{{ $color }}">
                        {{ $value }}
                    </span>
                </div>
            </div>

            <!-- Badges Section -->
            @if(!empty($badges))
                <div class="d-flex flex-row flex-wrap gap-2 mb-3 justify-content-center align-items-center">
                    @foreach($badges as $badge)
                        <span class="badge fs-7 fw-bold bg-light-{{ $badge['color'] ?? 'primary' }} text-{{ $badge['color'] ?? 'primary' }} px-3 py-2">
                            {{ $badge['label'] ?? $badge['text'] ?? '' }}
                            @if(isset($badge['value']))
                                : <span class="fw-bold">{{ $badge['value'] }}</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif

            <!-- Progress Bar -->
            @if($progress !== null)
                <div class="d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-7 text-gray-600">التقدم</span>
                        <span class="fs-7 fw-bold text-{{ $progressColor }}">{{ number_format($progress, 1) }}%</span>
                    </div>
                    <div class="progress w-100" style="height: 8px;">
                        <div class="progress-bar bg-{{ $progressColor }} rounded"
                             style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
