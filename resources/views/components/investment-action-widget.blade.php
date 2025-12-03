@props([
    'type' => 'merchandise', // merchandise, returns, distribution, completion
    'opportunity' => null,
    'class' => 'col-xl-4'
])

@php
    $configs = [
        'merchandise' => [
            'title' => 'إدارة البضائع',
            'subtitle' => 'تتبع حالة تسليم البضائع',
            'chartColor' => 'primary',
            'buttonColor' => 'primary',
            'buttonText' => 'إدارة البضائع',
            'buttonUrl' => '#',
            'note' => 'تأكد من وصول جميع البضائع قبل التوزيع',
            'noteBadge' => 'تنبيه',
            'noteBadgeColor' => 'warning',
            'showFilter' => true,
            'filterOptions' => [
                'status' => [
                    'label' => 'حالة التسليم:',
                    'options' => ['تم التسليم', 'في الانتظار', 'متأخر', 'ملغي']
                ],
                'memberType' => [
                    'label' => 'نوع الاستثمار:',
                    'options' => ['نفسي' => false, 'مفوض' => true]
                ]
            ]
        ],
        'returns' => [
            'title' => 'إدارة العوائد',
            'subtitle' => 'تتبع وتسجيل العوائد الفعلية',
            'chartColor' => 'success',
            'buttonColor' => 'success',
            'buttonText' => 'إدارة العوائد',
            'buttonUrl' => '#',
            'note' => 'تسجيل العوائد الفعلية وتوزيعها على المستثمرين',
            'noteBadge' => 'مهم',
            'noteBadgeColor' => 'info',
            'showFilter' => true,
            'filterOptions' => [
                'status' => [
                    'label' => 'حالة التوزيع:',
                    'options' => ['مكتمل', 'جاري', 'معلق', 'مرفوض']
                ],
                'memberType' => [
                    'label' => 'نوع الاستثمار:',
                    'options' => ['نفسي' => false, 'مفوض' => true]
                ]
            ]
        ],
        'distribution' => [
            'title' => 'توزيع الأرباح',
            'subtitle' => 'توزيع الأرباح على المستثمرين',
            'chartColor' => 'warning',
            'buttonColor' => 'warning',
            'buttonText' => 'توزيع الأرباح',
            'buttonUrl' => '#',
            'note' => 'توزيع الأرباح المحققة على جميع المستثمرين',
            'noteBadge' => 'تنبيه',
            'noteBadgeColor' => 'success',
            'showFilter' => true,
            'filterOptions' => [
                'status' => [
                    'label' => 'حالة التوزيع:',
                    'options' => ['موزع', 'في الانتظار', 'معلق', 'مكتمل']
                ]
            ]
        ],
        'completion' => [
            'title' => 'معدل الإكمال',
            'subtitle' => 'تتبع تقدم الفرصة الاستثمارية',
            'chartColor' => 'info',
            'buttonColor' => 'info',
            'buttonText' => 'عرض التفاصيل',
            'buttonUrl' => '#',
            'note' => 'مراقبة معدل الإكمال والوصول للهدف المطلوب',
            'noteBadge' => 'متابعة',
            'noteBadgeColor' => 'primary',
            'showFilter' => false,
            'filterOptions' => []
        ]
    ];

    $config = $configs[$type] ?? $configs['merchandise'];

    // إضافة البيانات الديناميكية إذا كانت الفرصة متوفرة
    if ($opportunity) {
        switch($type) {
            case 'merchandise':
                $config['buttonUrl'] = route('admin.investment-opportunities.merchandise-status', $opportunity->id);
                break;
            case 'returns':
                $config['buttonUrl'] = route('admin.investment-opportunities.returns-status', $opportunity->id);
                break;
            case 'distribution':
                $config['buttonUrl'] = '#';
                break;
            case 'completion':
                $config['buttonUrl'] = route('admin.investments.index', ['opportunity_id' => $opportunity->id]);
                break;
        }
    }
@endphp

<x-dynamic-action-widget
    :title="$config['title']"
    :subtitle="$config['subtitle']"
    :chartColor="$config['chartColor']"
    :buttonColor="$config['buttonColor']"
    :buttonText="$config['buttonText']"
    :buttonUrl="$config['buttonUrl']"
    :note="$config['note']"
    :noteBadge="$config['noteBadge']"
    :noteBadgeColor="$config['noteBadgeColor']"
    :showFilter="$config['showFilter']"
    :filterOptions="$config['filterOptions']"
    :class="$class"
/>
