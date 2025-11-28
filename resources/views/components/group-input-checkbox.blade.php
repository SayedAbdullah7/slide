<!--begin::Input group-->
<div class="fv-row mb-7">
    <!--begin::Checkbox-->
    <div class="form-check form-switch form-check-custom form-check-solid">
        <input
            class="form-check-input"
            type="checkbox"
            name="{{ $name }}"
            value="1"
            id="{{ $name }}checkBox"
            @checked(old($name, $value ?? false))
            {{ $required ?? false ? 'required' : '' }}
        />
        <label class="form-check-label" for="{{ $name }}checkBox">
            {{ $label }}
        </label>
    </div>
    <!--end::Checkbox-->
    <!--begin::Error Message-->
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <!--end::Error Message-->
</div>
<!--end::Input group-->
