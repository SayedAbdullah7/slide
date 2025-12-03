@php
    $isEdit = isset($appVersion) || isset($model);
    $version = $appVersion ?? $model ?? null;
    $actionRoute = $isEdit
        ? route('admin.app-versions.update', [$version->id])
        : route('admin.app-versions.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Version -->
    <x-group-input-text
        label="الإصدار"
        name="version"
        :value="$isEdit ? $version->version : (old('version') ?? '')"
        placeholder="مثال: 1.0.0"
        required
    />

    <!-- OS Selection -->
    <x-select
        label="نظام التشغيل"
        name="os"
        :options="[
            'iOS' => 'ios',
            'Android' => 'android'
        ]"
        old="{{ $isEdit ? $version->os : old('os') }}"
        required
    />

    <!-- Is Mandatory Checkbox -->
    <x-group-input-checkbox
        label="تحديث إجباري"
        name="is_mandatory"
        :value="$isEdit ? $version->is_mandatory : (old('is_mandatory', false))"
    />

    <!-- Release Notes (English) -->
    <x-group-input-textarea
        label="ملاحظات الإصدار (إنجليزي)"
        name="release_notes"
        :value="$isEdit ? $version->release_notes : (old('release_notes') ?? '')"
        rows="5"
        placeholder="Enter release notes in English..."
    />

    <!-- Release Notes (Arabic) -->
    <x-group-input-textarea
        label="ملاحظات الإصدار (عربي)"
        name="release_notes_ar"
        :value="$isEdit ? $version->release_notes_ar : (old('release_notes_ar') ?? '')"
        rows="5"
        placeholder="أدخل ملاحظات الإصدار بالعربية..."
    />

    <!-- Released At Date -->
    <x-group-input-text
        label="تاريخ الإصدار"
        name="released_at"
        type="date"
        :value="$isEdit ? ($version->released_at ? $version->released_at->format('Y-m-d') : now()->format('Y-m-d')) : (old('released_at') ?? now()->format('Y-m-d'))"
    />

    <!-- Is Active Checkbox -->
    <x-group-input-checkbox
        label="مفعل"
        name="is_active"
        :value="$isEdit ? $version->is_active : (old('is_active', true))"
    />

</x-form>

