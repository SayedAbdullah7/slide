/**
 * ========================================================================
 * MAIN.JS - CORE APPLICATION JAVASCRIPT
 * ========================================================================
 *
 * AI TOOL RULES & GUIDELINES - READ THIS FIRST!
 * =============================================
 *
 * When modifying or extending this file, AI tools MUST follow these rules:
 *
 * 1. **DO NOT CREATE CUSTOM HANDLERS FOR EXISTING FUNCTIONALITY**
 *    - Check if .has_action, .admin-action-btn, .delete_btn handlers exist
 *    - Always reuse existing handlers instead of creating new ones
 *    - Use data attributes (data-action, data-type, data-method, etc.)
 *
 * 2. **DO NOT ADD MODULE-SPECIFIC CODE**
 *    - Do NOT add handlers like .withdrawal-status-btn, .user-toggle-btn, etc.
 *    - Instead, use existing .admin-action-btn or .has_action classes
 *    - Pass module-specific data via data attributes
 *
 * 3. **HANDLER USAGE PATTERNS**
 *
 *    a) **.has_action** - For loading content into modals
 *       - Use for: View/Show, Edit/Form, Create/Form actions
 *       - Requires: data-type (show/edit/create) and data-action (URL)
 *       - Example: <a href="#" class="has_action" data-type="show" data-action="/route">
 *
 *    b) **.admin-action-btn** - For AJAX POST/PUT/PATCH requests with confirmation
 *       - Use for: Status updates, toggle actions, approvals, rejections
 *       - Requires: data-action (URL), data-method (POST/PUT/PATCH),
 *                   data-confirm (true), data-confirm-text (message)
 *       - Supports: Query parameters in URL (?status=pending)
 *       - Example: <a href="#" class="admin-action-btn"
 *                         data-action="/route?id=123&status=active"
 *                         data-method="POST"
 *                         data-confirm="true"
 *                         data-confirm-text="Confirm action?">
 *
 *    c) **.delete_btn** - For DELETE operations
 *       - Use for: Delete/Remove actions
 *       - Requires: data-action (URL)
 *       - Example: <a href="#" class="delete_btn" data-action="/route/123">
 *
 *    d) **#kt_modal_form** - For form submissions inside modals
 *       - Use for: Forms that submit via AJAX in modals
 *       - Requires: form action, data-method attribute
 *       - Example: <form id="kt_modal_form" action="/route" data-method="POST">
 *
 * 4. **RESPONSE FORMAT REQUIREMENTS**
 *
 *    Controllers MUST return JSON with these fields:
 *    - status: boolean (true/false)
 *    - success: boolean (true/false)
 *    - msg: string (user-friendly message)
 *    - message: string (alternative message field)
 *    - reload: boolean (optional, if true reloads page after 1 second)
 *    - errors: object (optional, for validation errors)
 *
 *    Example:
 *    {
 *        "status": true,
 *        "success": true,
 *        "msg": "Operation successful",
 *        "message": "Operation successful",
 *        "reload": false
 *    }
 *
 * 5. **VIEW FILE RULES**
 *
 *    - DO NOT create inline JavaScript functions in view files
 *    - DO NOT create onclick handlers with custom functions
 *    - DO NOT duplicate functionality that exists in main.js
 *    - USE existing classes (.has_action, .admin-action-btn, .delete_btn)
 *    - PASS data via data attributes, not JavaScript variables
 *    - IF custom logic needed, define it in a separate JS file, NOT in Blade views
 *
 * 6. **DATA ATTRIBUTES CONVENTIONS**
 *
 *    Required for .has_action:
 *    - data-type: "show" | "edit" | "create" | "index"
 *    - data-action: Full URL route
 *
 *    Required for .admin-action-btn:
 *    - data-action: Full URL (can include query params)
 *    - data-method: "POST" | "PUT" | "PATCH"
 *    - data-confirm: "true" | "false" (optional, defaults to false)
 *    - data-confirm-text: Confirmation message (required if data-confirm="true")
 *
 *    Required for .delete_btn:
 *    - data-action: Full URL route
 *
 * 7. **MODAL CONTENT HANDLING**
 *
 *    - All modal content is loaded via .has_action handler
 *    - Modal content views should NOT include <x-app-layout> wrapper
 *    - Modal content should start with <div> container for scroll-y
 *    - Content automatically initializes (Select2, tooltips, etc.) via initializeModalContent()
 *
 * 8. **DATATABLE RELOADING**
 *
 *    - All handlers automatically reload DataTables after successful operations
 *    - No manual DataTable.reload() calls needed
 *    - Reloads all tables on page: $('table').each(...)
 *
 * 9. **ERROR HANDLING**
 *
 *    - All AJAX errors go through handleAjaxError()
 *    - Validation errors displayed via showValidationErrors()
 *    - User notifications via showNotification() with SweetAlert
 *
 * 10. **TRANSLATION SUPPORT**
 *
 *     - Use t('key') function for translations
 *     - Translations object supports 'en' and 'ar' languages
 *     - Current language from document.documentElement.lang
 *
 * WHEN ADDING NEW FUNCTIONALITY:
 * ==============================
 *
 * 1. First check if existing handlers can be used
 * 2. If not, add generic handler that works for all modules
 * 3. Document new handler with usage examples
 * 4. Update this documentation section
 *
 * COMMON MISTAKES TO AVOID:
 * =========================
 *
 * ❌ DON'T: Create module-specific handlers (.withdrawal-btn, .user-btn)
 * ✅ DO: Use generic handlers (.admin-action-btn, .has_action)
 *
 * ❌ DON'T: Add inline onclick="customFunction()" in Blade views
 * ✅ DO: Use class="admin-action-btn" with data attributes
 *
 * ❌ DON'T: Create duplicate AJAX handlers for similar actions
 * ✅ DO: Reuse existing handlers and pass different data attributes
 *
 * ❌ DON'T: Add JavaScript functions in Blade template files
 * ✅ DO: Use main.js handlers or create separate JS files
 *
 * ❌ DON'T: Modify core handler logic for module-specific needs
 * ✅ DO: Handle module differences via controller/data attributes
 *
 * ========================================================================
 * END OF AI TOOL RULES
 * ========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // Translation object
    const translations = {
        en: {
            confirmDelete: "Are you sure you want to delete this item?",
            deleteSuccess: "Item deleted successfully.",
            deleteError: "An error occurred while trying to delete the item. Please try again.",
            csrfError: "CSRF token mismatch. Please refresh the page and try again.",
            genericError: "An error occurred while attempting to delete the item. Please try again.",
            ok: "Ok, got it!",
            confirm: "Confirm",
            cancel: "Cancel",
            loading: "Please wait...",
            submit: "Submit",
            discard: "Discard",
        },
        ar: {
            confirmDelete: "هل أنت متأكد أنك تريد حذف هذا العنصر؟",
            deleteSuccess: "تم حذف العنصر بنجاح.",
            deleteError: "حدث خطأ أثناء محاولة حذف العنصر. يرجى المحاولة مرة أخرى.",
            csrfError: "فشل رمز CSRF. يرجى تحديث الصفحة والمحاولة مرة أخرى.",
            genericError: "حدث خطأ أثناء محاولة حذف العنصر. يرجى المحاولة مرة أخرى.",
            ok: "حسناً، فهمت!",
            confirm: "تأكيد",
            cancel: "إلغاء",
            loading: "يرجى الانتظار...",
            submit: "إرسال",
            discard: "تجاهل",
        }
    };

    // Get current language from HTML (default to English)
    const lang = document.documentElement.lang === "ar" ? "ar" : "en";

    // Function to get translated text
    const t = (key) => translations[lang][key];

    // Function to control loader visibility
    const toggleLoader = (show) => $("#loader").toggle(show);

    // Function to control modal visibility
    const toggleModal = (show) => $("#modal-form").modal(show ? 'show' : 'hide');

    // Function to control content visibility within the modal
    const toggleContent = (show) => $('#modal-form #content').toggle(show);

    // Function to reset form validation errors
    const resetValidationErrors = () => {
        $('.invalid-feedback').text('').hide();
        $('.form-control').removeClass('is-invalid');
    };

    // Function to initialize new elements in modal content
    const initializeModalContent = (container = '#modal-form #content') => {
        const $container = $(container);

        // Initialize Select2 for new select elements
        $container.find('select[data-kt-select2]').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal')
                });
            }
        });

        // Initialize KTMenu for new menu elements
        $container.find('[data-kt-menu-trigger]').each(function() {
            if (!$(this).data('kt-menu-initialized')) {
                // Initialize menu if KTMenu is available
                if (typeof KTMenu !== 'undefined') {
                    KTMenu.init();
                    $(this).data('kt-menu-initialized', true);
                }
            }
        });

        // Initialize charts for new chart elements
        $container.find('.mixed-widget-4-chart').each(function() {
            if (!$(this).data('chart-initialized')) {
                const color = $(this).data('kt-chart-color') || 'primary';
                initializeSimpleChart($(this), color);
                $(this).data('chart-initialized', true);
            }
        });

        // // Initialize mixed-widget-17-chart for new mixed widgets // case duplicate
        // $container.find('.mixed-widget-17-chart').each(function() {
        //     if (!$(this).data('chart-initialized')) {
        //         const color = $(this).data('kt-chart-color') || 'primary';
        //         initializeMixedWidgetChart($(this), color);
        //         $(this).data('chart-initialized', true);
        //     }

        // });

        // Initialize tooltips for new elements
        $container.find('[data-bs-toggle="tooltip"]').each(function() {
            if (!$(this).data('bs-tooltip-initialized')) {
                new bootstrap.Tooltip(this);
                $(this).data('bs-tooltip-initialized', true);
            }
        });

        // Initialize popovers for new elements
        $container.find('[data-bs-toggle="popover"]').each(function() {
            if (!$(this).data('bs-popover-initialized')) {
                new bootstrap.Popover(this);
                $(this).data('bs-popover-initialized', true);
            }
        });



                    // إعادة تهيئة الرسوم البيانية
            // إعادة تهيئة الرسوم البيانية لعناصر mixed-widget-17-chart
            // const chartElements = document.querySelectorAll('.mixed-widget-17-chart');
            // chartElements.forEach(function(chart) {
            //     if (chart && !chart.hasAttribute('data-kt-chart-initialized')) {
            //         initializeMixedWidget17Chart(chart);
            //         chart.setAttribute('data-kt-chart-initialized', 'true');
            //     }
            // });
    };

// دالة مخصصة لتهيئة الرسم البياني
function initializeMixedWidget17Chart(element) {
    if (!element) return;

    var height = parseInt(KTUtil.css(element, "height"));
    var color = element.getAttribute("data-kt-chart-color");

    var options = {
        labels: ["Total Orders"],
        series: [75],
        chart: {
            fontFamily: "inherit",
            height: height,
            type: "radialBar",
            offsetY: 0,
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                hollow: { margin: 0, size: "55%" },
                dataLabels: {
                    showOn: "always",
                    name: {
                        show: true,
                        fontSize: "12px",
                        fontWeight: "700",
                        offsetY: -5,
                        color: KTUtil.getCssVariableValue("--bs-gray-500"),
                    },
                    value: {
                        color: KTUtil.getCssVariableValue("--bs-gray-900"),
                        fontSize: "24px",
                        fontWeight: "600",
                        offsetY: -40,
                        show: true,
                        formatter: function (val) {
                            return "8,346";
                        },
                    },
                },
                track: {
                    background: KTUtil.getCssVariableValue("--bs-gray-300"),
                    strokeWidth: "100%",
                },
            },
        },
        colors: [KTUtil.getCssVariableValue("--bs-" + color)],
        stroke: { lineCap: "round" },
    };

    new ApexCharts(element, options).render();
};

    // Function to initialize simple chart for modal content
    const initializeSimpleChart = ($element, color) => {
        const progressValue = Math.floor(Math.random() * 40) + 60; // Random value between 60-100

        $element.html(`
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <svg width="120" height="120" class="progress-ring">
                            <circle cx="60" cy="60" r="50"
                                    fill="none"
                                    stroke="#f3f6f9"
                                    stroke-width="8"/>
                            <circle cx="60" cy="60" r="50"
                                    fill="none"
                                    stroke="var(--bs-${color})"
                                    stroke-width="8"
                                    stroke-dasharray="${2 * 3.14159 * 50}"
                                    stroke-dashoffset="${2 * 3.14159 * 50 * (1 - progressValue / 100)}"
                                    stroke-linecap="round"
                                    transform="rotate(-90 60 60)"
                                    style="transition: stroke-dashoffset 0.5s ease-in-out;"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <span class="fs-2x fw-bold text-${color}">${progressValue}%</span>
                        </div>
                    </div>
                    <div class="fs-6 text-gray-600">Completion Rate</div>
                    <div class="progress mt-3" style="width: 200px; margin: 0 auto;">
                        <div class="progress-bar bg-${color}"
                             style="width: ${progressValue}%"
                             role="progressbar"
                             aria-valuenow="${progressValue}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        `);
    };

    // Function to initialize mixed widget chart for modal content
    const initializeMixedWidgetChart = ($element, color) => {
        const progressValue = Math.floor(Math.random() * 40) + 60; // Random value between 60-100

        $element.html(`
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <!-- Large Progress Circle -->
                    <div class="position-relative d-inline-block mb-4">
                        <svg width="200" height="200" class="progress-ring">
                            <circle cx="100" cy="100" r="85"
                                    fill="none"
                                    stroke="#f3f6f9"
                                    stroke-width="12"/>
                            <circle cx="100" cy="100" r="85"
                                    fill="none"
                                    stroke="var(--bs-${color})"
                                    stroke-width="12"
                                    stroke-dasharray="${2 * 3.14159 * 85}"
                                    stroke-dashoffset="${2 * 3.14159 * 85 * (1 - progressValue / 100)}"
                                    stroke-linecap="round"
                                    transform="rotate(-90 100 100)"
                                    style="transition: stroke-dashoffset 0.8s ease-in-out;"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <div class="fs-1 fw-bold text-${color} mb-1">${progressValue}%</div>
                            <div class="fs-7 text-gray-600">مكتمل</div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="d-flex justify-content-center gap-4 mt-3">
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-gray-800">$2.4M</div>
                            <div class="fs-8 text-gray-600">المستهدف</div>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-${color}">$1.8M</div>
                            <div class="fs-8 text-gray-600">المجمع</div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    };

    // Initialize modal event listeners
    $('#modal-form').on('shown.bs.modal', function () {
        // Initialize new content when modal is shown
        setTimeout(() => {
            initializeModalContent();
        }, 100);
    });

    $('#modal-form').on('hidden.bs.modal', function () {
        // Clean up when modal is hidden
        $('#modal-form #content').empty();
    });

    // Function to load content into modal and initialize it
    const loadModalContent = (url, data = {}) => {
        resetValidationErrors();
        toggleContent(false);
        toggleLoader(true);
        toggleModal(true);

        $.ajax({
            type: 'GET',
            url: url,
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                toggleLoader(false);
                $('#modal-form #content').html(response);
                toggleContent(true);

                // Initialize new content including widgets
                setTimeout(() => {
                    initializeModalContent();

                    // Initialize any mixed widgets specifically
                    initializeMixedWidgetsInModal();
                }, 100);
            },
            error: (xhr) => {
                toggleLoader(false);
                handleAjaxError(xhr);
            }
        });
    };

    // Function to initialize mixed widgets in modal
    const initializeMixedWidgetsInModal = () => {
        const $container = $('#modal-form #content');

        // Initialize mixed-widget-17-chart elements
        $container.find('.mixed-widget-17-chart[data-modal-widget="true"]').each(function() {
            if (!$(this).data('chart-initialized')) {
                const color = $(this).data('kt-chart-color') || 'primary';
                initializeMixedWidgetChart($(this), color);
                $(this).data('chart-initialized', true);
            }
        });
    };

    // Function to initialize all widgets in a container
    const initializeAllWidgets = (container = document) => {
        // Initialize mixed widgets
        $(container).find('.mixed-widget-17-chart').each(function() {
            if (!$(this).data('chart-initialized')) {
                const color = $(this).data('kt-chart-color') || 'primary';
                initializeMixedWidgetChart($(this), color);
                $(this).data('chart-initialized', true);
            }
        });

        // Initialize simple charts
        $(container).find('.mixed-widget-4-chart').each(function() {
            if (!$(this).data('chart-initialized')) {
                const color = $(this).data('kt-chart-color') || 'primary';
                initializeSimpleChart($(this), color);
                $(this).data('chart-initialized', true);
            }
        });
    };

    // Make functions globally available
    window.initializeModalContent = initializeModalContent;
    window.loadModalContent = loadModalContent;
    window.initializeAllWidgets = initializeAllWidgets;

    // Function to show validation errors
    const showValidationErrors = (errors) => {
        resetValidationErrors();
        $.each(errors, (key, value) => {
            const $element = $('[name="' + key + '"]');
            const $invalidFeedback = $element.siblings('.invalid-feedback');
            if ($invalidFeedback.length === 0) {
                $element.after('<div class="invalid-feedback"></div>');
            }
            $element.addClass('is-invalid');
            $element.siblings('.invalid-feedback').text(value[0]).show();
        });
    };

    // Function to handle AJAX errors and display validation messages
    const handleAjaxError = (xhr) => {
        toggleLoader(false);
        toggleContent(true);
        console.error(xhr);

        // Handle validation errors
        if (xhr.responseJSON && xhr.responseJSON.errors) {
            showValidationErrors(xhr.responseJSON.errors);
            return;
        }

        // Handle other errors
        let errorMessage = t('genericError');

        if (xhr.status === 419) {
            errorMessage = t('csrfError');
        } else if (xhr.responseJSON && xhr.responseJSON.msg) {
            errorMessage = xhr.responseJSON.msg;
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }

        showNotification(errorMessage, 'error');
    };

    // Function to show a notification with SweetAlert
    const showNotification = (message, status) => {
        Swal.fire({
            text: message,
            icon: status === 'success' ? 'success' : 'error',
            buttonsStyling: false,
            confirmButtonText: t('ok'),
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    };

    // Click handler for elements with the class 'has_action'
    $(document).on('click', '.has_action', function (e) {
        e.preventDefault();

        resetValidationErrors();
        toggleContent(false);
        toggleLoader(true);
        toggleModal(true);

        const url = $(this).data('action');
        const type = $(this).data('type');

        console.log(`Loading ${type} form: ${url}`);

        $.ajax({
            type: 'GET',
            url: url,
            success: (data) => {
                console.log(data);
                $('#modal-form #content').html(data);
                toggleLoader(false);
                toggleContent(true);
            },
            error: handleAjaxError
        });
    });

    // Submit handler for the form
    $(document).on('submit', '#kt_modal_form', function (event) {
        event.preventDefault();

        resetValidationErrors();
        toggleLoader(true);
        toggleContent(false);

        const form = $(this);
        const formUrl = form.attr('action');
        const method = form.data('method');

        console.log(`Submitting form to: ${formUrl} using method: ${method}`);

        $.ajax({
            url: formUrl,
            type: method,
            dataType: "json",
            data: form.serialize(),
            success: (data) => {
                console.log(data);
                showNotification(data.msg, data.status ? 'success' : 'error');
                toggleModal(false);

                // Reload all DataTables
                $('table').each(function() {
                    if ($.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable().ajax.reload(null, false);
                    }
                });
            },
            error: handleAjaxError
        });
    });

    // Click handler for delete buttons
    $(document).on('click', '.delete_btn', function () {
        const url = $(this).data('action');

        Swal.fire({
            text: t('confirmDelete'),
            icon: "warning",
            buttonsStyling: true,
            showCancelButton: true,
            confirmButtonText: t('confirm'), // Updated line for confirmation button
            cancelButtonText: t('cancel'),
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                console.log(url);
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (data) => {
                        console.log(data);
                        showNotification(data.msg || t('deleteSuccess'), data.status ? 'success' : 'danger');
                        // Reload the DataTable
                        $('table').each(function() {
                            if ($.fn.DataTable.isDataTable(this)) { // Check if it's a DataTable
                                $(this).DataTable().ajax.reload();
                            }
                        });
                    },
                    error: handleAjaxError
                });
            }
        });
    });

    // Click handler for admin action buttons
    $(document).on('click', '.admin-action-btn', function () {
        const url = $(this).data('action');
        const method = $(this).data('method') || 'POST';
        const confirm = $(this).data('confirm');
        const confirmText = $(this).data('confirm-text');

        console.log('url: '+ url);
        console.log('method: '+ method);
        console.log('confirm: '+ confirm);
        console.log('confirmText: '+ confirmText);

        const executeAction = () => {
            console.log('executeAction is called');
            // toggleLoader(true);

            resetValidationErrors();

            // check first there content show like #modal-form #content to works fine with action that come from table dircty not from modalform
            //var isContentShow
            var isContentShow = $('#modal-form #content').html().trim() !== '';
            // console.log('isContentShow: '+ isContentShow);
            // console.log('content: '+ $('#modal-form #content').html().trim());
            if (isContentShow) {
                toggleContent(false);
                toggleLoader(true);
                toggleModal(true);
            }
            // return;

            $.ajax({
                type: method,
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (data) => {
                    console.log(data);
                    if (isContentShow) {
                    toggleLoader(false);
                    }
                    showNotification(data.message || data.msg || t('ok'), data.success !== false ? 'success' : 'error');

                    // Reload all DataTables
                    $('table').each(function() {
                        if ($.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable().ajax.reload(null, false);
                        }
                    });

                    // Reload page if needed
                    if (data.reload) {
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: (xhr) => {
                    if (isContentShow) {
                        toggleLoader(false);
                    }
                    handleAjaxError(xhr);
                }
            });
        };

        if (confirm && confirmText) {
            Swal.fire({
                text: confirmText,
                icon: "question",
                buttonsStyling: true,
                showCancelButton: true,
                confirmButtonText: t('confirm'),
                cancelButtonText: t('cancel'),
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-secondary"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log(' result is confirmed');
                    executeAction();
                }
            });
        } else {
            executeAction();
        }
    });

    // Click handler to close the modal
    $(document).on('click', '.close', function () {
        $(this).closest('#modal-form').hide();
    });
});
