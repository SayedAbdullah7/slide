@props([
    'type' => 'merchandise', // merchandise, returns
    'isActive' => false,
    'isCompleted' => false,
    'totalCount' => 0,
    'pendingCount' => 0,
    'actionUrl' => '#',
    'class' => '',
])

@php
    $configs = [
        'merchandise' => [
            'icon' => 'fa-solid fa-user',
            'buttonClass' => 'btn-primary',
            'activeText' => 'اضغط هنا لتسليم البضائع',
            'completedText' => 'تم التسليم',
            'inactiveText' => 'لا توجد استثمارات بنفسي',
            'description' => 'استثمارات بنفسي لم يتم تسليم البضائع لهم',
            'confirm' => true,
            'confirmText' => 'هل أنت متأكد من تسليم البضائع لهذه الفرصة؟'

        ],
        'returns' => [
            'icon' => 'fa-solid fa-handshake',
            'buttonClass' => 'btn-success',
            'activeText' => 'اضغط هنا لتوزيع العوائد',
            'completedText' => 'تم التوزيع',
            'inactiveText' => 'لا توجد استثمارات مفوضة',
            'description' => 'استثمارات مفوضة لم يتم توزيع العوائد لهم',
            'confirm' => true,
            'confirmText' => 'هل أنت متأكد من توزيع العوائد لهذه الفرصة؟'
        ]
    ];

    $config = $configs[$type] ?? $configs['merchandise'];

    $buttonText = $isCompleted ? $config['completedText'] : ($isActive ? $config['activeText'] : $config['inactiveText']);
    $isDisabled = !$isActive || $isCompleted;
@endphp

<div class="investment-action-button {{ $class }}" style="height: 120px;">
    @if($isActive && !$isCompleted)
        <a href="#" data-action="{{ $actionUrl }}" class="admin-action-btn btn btn-flex {{ $config['buttonClass'] }} px-6 justify-content-center align-items-center h-100" data-confirm="{{ $config['confirm'] }}" data-confirm-text="{{ $config['confirmText'] }}">
            <i class="{{ $config['icon'] }} fs-2x"></i>
            <span class="d-flex flex-column align-items-center ms-2">
                <span class="fs-3 fw-bold text-center">{{ $buttonText }}</span>
                <span class="fs-7 text-center">
                    {{ $totalCount }} / {{ $pendingCount }}
                    {{ $config['description'] }}
                </span>
            </span>
        </a>
    @else
        <button class="btn btn-flex {{ $config['buttonClass'] }} px-6 justify-content-center align-items-center h-100" disabled>
            <i class="{{ $config['icon'] }} fs-2x"></i>
            <span class="d-flex flex-column align-items-center ms-2">
                <span class="fs-3 fw-bold text-center">{{ $buttonText }}</span>
                <span class="fs-7 text-center">
                    {{ $totalCount }} / {{ $pendingCount }}
                    {{ $config['description'] }}
                </span>
            </span>
        </button>
    @endif
</div>
