@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.user-deletion-requests.update', [$model->id])
        : route('admin.user-deletion-requests.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- User (Readonly - Created via API) -->
    <div class="fv-row mb-7">
        <label class="fw-semibold fs-6 mb-2">User</label>
        <input type="text" 
               class="form-control form-control-solid" 
               value="{{ $isEdit && $model->user ? $model->user->display_name . ' (' . $model->user->phone . ')' : 'N/A' }}"
               readonly
               style="background-color: #f0f0f0; color: #888; cursor: not-allowed;">
        <input type="hidden" name="user_id" value="{{ $isEdit ? $model->user_id : '' }}">
        <div class="form-text text-muted fs-8">Note: User deletion requests are created by users via API only.</div>
    </div>

    <!-- Reason -->
    <x-group-input-textarea
        label="Reason"
        name="reason"
        :value="$isEdit ? $model->reason : (old('reason') ?? '')"
        rows="4"
        placeholder="Enter reason for deletion request..."
    />

    <!-- Status -->
    <div class="fv-row mb-7">
        <label for="status" class="required fw-semibold fs-6 mb-2">Status</label>
        <select name="status" id="status" class="form-select form-select-solid" data-kt-select2="true" required>
            @foreach(\App\Models\UserDeletionRequest::STATUSES as $status)
                <option value="{{ $status }}" {{ ($isEdit && $model->status == $status) || old('status', \App\Models\UserDeletionRequest::STATUS_PENDING) == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Admin Notes -->
    <x-group-input-textarea
        label="Admin Notes"
        name="admin_notes"
        :value="$isEdit ? $model->admin_notes : (old('admin_notes') ?? '')"
        rows="3"
        placeholder="Enter admin notes..."
    />

</x-form>

