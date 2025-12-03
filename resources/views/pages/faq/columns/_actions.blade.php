<div class="d-flex flex-row justify-content-center">
    <a href="#"
       class="has_action btn btn-icon btn-light-primary me-2"
       data-type="show"
       data-action="{{ route('admin.faqs.show', $model->id) }}"
       title="View">
        <i class="fa-solid fa-eye"></i>
    </a>
    <a href="#"
       class="has_action btn btn-icon btn-light-warning me-2"
       data-type="edit"
       data-action="{{ route('admin.faqs.edit', $model->id) }}"
       title="Edit">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="#"
       class="delete_btn btn btn-icon btn-light-danger"
       data-action="{{ route('admin.faqs.destroy', $model->id) }}"
       title="Delete">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>
