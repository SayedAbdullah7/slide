<div class="fv-row mb-7">
    <!--begin::Label-->
    <label for="{{ $name }}" class="required fw-semibold fs-6 mb-2 text-capitalize">{{ $label }}</label>
    <!--end::Label-->
    <!--begin::Input-->
    <input {{ $attributes->except(['label', 'value'])->merge([
        'type' => $type ?? 'text',
        'class' => 'form-control form-control-solid mb-3 mb-lg-0',
        'id' => $name,
        'name' => $name,
        'value' => old($name, $value ?? ''),
        'required' => $required ?? false,
    ]) }}
    style="{{ $attributes->has('readonly') ? 'background-color: #f0f0f0; color: #888; cursor: not-allowed;' : '' }}"
    >
    <!--end::Input-->
    <!--begin::Error Message-->
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <!--end::Error Message-->
</div>
