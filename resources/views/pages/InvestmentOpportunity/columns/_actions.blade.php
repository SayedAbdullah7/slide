<div class="d-flex justify-content-end flex-shrink-0">
    <a href="#" 
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 has_action" 
       data-type="show" 
       data-action="{{ route('investment-opportunity.show', $model->id) }}"
       title="View">
        <i class="ki-duotone ki-eye fs-3">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </a>
    <a href="#" 
       class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 has_action" 
       data-type="edit" 
       data-action="{{ route('investment-opportunity.edit', $model->id) }}"
       title="Edit">
        <i class="ki-duotone ki-pencil fs-3">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    <a href="#" 
       class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm has_action" 
       data-type="delete" 
       data-action="{{ route('investment-opportunity.destroy', $model->id) }}"
       title="Delete">
        <i class="ki-duotone ki-trash fs-3">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </a>
</div>