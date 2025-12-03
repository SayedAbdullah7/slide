@props([
    'icon' => 'ki-dollar',
    'iconPaths' => 3,
    'color' => 'success',
    'title' => 'Financial Metric',
    'subtitle' => 'Description',
    'value' => '0.00',
    'badge' => null,
    'isLast' => false,
    'symbolSize' => '40px'
])

<div class="d-flex flex-stack {{ $isLast ? 'mb-0' : 'mb-7' }}">
    <div class="d-flex align-items-center me-5">
        <div class="symbol symbol-{{ $symbolSize }} me-4">
            <span class="symbol-label bg-light-{{ $color }}">
                <i class="ki-duotone {{ $icon }} fs-2 text-{{ $color }}">
                    @for($i = 1; $i <= $iconPaths; $i++)
                        <span class="path{{ $i }}"></span>
                    @endfor
                </i>
            </span>
        </div>
        <div class="flex-grow-1">
            <span class="text-gray-800 fw-bold d-block fs-6">{{ $title }}</span>
            <span class="text-gray-400 fw-semibold fs-7">{{ $subtitle }}</span>
        </div>
    </div>
    <div class="text-end">
        {{ $slot }}
        @if($badge)
            {!! $badge !!}
        @endif
    </div>
</div>

