{{--
    Performance Chart Widget Component

    A reusable component for displaying performance charts with date range filtering.

    Usage Examples:

    1. Basic usage with 3 series (Investment Performance):
    <x-performance-chart-widget
        :investment-performance="$investmentPerformance"
        :api-url="route('admin.dashboard.investment-performance')"
        :series="[
            ['key' => 'expected_profit', 'name' => 'Expected Profit (SAR)', 'color' => 'primary', 'scale' => 1, 'unit' => 'SAR'],
            ['key' => 'actual_profit', 'name' => 'Actual Profit (SAR)', 'color' => 'success', 'scale' => 1, 'unit' => 'SAR'],
            ['key' => 'short_investments', 'name' => 'Short Investments (Count)', 'color' => 'warning', 'scale' => 100, 'unit' => 'Investments']
        ]"
    />

    2. Custom title and subtitle:
    <x-performance-chart-widget
        :investment-performance="$data"
        :api-url="route('api.custom-performance')"
        title="Sales Performance"
        subtitle="Total sales over time"
        subtitle-format="custom"
    />

    3. Smaller chart without date picker:
    <x-performance-chart-widget
        :investment-performance="$data"
        :api-url="route('api.performance')"
        chart-id="my_custom_chart"
        chart-height="200"
        col-size="6"
        :show-date-picker="false"
        title="Quick Stats"
    />

    4. Dynamic series (custom number):
    <x-performance-chart-widget
        :investment-performance="$data"
        :api-url="route('api.performance')"
        :series="[
            [
                'key' => 'expected_profit',
                'name' => 'Expected Profit (SAR)',
                'color' => 'primary',
                'scale' => 1
            ],
            [
                'key' => 'actual_profit',
                'name' => 'Actual Profit (SAR)',
                'color' => 'success',
                'scale' => 1
            ]
        ]"
    />

    5. Multiple series (5 series example):
    <x-performance-chart-widget
        :investment-performance="$data"
        :api-url="route('api.metrics')"
        :series="[
            ['key' => 'sales', 'name' => 'Sales', 'color' => 'primary', 'scale' => 1, 'unit' => 'SAR'],
            ['key' => 'revenue', 'name' => 'Revenue', 'color' => 'success', 'scale' => 1, 'unit' => 'SAR'],
            ['key' => 'costs', 'name' => 'Costs', 'color' => 'danger', 'scale' => 1, 'unit' => 'SAR'],
            ['key' => 'profit', 'name' => 'Profit', 'color' => 'warning', 'scale' => 1, 'unit' => 'SAR'],
            ['key' => 'orders', 'name' => 'Orders', 'color' => 'info', 'scale' => 1, 'unit' => '']
        ]"
    />

    6. Single series:
    <x-performance-chart-widget
        :investment-performance="$data"
        :api-url="route('api.sales')"
        title="Sales Over Time"
        :series="[
            ['key' => 'total_sales', 'name' => 'Total Sales', 'color' => 'primary', 'scale' => 1, 'unit' => 'SAR']
        ]"
    />

    7. Two series:
    <x-performance-chart-widget
        :investment-performance="$data"
        :api-url="route('api.profit')"
        :series="[
            ['key' => 'expected_profit', 'name' => 'Expected', 'color' => 'primary'],
            ['key' => 'actual_profit', 'name' => 'Actual', 'color' => 'success']
        ]"
    />

    @param array|null $investmentPerformance - Initial chart data
    @param string|null $apiUrl - API endpoint for fetching filtered data
    @param string $chartId - Unique ID for the chart element (default: 'kt_charts_widget_36')
    @param string $title - Chart title (default: 'Performance')
    @param string|null $subtitle - Chart subtitle (auto-generated if null and subtitleFormat='investment')
    @param string $subtitleFormat - Format: 'investment' or 'custom' (default: 'investment')
    @param int $chartHeight - Chart height in pixels (default: 300)
    @param int $colSize - Bootstrap column size 1-12 (default: 12)
    @param string|null $dateRangePickerId - Unique ID for date picker (auto-generated if null)
    @param bool $showDatePicker - Show/hide date range picker (default: true)
    @param string $defaultDateRangeText - Default text for date picker (default: 'Last 12 Months')
    @param array $series - REQUIRED: Dynamic series configuration array
        Each series item: ['key' => 'data_key', 'name' => 'Display Name', 'color' => 'primary', 'scale' => 1, 'unit' => 'SAR']
    @param string $containerClass - CSS classes for container row
    @param string $cardClass - CSS classes for card element
--}}
@props([
    'investmentPerformance' => null,
    'apiUrl' => null,
    'chartId' => 'kt_charts_widget_36',
    'title' => 'Performance',
    'subtitle' => null,
    'subtitleFormat' => 'investment', // 'investment' or 'custom'
    'chartHeight' => 300,
    'colSize' => 12, // Bootstrap column size (1-12)
    'dateRangePickerId' => null, // Unique ID for date range picker (auto-generated if null)
    'showDatePicker' => true,
    'defaultDateRangeText' => 'Last 12 Months',
    'series' => [], // REQUIRED: Dynamic series array configuration
    'containerClass' => 'row g-5 g-xl-8 mb-5 mb-xl-10',
    'cardClass' => 'card card-flush overflow-hidden h-lg-100'
])

