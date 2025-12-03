@props([
    'value' => '0',
    'label' => 'Label',
    'subtitle' => null,
    'bgColor' => '#F1416C',
    'showProgress' => false,
    'progress' => 0,
    'class' => 'col-xl-3 col-lg-6 col-md-6 col-sm-6'
])

<div class="{{ $class }}">
    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100"
         style="background-color: {{ $bgColor }};background-image:url('assets/media/patterns/vector-1.png')">
        <div class="card-header pt-5">
            <div class="card-title d-flex flex-column">
                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $value }}</span>
                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ $label }}</span>
            </div>
        </div>
        <div class="card-body d-flex align-items-end pt-0">
            <div class="d-flex align-items-center flex-column mt-3 w-100">
                <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                    <span>{{ $subtitle ?? '' }}</span>
                </div>
                @if($showProgress)
                    <div class="progress h-6px w-100 bg-white bg-opacity-50">
                        <div class="progress-bar bg-white"
                             role="progressbar"
                             style="width: {{ min($progress, 100) }}%"
                             aria-valuenow="{{ $progress }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

