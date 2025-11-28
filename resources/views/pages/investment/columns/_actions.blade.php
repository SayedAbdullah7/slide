<div class="d-flex flex-row text-center">
    {{-- View Action --}}
    <a href="#"
       class="has_action btn btn-icon btn-light-primary me-2"
       data-type="show"
       data-action="{{ route('admin.investments.show', [$model->id]) }}"
       title="View Details">
        <i class="fa-solid fa-eye"></i>
    </a>

    {{-- Merchandise Delivery Action (only for Myself type, pending delivery) --}}
    @if($model->isMyselfType())
        @if($model->isReadyForMerchandiseArrival())
        <a href="#"
           class="admin-action-btn btn btn-icon btn-light-success me-2"
           data-action="{{ route('admin.investments.mark-merchandise-arrived', $model->id) }}"
           data-method="POST"
           data-confirm="true"
           data-confirm-text="Mark merchandise as arrived for this investment?"
           title="Mark Merchandise Arrived">
            <i class="ki-duotone ki-package fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
        @else
        <button class="admin-action-btn btn btn-icon btn-light-secondary me-2" disabled>
            <i class="ki-duotone ki-package fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </button>
        @endif


    @endif

    {{-- Distribute Profit Action (only for Authorize type, ready for distribution) --}}
    @if($model->isAuthorizeType())
        @if($model->isReadyForDistribution())
        <a href="#"
           class="admin-action-btn btn btn-icon btn-light-warning me-2"
           data-action="{{ route('admin.investments.distribute-profit', $model->id) }}"
           data-method="POST"
           data-confirm="true"
           data-confirm-text="Distribute profit for this investment?"
           title="Distribute Profit">
            <i class="ki-duotone ki-dollar fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
        @else
        <button class="admin-action-btn btn btn-icon btn-light-secondary me-2" disabled>
            <i class="ki-duotone ki-dollar fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </button>
        @endif
    @endif

    {{-- Edit Action (commented out) --}}
    {{-- <a href="#"
       class="has_action btn btn-icon btn-light-warning me-2"
       data-type="edit"
       data-action="{{ route('admin.investments.edit', [$model->id]) }}"
       title="Edit">
        <i class="fa-solid fa-pen-to-square"></i>
    </a> --}}

    {{-- Delete Action (commented out) --}}
    {{-- <a href="#"
       class="delete_btn btn btn-icon btn-light-danger me-2"
       data-type="delete"
       data-action="{{ route('admin.investments.destroy', [$model->id]) }}"
       title="Delete">
        <i class="fa-solid fa-trash"></i>
    </a> --}}
</div>
