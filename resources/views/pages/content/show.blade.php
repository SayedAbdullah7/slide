<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Content Details Card -->
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">Content Details</h3>
        </div>
        <div class="card-body">
            <table class="table table-row-bordered">
                <tbody>
                    <tr>
                        <td class="fw-bold text-gray-600" style="width: 30%;">ID</td>
                        <td class="text-gray-800">{{ $content->id }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Type</td>
                        <td class="text-gray-800">
                            @php
                                $types = \App\Models\Content::getContentTypes();
                            @endphp
                            <span class="badge badge-light-primary">{{ $types[$content->type] ?? $content->type }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Title</td>
                        <td class="text-gray-800">{{ $content->title }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Content</td>
                        <td class="text-gray-800">
                            <div class="content-text">
                                {!! nl2br(e($content->content)) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Status</td>
                        <td class="text-gray-800">
                            @if($content->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Last Updated</td>
                        <td class="text-gray-800">
                            {{ $content->last_updated ? $content->last_updated->format('Y-m-d H:i:s') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Created At</td>
                        <td class="text-gray-800">{{ $content->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Updated At</td>
                        <td class="text-gray-800">{{ $content->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center pt-5 mb-7">
        <a href="{{ route('admin.contents.edit', $content->id) }}" class="btn btn-primary me-3">
            <i class="fa-solid fa-pen-to-square me-2"></i>Edit Content
        </a>
        <button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Close
        </button>
    </div>

</div>







