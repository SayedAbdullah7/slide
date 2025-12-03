@php
    $actionRoute = route('admin.user-deletion-requests.reject', $model->id);
@endphp

<x-form :actionRoute="$actionRoute" :method="'POST'" :isEdit="false">

    <div class="alert alert-warning mb-7">
        <div class="d-flex align-items-center">
            <i class="ki-duotone ki-warning-2 fs-2x text-warning me-4">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-warning">Reject Deletion Request</h4>
                <span>You are about to reject this user deletion request. Please provide a reason below.</span>
            </div>
        </div>
    </div>

    <!-- User Info (Readonly) -->
    <div class="fv-row mb-7">
        <label class="fw-semibold fs-6 mb-2">User</label>
        <input type="text"
               class="form-control form-control-solid"
               value="{{ $model->user ? $model->user->display_name . ' (' . $model->user->phone . ')' : 'N/A' }}"
               readonly
               style="background-color: #f0f0f0; color: #888; cursor: not-allowed;">
    </div>

    <!-- Reason (Readonly) -->
    @if($model->reason)
    <div class="fv-row mb-7">
        <label class="fw-semibold fs-6 mb-2">User's Reason</label>
        <textarea class="form-control form-control-solid" rows="3" readonly style="background-color: #f0f0f0; color: #888; cursor: not-allowed;">{{ $model->reason }}</textarea>
    </div>
    @endif

    <!-- Admin Notes -->
    <x-group-input-textarea
        label="Admin Notes (Rejection Reason)"
        name="admin_notes"
        :value="old('admin_notes') ?? ''"
        rows="4"
        placeholder="Enter reason for rejection..."
    />

</x-form>











