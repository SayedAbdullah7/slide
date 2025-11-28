@props([
    'icon' => 'ki-chart-simple',
    'iconPaths' => 2,
    'color' => 'primary',
    'title' => 'Metric Title',
    'subtitle' => 'Metric description',
    'value' => '0',
    'link' => '#',
    'badge' => null,
    'isLast' => false,
    'symbolSize' => '40px',
    'iconSize' => '2'
])

<div class="d-flex flex-stack {{ $isLast ? 'mb-0' : 'mb-7' }}">
    <div class="d-flex align-items-center me-5">
        <div class="symbol symbol-{{ $symbolSize }} me-4">
            <span class="symbol-label bg-light-{{ $color }}">
                <i class="ki-duotone {{ $icon }} fs-{{ $iconSize }} text-{{ $color }}">
                    @for($i = 1; $i <= $iconPaths; $i++)
                        <span class="path{{ $i }}"></span>
                    @endfor
                </i>
            </span>
        </div>
        <div class="flex-grow-1">
            @if($link && $link !== '#')
                <a href="{{ $link }}" class="text-gray-800 text-hover-primary fw-bold d-block fs-6">{{ $title }}</a>
            @else
                <span class="text-gray-800 fw-bold d-block fs-6">{{ $title }}</span>
            @endif
            <span class="text-gray-400 fw-semibold fs-7">{{ $subtitle }}</span>
        </div>
    </div>
    <div class="text-end">
        @if($badge)
            {!! $badge !!}
        @else
            <span class="text-gray-800 fw-bold fs-4 d-block">{{ $value }}</span>
        @endif
    </div>
</div>

