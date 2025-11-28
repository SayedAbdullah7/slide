@props([
    'name' => 'date_range',
    'placeholder' => 'Select date range',
    'value' => null,
    'opens' => 'left',
    'class' => 'btn btn-sm btn-light d-flex align-items-center px-4'
])

<div data-kt-daterangepicker="true" data-kt-daterangepicker-opens="{{ $opens }}" class="{{ $class }}">
    <!--begin::Display range-->
    <div class="text-gray-600 fw-bold">
        @if($value)
            {{ $value }}
        @else
            {{ $placeholder }}
        @endif
    </div>
    <!--end::Display range-->
    <i class="ki-duotone ki-calendar-8 text-gray-500 lh-0 fs-2 ms-2 me-0">
        <span class="path1"></span>
        <span class="path2"></span>
        <span class="path3"></span>
        <span class="path4"></span>
        <span class="path5"></span>
        <span class="path6"></span>
    </i>
</div>

<input type="hidden" name="{{ $name }}" value="{{ $value }}">


