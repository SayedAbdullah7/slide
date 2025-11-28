@php
    $opportunity = $opportunity ?? \App\Models\InvestmentOpportunity::first();
@endphp

<div class="row g-5 g-xl-8">
    <div class="col-12">
        <x-mixed-widget
            title="معدل إكمال الفرصة الاستثمارية"
            description="تتبع تقدم جمع الاستثمارات لهذه الفرصة الاستثمارية"
            button-text="{{ $opportunity && $opportunity->is_completed ? 'مكتملة' : ($opportunity && $opportunity->is_fundable ? 'قابلة للتمويل' : 'غير قابلة للتمويل') }}"
            button-class="{{ $opportunity && $opportunity->is_completed ? 'btn-success' : ($opportunity && $opportunity->is_fundable ? 'btn-primary' : 'btn-secondary') }}"
            button-action="#"
            :chart-height="300"
            chart-color="{{ $opportunity && $opportunity->completion_rate >= 100 ? 'success' : ($opportunity && $opportunity->completion_rate >= 75 ? 'warning' : 'danger') }}"
            :legend-items="[
                ['color' => $opportunity && $opportunity->completion_rate >= 100 ? 'success' : ($opportunity && $opportunity->completion_rate >= 75 ? 'warning' : 'danger'), 'label' => 'مكتمل: ' . number_format($opportunity->completion_rate ?? 0, 1) . '%'],
                ['color' => 'info', 'label' => 'المستهدف: $' . number_format($opportunity->target_amount ?? 0, 0)],
                ['color' => 'primary', 'label' => 'المجمع: $' . number_format($opportunity->investments->sum('total_investment') ?? 0, 0)]
            ]"
            :menu-items="[
                'heading' => 'إدارة التقدم',
                'items' => [
                    ['text' => 'عرض تفاصيل الاستثمارات', 'url' => '#'],
                    ['text' => 'تحديث معدل الإكمال', 'url' => '#'],
                    ['text' => 'إرسال تقرير التقدم', 'url' => '#'],
                    [
                        'text' => 'إعدادات الفرصة',
                        'url' => '#',
                        'submenu' => [
                            ['text' => 'تعديل المبلغ المستهدف', 'url' => '#'],
                            ['text' => 'تحديث تاريخ الانتهاء', 'url' => '#'],
                            ['text' => 'إدارة الرؤية', 'url' => '#']
                        ]
                    ],
                    ['text' => 'إحصائيات مفصلة', 'url' => '#', 'separator' => true]
                ]
            ]"
        />
    </div>
</div>

<script>
// Initialize widgets when modal content is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Mixed widget loaded in modal');

    // Initialize all widgets in this modal content
    setTimeout(() => {
        if (typeof window.initializeAllWidgets === 'function') {
            window.initializeAllWidgets(document);
        }
    }, 200);
});
</script>


