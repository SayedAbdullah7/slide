@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('admin.contact-messages.update', [$model->id])
        : route('admin.contact-messages.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <!-- User -->
    <div class="fv-row mb-7">
        <label for="user_id" class="fw-semibold fs-6 mb-2">User (Optional)</label>
        <select name="user_id" id="user_id" class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Select user (optional)">
            <option value="">Guest (No user)</option>
            @foreach(\App\Models\User::all() as $user)
                <option value="{{ $user->id }}" {{ ($isEdit && $model->user_id == $user->id) || old('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->display_name }} ({{ $user->phone }})
                </option>
            @endforeach
        </select>
        @error('user_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Profile Type -->
    <div class="fv-row mb-7">
        <label for="profile_type" class="required fw-semibold fs-6 mb-2">Profile Type</label>
        <select name="profile_type" id="profile_type" class="form-select form-select-solid" data-kt-select2="true" required>
            @foreach(\App\Models\ContactMessage::PROFILE_TYPES as $type)
                <option value="{{ $type }}" {{ ($isEdit && $model->profile_type == $type) || old('profile_type', \App\Models\ContactMessage::PROFILE_TYPE_GUEST) == $type ? 'selected' : '' }}>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
        @error('profile_type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <!-- Subject -->
    <x-group-input-text
        label="Subject"
        name="subject"
        :value="$isEdit ? $model->subject : (old('subject') ?? '')"
        required
    />

    <!-- Message -->
    <x-group-input-textarea
        label="Message"
        name="message"
        :value="$isEdit ? $model->message : (old('message') ?? '')"
        rows="6"
        placeholder="Enter message..."
        required
    />

    <!-- Status -->
    <div class="fv-row mb-7">
        <label for="status" class="required fw-semibold fs-6 mb-2">Status</label>
        <select name="status" id="status" class="form-select form-select-solid" data-kt-select2="true" required>
            @foreach(\App\Models\ContactMessage::STATUSES as $status)
                <option value="{{ $status }}" {{ ($isEdit && $model->status == $status) || old('status', \App\Models\ContactMessage::STATUS_PENDING) == $status ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
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













