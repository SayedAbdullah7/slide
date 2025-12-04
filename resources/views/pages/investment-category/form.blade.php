@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.investment-categories.update', [$model->id])
        : route('admin.investment-categories.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Name -->
    <x-group-input-text
        label="Name"
        name="name"
        :value="$isEdit ? $model->name : (old('name') ?? '')"
        required
    />

    <!-- Description -->
    <x-group-input-textarea
        label="Description"
        name="description"
        :value="$isEdit ? $model->description : (old('description') ?? '')"
        rows="4"
        placeholder="Enter description..."
    />

    <!-- Icon -->
    <x-group-input-text
        label="Icon (CSS Class)"
        name="icon"
        :value="$isEdit ? $model->icon : (old('icon') ?? '')"
        placeholder="e.g., ki-outline ki-category fs-2"
    />

    <!-- Is Active -->
    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $model->is_active : (old('is_active', true))"
    />

</x-form>














