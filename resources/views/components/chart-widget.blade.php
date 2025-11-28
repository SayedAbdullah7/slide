@props([
    'title',
    'subtitle' => null,
    'chartId',
    'chartHeight' => 'h-325px',
    'height' => 'h-xl-50',
    'showDateRange' => false,
    'dateRangeName' => 'date_range',
    'dateRangeValue' => null,
    'class' => ''
])

<div class="card card-flush {{ $height }} {{ $class }}">
    <!--begin::Header-->
    <div class="card-header pt-7">
        <!--begin::Title-->
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-800">{{ $title }}</span>
            @if($subtitle)
                <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $subtitle }}</span>
            @endif
        </h3>
        <!--end::Title-->
        <!--begin::Toolbar-->
        @if($showDateRange)
            <div class="card-toolbar">
                <x-date-range-picker
                    :name="$dateRangeName"
                    :value="$dateRangeValue"
                    placeholder="Select date range"
                />
            </div>
        @endif
        <!--end::Toolbar-->
    </div>
    <!--end::Header-->
    <!--begin::Body-->
    <div class="card-body d-flex align-items-end px-0 pt-3 pb-5">
        <!--begin::Chart-->
        <div id="{{ $chartId }}" class="{{ $chartHeight }} w-100 min-h-auto ps-4 pe-6"></div>
        <!--end::Chart-->
    </div>
    <!--end: Card Body-->
</div>


