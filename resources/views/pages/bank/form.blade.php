@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.banks.update', [$model->id])
        : route('admin.banks.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Code -->
    <x-group-input-text
        label="Code"
        name="code"
        :value="$isEdit ? $model->code : (old('code') ?? '')"
        required
    />

    <!-- Name Arabic -->
    <x-group-input-text
        label="Name (Arabic)"
        name="name_ar"
        :value="$isEdit ? $model->name_ar : (old('name_ar') ?? '')"
        required
    />

    <!-- Name English -->
    <x-group-input-text
        label="Name (English)"
        name="name_en"
        :value="$isEdit ? $model->name_en : (old('name_en') ?? '')"
        required
    />

    <!-- Icon -->
    <x-group-input-text
        label="Icon (CSS Class)"
        name="icon"
        :value="$isEdit ? $model->icon : (old('icon') ?? '')"
        placeholder="e.g., ki-outline ki-bank fs-2"
    />

    <!-- Is Active -->
    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $model->is_active : (old('is_active', true))"
    />

</x-form>











