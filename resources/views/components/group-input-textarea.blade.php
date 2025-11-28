@php
if (isset($required)) {
    $required = 'required';
}else{
    $required = '';
}
if(isset($value) && !isset($old)){
    $old = $value;
}
@endphp
<!--begin::Input group-->
<div class="fv-row mb-7">
    <!--begin::Label-->
    <label for="{{ $name }}" class="{{ $required }} fw-semibold fs-6 mb-2 text-capitalize">{{ $label }}</label>
    <!--end::Label-->
    <!--begin::Input-->
    <textarea
        {{ $attributes->except(['label', 'value', 'rows', 'placeholder'])->merge([
            'class' => 'form-control form-control-solid',
            'id' => $name,
            'name' => $name,
            'rows' => $rows ?? 4,
            'placeholder' => $placeholder ?? 'Enter ' . strtolower($label) . '...',
            'required' => $required ?? false,
        ]) }}
        style="{{ $attributes->has('readonly') ? 'background-color: #f0f0f0; color: #888; cursor: not-allowed;' : '' }}"
    >{{ old($name, $value ?? '') }}</textarea>
    <!--end::Input-->
    <!--begin::Error Message-->
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <!--end::Error Message-->
</div>
<!--end::Input group-->
