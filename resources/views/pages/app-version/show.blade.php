<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- App Version Details Card -->
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">تفاصيل الإصدار</h3>
        </div>
        <div class="card-body">
            <table class="table table-row-bordered">
                <tbody>
                    <tr>
                        <td class="fw-bold text-gray-600" style="width: 30%;">ID</td>
                        <td class="text-gray-800">{{ $appVersion->id }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">الإصدار</td>
                        <td class="text-gray-800">
                            <span class="badge badge-primary fs-6">{{ $appVersion->version }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">نظام التشغيل</td>
                        <td class="text-gray-800">
                            @if($appVersion->os == 'ios')
                                <span class="badge badge-light-primary">
                                    <i class="fa-brands fa-apple me-1"></i>iOS
                                </span>
                            @else
                                <span class="badge badge-light-success">
                                    <i class="fa-brands fa-android me-1"></i>Android
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">تحديث إجباري</td>
                        <td class="text-gray-800">
                            @if($appVersion->is_mandatory)
                                <span class="badge badge-danger">إجباري</span>
                            @else
                                <span class="badge badge-light">اختياري</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">الحالة</td>
                        <td class="text-gray-800">
                            @if($appVersion->is_active)
                                <span class="badge badge-success">مفعل</span>
                            @else
                                <span class="badge badge-danger">معطل</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">ملاحظات الإصدار (إنجليزي)</td>
                        <td class="text-gray-800">
                            <div class="content-text">
                                {!! nl2br(e($appVersion->release_notes ?? 'N/A')) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">ملاحظات الإصدار (عربي)</td>
                        <td class="text-gray-800">
                            <div class="content-text">
                                {!! nl2br(e($appVersion->release_notes_ar ?? 'N/A')) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">تاريخ الإصدار</td>
                        <td class="text-gray-800">
                            {{ $appVersion->released_at ? $appVersion->released_at->format('Y-m-d H:i:s') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">تاريخ الإنشاء</td>
                        <td class="text-gray-800">{{ $appVersion->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">آخر تحديث</td>
                        <td class="text-gray-800">{{ $appVersion->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center pt-5 mb-7">
        <a href="#"
           class="btn btn-primary me-3 has_action"
           data-type="edit"
           data-action="{{ route('admin.app-versions.edit', $appVersion->id) }}">
            <i class="fa-solid fa-pen-to-square me-2"></i>تعديل الإصدار
        </a>
        <button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>إغلاق
        </button>
    </div>

</div>

