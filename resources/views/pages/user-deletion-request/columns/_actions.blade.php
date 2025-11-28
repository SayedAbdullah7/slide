@php
    $isPending = $model->isPending();
    $isApproved = $model->isApproved();
    $isRejected = $model->isRejected();
    $isCancelled = $model->isCancelled();
@endphp

<div class="d-flex justify-content-end gap-2">
    <!-- Actions Dropdown -->
    <div class="dropdown">
        <button class="btn btn-icon btn-light btn-sm"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="More actions">
            <i class="ki-outline ki-dots-vertical fs-4"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
            <!-- View/Edit Section -->
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-eye fs-6 me-1"></i>
                View & Edit
            </li>

            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="edit"
                   data-action="{{ route('admin.user-deletion-requests.edit', $model->id) }}">
                    <i class="ki-outline ki-pencil fs-5 me-2 text-warning"></i>
                    Edit Request
                </a>
            </li>

            @if($model->user)
            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="show"
                   data-action="{{ route('admin.users.show', $model->user_id) }}">
                    <i class="ki-outline ki-user fs-5 me-2 text-primary"></i>
                    View User
                </a>
            </li>
            @endif

            <!-- Action Section -->
            @if($isPending)
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-check fs-6 me-1"></i>
                Actions
            </li>

            <li>
                <a class="dropdown-item admin-action-btn"
                   href="#"
                   data-action="{{ route('admin.user-deletion-requests.approve', $model->id) }}"
                   data-method="POST"
                   data-confirm="true"
                   data-confirm-text="Are you sure you want to approve this deletion request? This action cannot be undone.">
                    <i class="ki-outline ki-check-circle fs-5 me-2 text-success"></i>
                    Approve Request
                </a>
            </li>

            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="reject"
                   data-action="{{ route('admin.user-deletion-requests.reject-form', $model->id) }}">
                    <i class="ki-outline ki-cross-circle fs-5 me-2 text-danger"></i>
                    Reject Request
                </a>
            </li>

            <li>
                <a class="dropdown-item admin-action-btn"
                   href="#"
                   data-action="{{ route('admin.user-deletion-requests.cancel', $model->id) }}"
                   data-method="POST"
                   data-confirm="true"
                   data-confirm-text="Are you sure you want to cancel this deletion request?">
                    <i class="ki-outline ki-cross fs-5 me-2 text-warning"></i>
                    Cancel Request
                </a>
            </li>
            @endif

            <!-- Status Info -->
            @if(!$isPending)
            <li><hr class="dropdown-divider"></li>
            <li class="px-4 py-2">
                <div class="d-flex align-items-center">
                    <span class="badge badge-{{ $isApproved ? 'success' : ($isRejected ? 'danger' : 'secondary') }} me-2">
                        {{ ucfirst($model->status) }}
                    </span>
                    @if($model->processed_at)
                    <span class="text-muted fs-8">
                        {{ $model->processed_at->format('Y-m-d H:i') }}
                    </span>
                    @endif
                </div>
            </li>
            @endif

            <!-- Danger Zone -->
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-information-5 fs-6 me-1"></i>
                Danger Zone
            </li>

            <li>
                <a class="dropdown-item delete_btn text-danger"
                   href="#"
                   data-action="{{ route('admin.user-deletion-requests.destroy', $model->id) }}">
                    <i class="ki-outline ki-trash fs-5 me-2"></i>
                    Delete Request
                </a>
            </li>
        </ul>
    </div>
</div>