@php
    // Generate unique IDs if not provided
    $dateRangePickerId = $dateRangePickerId ?: 'daterangepicker_' . $chartId;
    $subtitleId = 'subtitle_' . $chartId;

    // Generate subtitle if not provided
    if (!$subtitle && $subtitleFormat === 'investment' && $investmentPerformance) {
        $subtitle = number_format($investmentPerformance['total_short_investments']) .
                    ' Short Investments with ' .
                    number_format($investmentPerformance['total_expected_profit'], 2) .
                    ' SAR Expected Profit';
    } elseif (!$subtitle) {
        $subtitle = 'Loading...';
    }

    // Validate series is provided and is an array
    if (empty($series) || !is_array($series)) {
        throw new \InvalidArgumentException('Series parameter is required and must be a non-empty array for performance-chart-widget component');
    }

    // Ensure each series has required keys
    foreach ($series as $index => $seriesItem) {
        if (!isset($seriesItem['key']) || !isset($seriesItem['name'])) {
            throw new \InvalidArgumentException("Series item at index {$index} must have 'key' and 'name' properties");
        }
        // Set defaults for optional properties
        $series[$index]['color'] = $series[$index]['color'] ?? 'primary';
        $series[$index]['scale'] = $series[$index]['scale'] ?? 1;
        $series[$index]['unit'] = $series[$index]['unit'] ?? '';
    }
@endphp

<!--begin::Row - Performance Chart-->
<div class="{{ $containerClass }}">
    <!--begin::Col-->
    <div class="col-xl-{{ $colSize }}">
        <!--begin::Chart widget-->
        <div class="{{ $cardClass }}">
            <!--begin::Header-->
            <div class="card-header pt-5">
                <!--begin::Title-->
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">{{ $title }}</span>
                    <span id="{{ $subtitleId }}" class="text-gray-500 mt-1 fw-semibold fs-6 performance-subtitle">
                        {{ $subtitle }}
                    </span>
                </h3>
                <!--end::Title-->
                <!--begin::Toolbar-->
                @if($showDatePicker)
                <div class="card-toolbar">
                    <!--begin::Daterangepicker-->
                    <div id="{{ $dateRangePickerId }}"
                         data-kt-daterangepicker="true"
                         data-kt-daterangepicker-opens="left"
                         data-kt-daterangepicker-range="today"
                         class="btn btn-sm btn-light d-flex align-items-center px-4">
                        <!--begin::Display range-->
                        <div class="text-gray-600 fw-bold">{{ $defaultDateRangeText }}</div>
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
                    <!--end::Daterangepicker-->
                </div>
                <!--end::Toolbar-->
                @endif
            </div>
            <!--end::Header-->
            <!--begin::Card body-->
            <div class="card-body d-flex align-items-end p-0">
                <!--begin::Chart-->
                <div id="{{ $chartId }}" class="min-h-auto w-100 ps-4 pe-6" style="height: {{ $chartHeight }}px"></div>
                <!--end::Chart-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Chart widget 36-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

@push('scripts')
<script>
    (function() {
        // Check if jQuery is already loaded
        if (typeof window.jQuery === "undefined") {
            // Not loaded → load from CDN
            var script = document.createElement("script");
            script.src = "https://code.jquery.com/jquery-3.7.1.min.js";
            script.integrity = "sha256-WpOohJOq7NzL2QbXr3RrP6XGk6Zr3I9l8a4H2yPBBhU=";
            script.crossOrigin = "anonymous";
            document.head.appendChild(script);
        }

        // Check if ApexCharts is already loaded
        if (typeof ApexCharts === "undefined") {
            var apexScript = document.createElement("script");
            apexScript.src = "https://cdn.jsdelivr.net/npm/apexcharts";
            document.head.appendChild(apexScript);
        }
    })();
</script>
<script src="{{ asset('assets/js/custom/pages/dashboard/performance-chart.js') }}"></script>
<script>
    // Initialize Performance Chart Widget
    (function() {
        // Wait for dependencies to be loaded
        if (typeof KTChartsWidget36 === 'undefined') {
            console.error('Performance chart widget script not loaded');
            return;
        }

        // Configuration
        var config = {
            chartId: '{{ $chartId }}',
            apiUrl: @json($apiUrl ?: route('admin.dashboard.investment-performance')),
            initialData: @json($investmentPerformance ?: []),
            dateRangePickerId: '{{ $dateRangePickerId }}',
            subtitleId: '{{ $subtitleId }}',
            chartHeight: {{ $chartHeight }},
            showDatePicker: {{ $showDatePicker ? 'true' : 'false' }},
            defaultDateRangeText: @json($defaultDateRangeText),
            series: @json($series),
            subtitleFormat: @json($subtitleFormat)
        };

        // Initialize on document ready
        KTUtil.onDOMContentLoaded(function() {
            KTChartsWidget36.init(config);
        });
    })();
</script>
@endpush

