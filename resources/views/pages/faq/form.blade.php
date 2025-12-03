@php
    $isEdit = isset($faq);
    $actionRoute = $isEdit
        ? route('admin.faqs.update', [$faq->id])
        : route('admin.faqs.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- Question -->
    <x-group-input-text
        label="Question"
        name="question"
        :value="$isEdit ? $faq->question : (old('question') ?? '')"
        required
    />

    <!-- Answer Text Area -->
    <x-group-input-textarea
        label="Answer"
        name="answer"
        :value="$isEdit ? $faq->answer : (old('answer') ?? '')"
        rows="6"
        placeholder="Enter answer here..."
        required
    />

    <!-- Order -->
    <x-group-input-text
        label="Order"
        name="order"
        type="number"
        :value="$isEdit ? $faq->order : (old('order') ?? 0)"
        required
    />

    <!-- Is Active Checkbox -->
    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $faq->is_active : (old('is_active', true))"
    />

</x-form>







