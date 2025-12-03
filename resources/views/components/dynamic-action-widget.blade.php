@props([
    'title' => 'Action Needed',
    'subtitle' => 'Complete your profile setup',
    'chartColor' => 'primary', // primary, success, danger, warning, info
    'buttonColor' => 'primary', // primary, success, danger, warning, info
    'buttonText' => 'Take Action',
    'buttonUrl' => '#',
    'note' => 'Current sprint requires stakeholders to approve newly amended policies',
    'noteBadge' => 'Notes',
    'noteBadgeColor' => 'danger',
    'chartHeight' => '200px',
    'class' => 'col-xl-4',
    'showFilter' => true,
    'filterOptions' => []
])

<div class="{{ $class }}">
    <!--begin::Mixed Widget-->
    <div class="card card-xl-stretch mb-xl-8">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">{{ $title }}</span>
                <span class="text-muted fw-semibold fs-7">{{ $subtitle }}</span>
            </h3>

            @if($showFilter && !empty($filterOptions) && count($filterOptions) > 0)
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary"
                            data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-category fs-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </button>

                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true">
                        <!--begin::Header-->
                        <div class="px-7 py-5">
                            <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                        </div>
                        <!--end::Header-->

                        <!--begin::Menu separator-->
                        <div class="separator border-gray-200"></div>
                        <!--end::Menu separator-->

                        <!--begin::Form-->
                        <div class="px-7 py-5">
                            @if(isset($filterOptions['status']))
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold">{{ $filterOptions['status']['label'] }}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div>
                                        <select class="form-select form-select-solid" multiple="multiple"
                                                data-kt-select2="true" data-close-on-select="false"
                                                data-placeholder="Select option" data-allow-clear="true">
                                            <option></option>
                                            @foreach($filterOptions['status']['options'] as $option)
                                                <option value="{{ $loop->iteration }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                            @endif

                            @if(isset($filterOptions['memberType']))
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold">{{ $filterOptions['memberType']['label'] }}</label>
                                    <!--end::Label-->
                                    <!--begin::Options-->
                                    <div class="d-flex">
                                        @foreach($filterOptions['memberType']['options'] as $label => $checked)
                                            <label class="form-check form-check-sm form-check-custom form-check-solid {{ !$loop->last ? 'me-5' : '' }}">
                                                <input class="form-check-input" type="checkbox" value="{{ $loop->iteration }}"
                                                       {{ $checked ? 'checked="checked"' : '' }} />
                                                <span class="form-check-label">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <!--end::Options-->
                                </div>
                                <!--end::Input group-->
                            @endif

                            @if(isset($filterOptions['notifications']))
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold">{{ $filterOptions['notifications']['label'] }}</label>
                                    <!--end::Label-->
                                    <!--begin::Switch-->
                                    <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="" name="notifications"
                                               {{ $filterOptions['notifications']['enabled'] ? 'checked="checked"' : '' }} />
                                        <label class="form-check-label">Enabled</label>
                                    </div>
                                    <!--end::Switch-->
                                </div>
                                <!--end::Input group-->
                            @endif

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2"
                                        data-kt-menu-dismiss="true">Reset</button>
                                <button type="submit" class="btn btn-sm btn-primary"
                                        data-kt-menu-dismiss="true">Apply</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Form-->
                    </div>
                    <!--end::Menu-->
                </div>
            @endif
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column">
            <div class="flex-grow-1">
                <div class="mixed-widget-4-chart"
                     data-kt-chart-color="{{ $chartColor }}"
                     style="height: {{ $chartHeight }}"></div>
            </div>
            <div class="pt-5">
                @if($note)
                    <p class="text-center fs-6 pb-5">
                        <span class="badge badge-light-{{ $noteBadgeColor }} fs-8">{{ $noteBadge }}:</span>&nbsp; {{ $note }}
                    </p>
                @endif
                <a href="{{ $buttonUrl }}" class="btn btn-{{ $buttonColor }} w-100 py-3">{{ $buttonText }}</a>
            </div>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Mixed Widget-->
</div>
