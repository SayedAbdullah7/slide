@props([
    'value' => '0',
    'label' => 'Label',
    'icon' => 'ki-chart-simple',
    'iconPaths' => 2,
    'color' => 'primary',
    'class' => 'col-xl-3 col-lg-6 col-md-6 col-sm-6'
])

<div class="{{ $class }}">
    <div class="card card-xl-stretch mb-xl-8">
        <div class="card-body d-flex flex-column p-0">
            <div class="d-flex flex-stack flex-grow-1 card-p">
                <div class="d-flex flex-column me-2">
                    <span class="text-gray-800 text-hover-primary fw-bold fs-3">{{ $value }}</span>
                    <span class="text-muted fw-semibold mt-1 fs-7">{{ $label }}</span>
                </div>
                <div class="symbol symbol-50px">
                    <span class="symbol-label bg-light-{{ $color }}">
                        <i class="ki-duotone {{ $icon }} fs-2x text-{{ $color }}">
                            @for($i = 1; $i <= $iconPaths; $i++)
                                <span class="path{{ $i }}"></span>
                            @endfor
                        </i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

