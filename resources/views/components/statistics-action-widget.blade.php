@props([
    'title' => 'Investment Statistics',
    'subtitle' => 'Real-time investment data',
    'chartColor' => 'primary',
    'buttonColor' => 'primary',
    'buttonText' => 'View Details',
    'buttonUrl' => '#',
    'note' => 'Current investment performance metrics',
    'noteBadge' => 'Live',
    'noteBadgeColor' => 'success',
    'class' => 'col-xl-4',
    'showFilter' => true,
    'customFilters' => [],
    'chartValue' => null
])

@php
    $defaultFilters = [
        'status' => [
            'label' => 'Investment Status:',
            'options' => [
                'Active',
                'Pending',
                'Completed',
                'Cancelled'
            ]
        ],
        'memberType' => [
            'label' => 'Investment Type:',
            'options' => [
                'Myself' => false,
                'Authorize' => true
            ]
        ],
        'notifications' => [
            'label' => 'Alerts:',
            'enabled' => true
        ]
    ];

    $filterOptions = !empty($customFilters) && count($customFilters) > 0 ? $customFilters : $defaultFilters;

    // Generate unique ID for this widget instance
    $uniqueId = 'widget_' . uniqid();

    // If chartValue is not provided, generate a random value between 60-100
    $chartValue = $chartValue ?? rand(60, 100);
@endphp

<div class="{{ $class }}">
    <!--begin::Mixed Widget-->
    <div class="card card-xl-stretch mb-xl-8">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">{{ $title }}</span>
                <span class="text-muted fw-semibold fs-7">{{ $subtitle }}</span>
            </h3>

            @if($showFilter && !empty($filterOptions) && count($filterOptions) > 0)
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary"
                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-category fs-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </button>

                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true">
                        <!--begin::Header-->
                        <div class="px-7 py-5">
                            <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                        </div>
                        <!--end::Header-->

                        <!--begin::Menu separator-->
                        <div class="separator border-gray-200"></div>
                        <!--end::Menu separator-->

                        <!--begin::Form-->
                        <div class="px-7 py-5">
                            @if(isset($filterOptions['status']))
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold">{{ $filterOptions['status']['label'] }}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div>
                                        <select class="form-select form-select-solid" multiple="multiple"
                                                data-kt-select2="true" data-close-on-select="false"
                                                data-placeholder="Select option" data-allow-clear="true">
                                            <option></option>
                                            @foreach($filterOptions['status']['options'] as $option)
                                                <option value="{{ $loop->iteration }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                            @endif

                            @if(isset($filterOptions['memberType']))
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold">{{ $filterOptions['memberType']['label'] }}</label>
                                    <!--end::Label-->
                                    <!--begin::Options-->
                                    <div class="d-flex">
                                        @foreach($filterOptions['memberType']['options'] as $label => $checked)
                                            <label class="form-check form-check-sm form-check-custom form-check-solid {{ !$loop->last ? 'me-5' : '' }}">
                                                <input class="form-check-input" type="checkbox" value="{{ $loop->iteration }}"
                                                       {{ $checked ? 'checked="checked"' : '' }} />
                                                <span class="form-check-label">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <!--end::Options-->
                                </div>
                                <!--end::Input group-->
                            @endif

                            @if(isset($filterOptions['notifications']))
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold">{{ $filterOptions['notifications']['label'] }}</label>
                                    <!--end::Label-->
                                    <!--begin::Switch-->
                                    <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="" name="notifications"
                                               {{ $filterOptions['notifications']['enabled'] ? 'checked="checked"' : '' }} />
                                        <label class="form-check-label">Enabled</label>
                                    </div>
                                    <!--end::Switch-->
                                </div>
                                <!--end::Input group-->
                            @endif

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2"
                                        data-kt-menu-dismiss="true">Reset</button>
                                <button type="submit" class="btn btn-sm btn-primary"
                                        data-kt-menu-dismiss="true">Apply</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Form-->
                    </div>
                    <!--end::Menu-->
                </div>
            @endif
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column">
            <div class="flex-grow-1">
                <div id="{{ $uniqueId }}" class="statistics-action-widget-chart" style="height: 200px"></div>
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
    <!--end::Mixed Widget-->
</div>

<script>
(function() {
    'use strict';

    // Initialize the chart when DOM is ready
    function initChart() {
        const element = document.getElementById('{{ $uniqueId }}');
        if (!element) return;

        const chartValue = {{ $chartValue }};
        const chartColor = '{{ $chartColor }}';

        // Create the chart HTML
        element.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <svg width="120" height="120" class="progress-ring">
                            <circle cx="60" cy="60" r="50"
                                    fill="none"
                                    stroke="#f3f6f9"
                                    stroke-width="8"/>
                            <circle cx="60" cy="60" r="50"
                                    fill="none"
                                    stroke="var(--bs-${chartColor})"
                                    stroke-width="8"
                                    stroke-dasharray="${2 * 3.14159 * 50}"
                                    stroke-dashoffset="${2 * 3.14159 * 50 * (1 - chartValue / 100)}"
                                    stroke-linecap="round"
                                    transform="rotate(-90 60 60)"
                                    style="transition: stroke-dashoffset 0.5s ease-in-out;"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <span class="fs-2x fw-bold text-${chartColor}">${chartValue}%</span>
                        </div>
                    </div>
                    <div class="fs-6 text-gray-600">Completion Rate</div>
                    <div class="progress mt-3" style="width: 200px; margin: 0 auto;">
                        <div class="progress-bar bg-${chartColor}"
                             style="width: ${chartValue}%"
                             role="progressbar"
                             aria-valuenow="${chartValue}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Run when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        // DOM is already loaded
        initChart();
    }
})();
</script>
