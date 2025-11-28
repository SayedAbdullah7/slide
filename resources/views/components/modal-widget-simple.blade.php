@props([
    'title' => 'Action Needed',
    'subtitle' => 'Complete your profile setup',
    'progressValue' => 74,
    'buttonColor' => 'primary',
    'buttonText' => 'Take Action',
    'buttonUrl' => '#',
    'note' => 'Current sprint requires stakeholders to approve newly amended policies',
    'noteBadge' => 'Notes',
    'noteBadgeColor' => 'danger',
    'showFilter' => false,
    'widgetId' => null
])

@php
    $widgetId = $widgetId ?: 'modal-widget-' . uniqid();
@endphp

<div id="{{ $widgetId }}" class="modal-widget-simple">
    <div class="card card-xl-stretch mb-xl-8">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">{{ $title }}</span>
                <span class="text-muted fw-semibold fs-7">{{ $subtitle }}</span>
            </h3>

            @if($showFilter)
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary"
                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-category fs-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </button>
                </div>
            @endif
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column">
            <div class="flex-grow-1">
                <!-- Simple Progress Chart -->
                <div class="d-flex align-items-center justify-content-center" style="height: 200px;">
                    <div class="text-center">
                        <!-- Progress Circle -->
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" class="progress-ring">
                                <circle cx="60" cy="60" r="50"
                                        fill="none"
                                        stroke="#f3f6f9"
                                        stroke-width="8"/>
                                <circle cx="60" cy="60" r="50"
                                        fill="none"
                                        stroke="var(--bs-{{ $buttonColor }})"
                                        stroke-width="8"
                                        stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                        stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $progressValue / 100) }}"
                                        stroke-linecap="round"
                                        transform="rotate(-90 60 60)"
                                        style="transition: stroke-dashoffset 0.5s ease-in-out;"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <span class="fs-2x fw-bold text-{{ $buttonColor }}">{{ $progressValue }}%</span>
                            </div>
                        </div>

                        <!-- Progress Label -->
                        <div class="fs-6 text-gray-600 mt-3">Completion Rate</div>

                        <!-- Progress Bar -->
                        <div class="progress mt-3" style="width: 200px; margin: 0 auto;">
                            <div class="progress-bar bg-{{ $buttonColor }}"
                                 style="width: {{ $progressValue }}%"
                                 role="progressbar"
                                 aria-valuenow="{{ $progressValue }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5">
                @if($note)
                    <p class="text-center fs-6 pb-5">
                        <span class="badge badge-light-{{ $noteBadgeColor }} fs-8">{{ $noteBadge }}:</span>&nbsp; {{ $note }}
                    </p>
                @endif
                <a href="{{ $buttonUrl }}" class="btn btn-{{ $buttonColor }} w-100 py-3">{{ $buttonText }}</a>
            </div>
        </div>
        <!--end::Body-->
    </div>
</div>

<style>
.progress-ring circle {
    transition: stroke-dashoffset 0.5s ease-in-out;
}
</style>


