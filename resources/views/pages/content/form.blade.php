@php
    $isEdit = isset($content);
    $actionRoute = $isEdit
        ? route('admin.contents.update', [$content->id])
        : route('admin.contents.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Type Selection -->
    <x-select
        label="Content Type"
        name="type"
        :options="$contentTypes"
        old="{{ $isEdit ? $content->type : old('type') }}"
        required
    />

    <!-- Title -->
    <x-group-input-text
        label="Title"
        name="title"
        :value="$isEdit ? $content->title : (old('title') ?? '')"
        required
    />

    <!-- Content Text Area -->
    <x-group-input-textarea
        label="Content"
        name="content"
        :value="$isEdit ? $content->content : (old('content') ?? '')"
        rows="8"
        placeholder="Enter content here..."
        required
    />

    <!-- Last Updated Date -->
    <x-group-input-text
        label="Last Updated"
        name="last_updated"
        type="date"
        :value="$isEdit ? ($content->last_updated ? $content->last_updated->format('Y-m-d') : now()->format('Y-m-d')) : (old('last_updated') ?? now()->format('Y-m-d'))"
    />

    <!-- Is Active Checkbox -->
    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $content->is_active : (old('is_active', true))"
    />

</x-form>







