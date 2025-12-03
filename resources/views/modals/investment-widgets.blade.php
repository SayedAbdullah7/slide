@php
    $opportunity = $opportunity ?? null;
@endphp

<div class="row g-5 g-xl-8">
    <!-- Merchandise Widget -->
    <div class="col-xl-4">
        <x-modal-widget-simple
            title="إدارة البضائع"
            subtitle="تتبع حالة تسليم البضائع"
            :progressValue="$opportunity && $opportunity->all_merchandise_delivered ? 100 : 65"
            buttonColor="primary"
            buttonText="إدارة البضائع"
            :buttonUrl="$opportunity ? route('admin.investment-opportunities.merchandise-status', $opportunity->id) : '#'"
            note="تأكد من وصول جميع البضائع قبل التوزيع"
            noteBadge="تنبيه"
            noteBadgeColor="warning"
            :showFilter="false"
        />
    </div>

    <!-- Returns Widget -->
    <div class="col-xl-4">
        <x-modal-widget-simple
            title="إدارة العوائد"
            subtitle="تتبع وتسجيل العوائد الفعلية"
            :progressValue="$opportunity && $opportunity->all_returns_distributed ? 100 : 80"
            buttonColor="success"
            buttonText="إدارة العوائد"
            :buttonUrl="$opportunity ? route('admin.investment-opportunities.returns-status', $opportunity->id) : '#'"
            note="تسجيل العوائد الفعلية وتوزيعها على المستثمرين"
            noteBadge="مهم"
            noteBadgeColor="info"
            :showFilter="false"
        />
    </div>

    <!-- Completion Widget -->
    <div class="col-xl-4">
        <x-modal-widget-simple
            title="معدل الإكمال"
            subtitle="تتبع تقدم الفرصة الاستثمارية"
            :progressValue="$opportunity ? min($opportunity->completion_rate, 100) : 88"
            buttonColor="info"
            buttonText="عرض التفاصيل"
            :buttonUrl="$opportunity ? route('admin.investments.index', ['opportunity_id' => $opportunity->id]) : '#'"
            note="مراقبة معدل الإكمال والوصول للهدف المطلوب"
            noteBadge="متابعة"
            noteBadgeColor="primary"
            :showFilter="false"
        />
    </div>
</div>

<script>
// Initialize widgets when modal content is loaded
document.addEventListener('DOMContentLoaded', function() {
    // This will be called by the main.js initializeModalContent function
    console.log('Investment widgets loaded in modal');
});
</script>


