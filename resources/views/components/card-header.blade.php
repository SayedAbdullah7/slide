@props([
    'title' => 'Card Title',
    'subtitle' => 'Card subtitle',
    'class' => 'col-xl-4'
])

<div class="{{ $class }}">
    <div class="card card-flush h-xl-100">
        <div class="card-header pt-7">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-800">{{ $title }}</span>
                <span class="text-muted mt-1 fw-semibold fs-7">{{ $subtitle }}</span>
            </h3>
        </div>
        <div class="card-body pt-6">
            {{ $slot }}
        </div>
    </div>
</div>

