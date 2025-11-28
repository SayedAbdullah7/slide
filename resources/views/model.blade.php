

<div class="modal fade" id="modal-form"  tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px" id="modal-dialog">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
{{--                <h2 class="fw-bold">Add User</h2>--}}
                <!--end::Modal title-->
                <!--begin::Toggle Width Button-->
                <button type="button" class="btn btn-icon btn-sm btn-light me-2" id="toggle-modal-width" title="Toggle Modal Width">
                    <i class="ki-duotone ki-maximize fs-2 text-info" id="width-icon">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>
                <!--end::Toggle Width Button-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary close" data-kt-users-modal-action="close" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <div class="d-block  overflow-auto" style="max-height: 550px">
                    <div id="loader" class="load" style="display: none; height: 370px;">
                        <img src="{{ asset('images/gif/loading.gif') }}" height="370" width="100%">
                    </div>
                    <div class="append" id="content">
                    </div>
                    {{--                <div class="card rounded-0 bg-light-dark ">--}}
                    {{--                </div>--}}

                </div>

            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
</div>

<style>
.modal-dialog-expanded {
    max-width: 65vw !important;
    width: 65vw !important;
}

#modal-dialog {
    transition: all 0.3s ease-in-out;
}

#toggle-modal-width {
    transition: all 0.2s ease-in-out;
}

#toggle-modal-width:hover {
    background-color: #f8f9fa !important;
    transform: scale(1.05);
}

#toggle-modal-width.expanded {
    background-color: #e3f2fd !important;
    color: #1976d2 !important;
}

#toggle-modal-width.expanded:hover {
    background-color: #bbdefb !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-modal-width');
    const modalDialog = document.getElementById('modal-dialog');
    const widthIcon = document.getElementById('width-icon');

    let isExpanded = false;

    toggleButton.addEventListener('click', function() {
        // Add click animation
        toggleButton.style.transform = 'scale(0.95)';
        setTimeout(() => {
            toggleButton.style.transform = '';
        }, 150);

        if (isExpanded) {
            // Collapse to normal size
            modalDialog.classList.remove('modal-dialog-expanded');
            modalDialog.classList.add('mw-650px');

            // Update icon and button state
            widthIcon.innerHTML = '<span class="path1"></span><span class="path2"></span>';
            widthIcon.className = 'ki-duotone ki-maximize fs-2  text-info';
            toggleButton.classList.remove('expanded');
            toggleButton.title = 'Expand Modal Width';

            isExpanded = false;
        } else {
            // Expand to large size
            modalDialog.classList.remove('mw-650px');
            modalDialog.classList.add('modal-dialog-expanded');

            // Update icon and button state
            widthIcon.innerHTML = '<span class="path1"></span><span class="path2"></span>';
            widthIcon.className = 'ki-duotone ki-arrow-down-left fs-2 text-info';
            toggleButton.classList.add('expanded');
            toggleButton.title = 'Collapse Modal Width';

            isExpanded = true;
        }
    });
});
</script>

