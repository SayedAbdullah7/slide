<!--begin::Mixed Widget 17-->
<div class="card card-xl-stretch mb-xl-8">
    <!--begin::Body-->
    <div class="card-body pt-5">
        <!--begin::Heading-->
        <div class="d-flex flex-stack">
            <!--begin::Title-->
            <h4 class="fw-bold text-gray-800 m-0">{{ $title }}</h4>
            <!--end::Title-->
            <!--begin::Menu-->
            @if(!empty($menuItems))
            <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-category fs-6">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
            </button>
            <!--begin::Menu 3-->
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                <!--begin::Heading-->
                @if(isset($menuItems['heading']))
                <div class="menu-item px-3">
                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">{{ $menuItems['heading'] }}</div>
                </div>
                @endif
                <!--end::Heading-->

                @foreach($menuItems['items'] as $item)
                    @if(isset($item['separator']) && $item['separator'])
                    <!--begin::Menu item-->
                    <div class="menu-item px-3 my-1">
                        <a href="{{ $item['url'] }}" class="menu-link px-3">{{ $item['text'] }}</a>
                    </div>
                    <!--end::Menu item-->
                    @elseif(isset($item['submenu']))
                    <!--begin::Menu item-->
                    <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-end">
                        <a href="{{ $item['url'] }}" class="menu-link px-3">
                            <span class="menu-title">{{ $item['text'] }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            @foreach($item['submenu'] as $subItem)
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="{{ $subItem['url'] }}" class="menu-link px-3">{{ $subItem['text'] }}</a>
                            </div>
                            <!--end::Menu item-->
                            @endforeach

                            @if(isset($item['switch']))
                            <!--begin::Menu separator-->
                            <div class="separator my-2"></div>
                            <!--end::Menu separator-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <div class="menu-content px-3">
                                    <!--begin::Switch-->
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <!--begin::Input-->
                                        <input class="form-check-input w-30px h-20px" type="checkbox" value="1" {{ $item['switch']['checked'] ? 'checked="checked"' : '' }} name="{{ $item['switch']['name'] }}" />
                                        <!--end::Input-->
                                        <!--end::Label-->
                                        <span class="form-check-label text-muted fs-6">{{ $item['switch']['label'] }}</span>
                                        <!--end::Label-->
                                    </label>
                                    <!--end::Switch-->
                                </div>
                            </div>
                            <!--end::Menu item-->
                            @endif
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->
                    @else
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="{{ $item['url'] }}" class="menu-link {{ isset($item['tooltip']) ? 'flex-stack' : '' }} px-3">
                            {{ $item['text'] }}
                            @if(isset($item['tooltip']))
                            <span class="ms-2" data-bs-toggle="tooltip" title="{{ $item['tooltip'] }}">
                                <i class="ki-duotone ki-information fs-6">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            @endif
                        </a>
                    </div>
                    <!--end::Menu item-->
                    @endif
                @endforeach
            </div>
            <!--end::Menu 3-->
            @endif
            <!--end::Menu-->
        </div>
        <!--end::Heading-->

        <!--begin::Chart-->
        <div class="d-flex flex-center w-100">
            <div class="mixed-widget-17-chart" data-kt-chart-color="{{ $chartColor }}" style="height: {{ $chartHeight }}px" data-modal-widget="true"></div>
        </div>
        <!--end::Chart-->

        <!--begin::Content-->
        <div class="text-center w-100 position-relative z-index-1" style="margin-top: -130px">
            <!--begin::Text-->
            <p class="fw-semibold fs-4 text-gray-500 mb-6">{{ $description }}</p>
            <!--end::Text-->
            <!--begin::Action-->
            <div class="mb-9 mb-xxl-1">
                <a href="{{ $buttonAction }}" class="btn {{ $buttonClass }} fw-semibold" data-bs-toggle="modal" data-bs-target="#kt_modal_invite_friends">{{ $buttonText }}</a>
            </div>
            <!--end::Action-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Body-->

    <!--begin::Footer-->
    @if(!empty($legendItems))
    <div class="card-footer d-flex flex-center py-5">
        @foreach($legendItems as $index => $legendItem)
        <!--begin::Item-->
        <div class="d-flex align-items-center flex-shrink-0 {{ $index > 0 ? '' : 'me-7 me-lg-12' }}">
            <!--begin::Bullet-->
            <span class="bullet bullet-dot bg-{{ $legendItem['color'] }} me-2 h-10px w-10px"></span>
            <!--end::Bullet-->
            <!--begin::Label-->
            <span class="fw-semibold text-gray-500 fs-6">{{ $legendItem['label'] }}</span>
            <!--end::Label-->
        </div>
        <!--end::Item-->
        @endforeach
    </div>
    @endif
    <!--end::Footer-->
</div>
<!--end::Mixed Widget 17-->

@push('scripts')
<script>
// Initialize mixed widget when loaded in modal
document.addEventListener('DOMContentLoaded', function() {
    // Check if this widget is in a modal
    const widget = document.querySelector('.mixed-widget-17-chart[data-modal-widget="true"]');
    if (widget && widget.closest('.modal')) {
        // Initialize the chart for modal content
        setTimeout(() => {
            if (typeof window.initializeModalContent === 'function') {
                window.initializeModalContent(widget.closest('.modal'));
            }
        }, 100);
    }
});
</script>
@endpush
