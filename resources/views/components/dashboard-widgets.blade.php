@props([
    'opportunity' => null,
    'showInvestmentWidgets' => true,
    'showStatisticsWidgets' => true,
    'class' => ''
])

<div class="{{ $class }}">
    @if($showInvestmentWidgets && $opportunity)
        <!-- Investment Management Widgets -->
        <div class="row g-5 g-xl-8 mb-7">
            <x-investment-action-widget
                type="merchandise"
                :opportunity="$opportunity"
                class="col-xl-4"
            />

            <x-investment-action-widget
                type="returns"
                :opportunity="$opportunity"
                class="col-xl-4"
            />

            <x-investment-action-widget
                type="distribution"
                :opportunity="$opportunity"
                class="col-xl-4"
            />
        </div>
    @endif

    @if($showStatisticsWidgets)
        <!-- Statistics Widgets -->
        <div class="row g-5 g-xl-8 mb-7">
            <x-statistics-action-widget
                title="إحصائيات النظام"
                subtitle="مراقبة أداء النظام العام"
                chartColor="primary"
                buttonColor="primary"
                buttonText="عرض التفاصيل"
                buttonUrl="#"
                note="مراقبة شاملة لأداء النظام والتنبيهات"
                noteBadge="نظام"
                noteBadgeColor="primary"
                class="col-xl-4"
            />

            <x-statistics-action-widget
                title="تقرير المبيعات"
                subtitle="تحليل أداء المبيعات والإيرادات"
                chartColor="success"
                buttonColor="success"
                buttonText="تقرير المبيعات"
                buttonUrl="#"
                note="تحليل شامل لأداء المبيعات والإيرادات المحققة"
                noteBadge="مبيعات"
                noteBadgeColor="success"
                class="col-xl-4"
            />

            <x-statistics-action-widget
                title="إدارة العملاء"
                subtitle="إدارة قاعدة بيانات العملاء"
                chartColor="info"
                buttonColor="info"
                buttonText="إدارة العملاء"
                buttonUrl="#"
                note="إدارة شاملة لقاعدة بيانات العملاء والمستثمرين"
                noteBadge="عملاء"
                noteBadgeColor="info"
                class="col-xl-4"
            />
        </div>
    @endif
</div>


