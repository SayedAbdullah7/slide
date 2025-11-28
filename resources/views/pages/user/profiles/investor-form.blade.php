@php
    $isEdit = isset($investorProfile);
    $actionRoute = $isEdit
        ? route('user.investor-profile.update', [$user->id])
        : route('user.investor-profile.store', [$user->id]);
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
                <h4 class="mb-1 text-info">Investor Profile</h4>
                <span>{{ $isEdit ? 'Editing' : 'Creating' }} investor profile for {{ $user->display_name }}</span>
            </div>
        </div>
    </div>

    <x-group-input-text
        label="Full Name"
        name="full_name"
        :value="$isEdit ? $investorProfile->full_name : (old('full_name') ?? '')"
        required
    />

    <x-group-input-text
        label="National ID"
        name="national_id"
        :value="$isEdit ? $investorProfile->national_id : (old('national_id') ?? '')"
        required
    />

    <x-group-input-text
        label="Birth Date"
        name="birth_date"
        type="date"
        :value="$isEdit ? $investorProfile->birth_date?->format('Y-m-d') : (old('birth_date') ?? '')"
        required
    />

    <x-group-input-text
        label="Extra Data"
        name="extra_data"
        :value="$isEdit ? $investorProfile->extra_data : (old('extra_data') ?? '')"
    />

    <div class="fv-row mb-7">
        <div class="form-check form-check-custom form-check-solid">
            <input class="form-check-input" type="checkbox" id="walletInfo" checked disabled />
            <label class="form-check-label" for="walletInfo">
                Wallet functionality will be automatically enabled for this investor profile
            </label>
        </div>
    </div>

</x-form>
