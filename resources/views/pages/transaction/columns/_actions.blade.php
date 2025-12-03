<div class="d-flex justify-content-end gap-2">
    <!-- View Details -->
    <a href="#"
       class="btn btn-icon btn-light-primary btn-sm has_action"
       data-type="show"
       data-action="{{ route('admin.transactions.show', $model->id) }}"
       data-bs-toggle="tooltip"
       title="View transaction details">
        <i class="ki-outline ki-eye fs-4"></i>
    </a>

    <!-- Copy UUID -->
    <button class="btn btn-icon btn-light-info btn-sm"
            onclick="navigator.clipboard.writeText('{{ $model->uuid }}')"
            data-bs-toggle="tooltip"
            title="Copy UUID">
        <i class="ki-outline ki-copy fs-4"></i>
    </button>

    <!-- More Actions Dropdown -->
    <div class="dropdown">
        <button class="btn btn-icon btn-light btn-sm"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="ki-outline ki-dots-vertical fs-4"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <!-- View Payable Account -->
            @if($model->payable)
                <li>
                    <a class="dropdown-item"
                       href="{{ $model->payable_type === 'App\\Models\\User' ? route('admin.users.show', $model->payable_id) : '#' }}">
                        <i class="ki-outline ki-user fs-5 me-2"></i>
                        View Account
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
            @endif

            <!-- Export -->
            <li>
                <a class="dropdown-item" href="#" onclick="exportTransaction({{ $model->id }})">
                    <i class="ki-outline ki-file-down fs-5 me-2"></i>
                    Export Details
                </a>
            </li>

            <!-- View Meta -->
            @if($model->meta && !empty($model->meta))
                <li>
                    <a class="dropdown-item"
                       href="#"
                       data-bs-toggle="modal"
                       data-bs-target="#metaModal{{ $model->id }}">
                        <i class="ki-outline ki-information fs-5 me-2"></i>
                        View Metadata
                    </a>
                </li>
            @endif

            @if(!$model->confirmed)
                <li><hr class="dropdown-divider"></li>
                <!-- Confirm Transaction (if pending) -->
                <li>
                    <a class="dropdown-item text-success"
                       href="#"
                       onclick="confirmTransaction({{ $model->id }})">
                        <i class="ki-outline ki-check-circle fs-5 me-2"></i>
                        Confirm Transaction
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>

<!-- Meta Modal (if exists) -->
@if($model->meta && !empty($model->meta))
    <div class="modal fade" id="metaModal{{ $model->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ki-outline ki-information fs-3 me-2"></i>
                        Transaction Metadata
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th style="width: 30%">Key</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($model->meta as $key => $value)
                                    <tr>
                                        <td class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                        <td>
                                            @if(is_array($value))
                                                <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
function confirmTransaction(id) {
    if (confirm('Are you sure you want to confirm this transaction?')) {
        // Send AJAX request to confirm transaction
        fetch(`/admin/transactions/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error confirming transaction');
            console.error('Error:', error);
        });
    }
}

function exportTransaction(id) {
    window.location.href = `/admin/transactions/${id}/export`;
}
</script>
