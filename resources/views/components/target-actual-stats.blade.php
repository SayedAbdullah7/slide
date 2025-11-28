@props([
    'targetValue',
    'targetLabel' => 'Target',
    'actualValue',
    'actualLabel' => 'Actual',
    'gapValue' => null,
    'gapLabel' => 'Gap',
    'currency' => '$',
    'height' => 'h-xl-50',
    'class' => ''
])

<div class="card card-flush {{ $height }} {{ $class }}">
    <!--begin::Header-->
    <div class="card-header py-5">
        <!--begin::Title-->
        <h3 class="card-title fw-bold text-gray-800">Target vs Actual</h3>
        <!--end::Title-->
    </div>
    <!--end::Header-->
    <!--begin::Card body-->
    <div class="card-body d-flex justify-content-between flex-column pb-0 px-0 pt-1">
        <!--begin::Items-->
        <div class="d-flex flex-wrap d-grid gap-5 px-9 mb-5">
            <!--begin::Target-->
            <div class="me-md-2">
                <!--begin::Statistics-->
                <div class="d-flex mb-2">
                    <span class="fs-4 fw-semibold text-gray-500 me-1">{{ $currency }}</span>
                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ number_format($targetValue, 0) }}</span>
                </div>
                <!--end::Statistics-->
                <!--begin::Description-->
                <span class="fs-6 fw-semibold text-gray-500">{{ $targetLabel }}</span>
                <!--end::Description-->
            </div>
            <!--end::Target-->

            <!--begin::Actual-->
            <div class="border-start-dashed border-end-dashed border-start border-end border-gray-300 px-5 ps-md-10 pe-md-7 me-md-5">
                <!--begin::Statistics-->
                <div class="d-flex mb-2">
                    <span class="fs-4 fw-semibold text-gray-500 me-1">{{ $currency }}</span>
                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ number_format($actualValue, 0) }}</span>
                </div>
                <!--end::Statistics-->
                <!--begin::Description-->
                <span class="fs-6 fw-semibold text-gray-500">{{ $actualLabel }}</span>
                <!--end::Description-->
            </div>
            <!--end::Actual-->

            @if($gapValue !== null)
                <!--begin::Gap-->
                <div class="d-flex align-items-center">
                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mb-2">
                        <span class="fs-4 fw-semibold text-gray-500 align-self-start me-1">{{ $currency }}</span>
                        <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">{{ number_format($gapValue, 0) }}</span>
                        @if($gapValue > 0)
                            <span class="badge badge-light-success">
                                <i class="ki-duotone ki-arrow-up fs-7">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                        @elseif($gapValue < 0)
                            <span class="badge badge-light-danger">
                                <i class="ki-duotone ki-arrow-down fs-7">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                        @endif
                    </div>
                    <!--end::Statistics-->
                    <!--begin::Description-->
                    <span class="fs-6 fw-semibold text-gray-500">{{ $gapLabel }}</span>
                    <!--end::Description-->
                </div>
                <!--end::Gap-->
            @endif
        </div>
        <!--end::Items-->
    </div>
    <!--end::Card body-->
</div>



