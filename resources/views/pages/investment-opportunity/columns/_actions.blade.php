<div class="d-flex flex-row text-center">
    <a href="#"
       class="has_action btn btn-icon btn-light-primary me-5"
       data-type="show"
       data-action="{{ route('investment-opportunity.show', [$model->id]) }}">
        <i class="fa-solid fa-eye"></i>
    </a>
    <a href="#"
       class="has_action btn btn-icon btn-light-warning me-5"
       data-type="edit"
       data-action="{{ route('investment-opportunity.edit', [$model->id]) }}">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="#"
       class="delete_btn btn btn-icon btn-light-danger me-5"
       data-type="delete"
       data-action="{{ route('investment-opportunity.destroy', [$model->id]) }}">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>
