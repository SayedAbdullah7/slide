@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('user.update', [$model->id])
        : route('user.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    <div class="alert alert-info mb-7">
        <div class="d-flex align-items-center">
            <i class="ki-duotone ki-information-5 fs-2x text-info me-4">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-info">User Information</h4>
                <span>Basic user account information. Personal details are managed in profile forms.</span>
            </div>
        </div>
    </div>

    <x-group-input-text
        label="Phone"
        name="phone"
        :value="$isEdit ? $model->phone : (old('phone') ?? '')"
        required
    />

    <x-group-input-text
        label="Email"
        name="email"
        :value="$isEdit ? $model->email : (old('email') ?? '')"
        required
    />

    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $model->is_active : (old('is_active', true))"
    />

    <x-group-input-checkbox
        label="Is Registered"
        name="is_registered"
        :value="$isEdit ? $model->is_registered : (old('is_registered', false))"
    />

    @if($isEdit)
        <div class="alert alert-warning mb-7">
            <div class="d-flex align-items-center">
                <i class="ki-duotone ki-warning-2 fs-2x text-warning me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Profile Management</h4>
                    <span>Personal information (name, national ID, birth date) is managed in the respective profile forms.</span>
                    <div class="mt-2">
                        @if($model->hasInvestor())
                            <a href="#" data-type="edit" data-action="{{ route('user.investor-profile.edit', $model->id) }}" class="btn btn-sm btn-light-primary me-2 has_action">
                                <i class="ki-outline ki-user fs-4 me-1"></i>
                                Edit Investor Profile
                            </a>
                        @endif
                        @if($model->hasOwner())
                            <a href="#" data-type="edit" data-action="{{ route('user.owner-profile.edit', $model->id) }}" class="btn btn-sm btn-light-info has_action">
                                <i class="ki-outline ki-briefcase fs-4 me-1"></i>
                                Edit Owner Profile
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-form>
