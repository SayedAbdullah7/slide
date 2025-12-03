<div class="d-flex justify-content-end gap-2">
    <!-- Quick View Button -->
    <a href="#"
       class="btn btn-icon btn-light-primary btn-sm has_action"
       data-type="show"
       data-action="{{ route('admin.app-versions.show', $model->id) }}"
       data-bs-toggle="tooltip"
       title="عرض التفاصيل">
        <i class="ki-outline ki-eye fs-4"></i>
    </a>

    <!-- Quick Edit Button -->
    <a href="#"
       class="btn btn-icon btn-light-warning btn-sm has_action"
       data-type="edit"
       data-action="{{ route('admin.app-versions.edit', $model->id) }}"
       data-bs-toggle="tooltip"
       title="تعديل الإصدار">
        <i class="ki-outline ki-pencil fs-4"></i>
    </a>

    <!-- Actions Dropdown -->
    <div class="dropdown">
        <button class="btn btn-icon btn-light btn-sm"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="المزيد من الإجراءات">
            <i class="ki-outline ki-dots-vertical fs-4"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
            <!-- Version Management Section -->
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-code fs-6 me-1"></i>
                إدارة الإصدار
            </li>

            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="show"
                   data-action="{{ route('admin.app-versions.show', $model->id) }}">
                    <i class="ki-outline ki-eye fs-5 me-2 text-primary"></i>
                    عرض التفاصيل
                </a>
            </li>

            <li>
                <a class="dropdown-item has_action"
                   href="#"
                   data-type="edit"
                   data-action="{{ route('admin.app-versions.edit', $model->id) }}">
                    <i class="ki-outline ki-pencil fs-5 me-2 text-warning"></i>
                    تعديل الإصدار
                </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <!-- Status Management Section -->
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-toggle-on fs-6 me-1"></i>
                إدارة الحالة
            </li>

            @if($model->is_active)
                <li>
                    <a class="dropdown-item" href="#" onclick="toggleVersionStatus({{ $model->id }}, false)">
                        <i class="ki-outline ki-shield-cross fs-5 me-2 text-danger"></i>
                        تعطيل الإصدار
                    </a>
                </li>
            @else
                <li>
                    <a class="dropdown-item" href="#" onclick="toggleVersionStatus({{ $model->id }}, true)">
                        <i class="ki-outline ki-shield-tick fs-5 me-2 text-success"></i>
                        تفعيل الإصدار
                    </a>
                </li>
            @endif

            @if($model->is_mandatory)
                <li>
                    <a class="dropdown-item" href="#" onclick="toggleMandatoryStatus({{ $model->id }}, false)">
                        <i class="ki-outline ki-check-circle fs-5 me-2 text-warning"></i>
                        جعل التحديث اختياري
                    </a>
                </li>
            @else
                <li>
                    <a class="dropdown-item" href="#" onclick="toggleMandatoryStatus({{ $model->id }}, true)">
                        <i class="ki-outline ki-shield-tick fs-5 me-2 text-danger"></i>
                        جعل التحديث إجباري
                    </a>
                </li>
            @endif

            <!-- Danger Zone -->
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header text-muted fw-bold fs-8 text-uppercase px-4">
                <i class="ki-outline ki-information-5 fs-6 me-1"></i>
                منطقة الخطر
            </li>

            <li>
                <a class="dropdown-item delete_btn text-danger"
                   href="#"
                   data-type="delete"
                   data-action="{{ route('admin.app-versions.destroy', $model->id) }}">
                    <i class="ki-outline ki-trash fs-5 me-2"></i>
                    حذف الإصدار
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- JavaScript Functions -->
<script>
function toggleVersionStatus(versionId, activate) {
    const action = activate ? 'تفعيل' : 'تعطيل';
    if (confirm(`هل أنت متأكد من ${action} هذا الإصدار؟`)) {
        fetch(`/admin/app-versions/${versionId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ is_active: activate })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                location.reload();
            } else {
                alert('خطأ: ' + (data.message || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            alert('خطأ في تغيير حالة الإصدار');
            console.error('Error:', error);
        });
    }
}

function toggleMandatoryStatus(versionId, mandatory) {
    const action = mandatory ? 'جعل التحديث إجباري' : 'جعل التحديث اختياري';
    if (confirm(`هل أنت متأكد من ${action}؟`)) {
        fetch(`/admin/app-versions/${versionId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ is_mandatory: mandatory })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                location.reload();
            } else {
                alert('خطأ: ' + (data.message || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            alert('خطأ في تغيير حالة الإصدار');
            console.error('Error:', error);
        });
    }
}
</script>


