<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- FAQ Details Card -->
    <div class="card mb-7">
        <div class="card-header">
            <h3 class="card-title">FAQ Details</h3>
        </div>
        <div class="card-body">
            <table class="table table-row-bordered">
                <tbody>
                    <tr>
                        <td class="fw-bold text-gray-600" style="width: 30%;">ID</td>
                        <td class="text-gray-800">{{ $faq->id }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Question</td>
                        <td class="text-gray-800 fs-5 fw-semibold">{{ $faq->question }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Answer</td>
                        <td class="text-gray-800">
                            <div class="answer-text">
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Order</td>
                        <td class="text-gray-800">
                            <span class="badge badge-light-info fs-5">{{ $faq->order }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Status</td>
                        <td class="text-gray-800">
                            @if($faq->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Created At</td>
                        <td class="text-gray-800">{{ $faq->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-gray-600">Updated At</td>
                        <td class="text-gray-800">{{ $faq->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center pt-5 mb-7">
        <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-primary me-3">
            <i class="fa-solid fa-pen-to-square me-2"></i>Edit FAQ
        </a>
        <button type="button" class="btn btn-light-primary" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-2"></i>Close
        </button>
    </div>

</div>







