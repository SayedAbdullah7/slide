@php
    $isEdit = isset($ownerProfile);
    $actionRoute = $isEdit
        ? route('user.owner-profile.update', [$user->id])
        : route('user.owner-profile.store', [$user->id]);
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
                <h4 class="mb-1 text-info">Owner Profile</h4>
                <span>Creating owner profile for {{ $user->display_name }}</span>
            </div>
        </div>
    </div>

    <x-group-input-text
        label="Business Name"
        name="business_name"
        :value="$isEdit ? $ownerProfile->business_name : (old('business_name') ?? '')"
        required
    />

    <x-group-input-text
        label="Tax Number"
        name="tax_number"
        :value="$isEdit ? $ownerProfile->tax_number : (old('tax_number') ?? '')"
        required
    />

    <x-group-input-text
        label="Business Address"
        name="business_address"
        :value="$isEdit ? $ownerProfile->business_address : (old('business_address') ?? '')"
    />

    <x-group-input-text
        label="Business Phone"
        name="business_phone"
        :value="$isEdit ? $ownerProfile->business_phone : (old('business_phone') ?? '')"
    />

    <x-group-input-text
        label="Business Email"
        name="business_email"
        type="email"
        :value="$isEdit ? $ownerProfile->business_email : (old('business_email') ?? '')"
    />

    <x-group-input-text
        label="Business Website"
        name="business_website"
        type="url"
        :value="$isEdit ? $ownerProfile->business_website : (old('business_website') ?? '')"
    />

    <x-group-input-text
        label="Goal"
        name="goal"
        :value="$isEdit ? $ownerProfile->goal : (old('goal') ?? '')"
    />

    <div class="fv-row mb-7">
        <label class="fw-semibold fs-6 mb-2">Business Description</label>
        <textarea
            name="business_description"
            class="form-control form-control-solid"
            rows="4"
            placeholder="Enter business description..."
        >{{ $isEdit ? $ownerProfile->business_description : (old('business_description') ?? '') }}</textarea>
        @error('business_description')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="fv-row mb-7">
        <div class="form-check form-check-custom form-check-solid">
            <input class="form-check-input" type="checkbox" id="walletInfo" checked disabled />
            <label class="form-check-label" for="walletInfo">
                Wallet functionality will be automatically enabled for this owner profile
            </label>
        </div>
    </div>

</x-form>
