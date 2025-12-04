<div class="d-flex justify-content-end gap-2">
    <!-- Quick Edit Button -->
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.banks.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Edit bank">
        <i class="ki-outline ki-pencil fs-4"></i>
    </a>

    <!-- Delete Button -->
    <a href="#"
       class="btn btn-icon btn-light-danger btn-sm delete_btn"
       data-action="{{ route('admin.banks.destroy', $model->id) }}"
       data-bs-toggle="tooltip"
       title="Delete bank">
        <i class="ki-outline ki-trash fs-4"></i>
    </a>
</div>














