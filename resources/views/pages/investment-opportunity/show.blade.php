@php
    $isEdit = isset($model);
    $opportunity = $investmentOpportunity ?? null;
@endphp




<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

        <!-- Show Opportunity ID -->
    <x-group-input-text
    label="Opportunity ID"
    name="opportunity_id"
    :value="$investmentOpportunity->id"
    readonly
    />


    <!-- Basic Information -->
    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Opportunity Name"
                name="name"
                :value="$investmentOpportunity->name"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Location"
                name="location"
                :value="$investmentOpportunity->location"
                readonly
            />
        </div>
    </div>

    <div class="fv-row mb-7">
        <label class="fw-semibold fs-6 mb-2">Description</label>
        <div class="form-control form-control-solid" style="min-height: 100px;">
            {{ $investmentOpportunity->description }}
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Category</label>
                <div class="form-control form-control-solid">
                    {{ $investmentOpportunity->category->name ?? 'N/A' }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Owner</label>
                <div class="form-control form-control-solid">
                    {{ $investmentOpportunity->ownerProfile->user->display_name ?? 'N/A' }}
                    @if($investmentOpportunity->ownerProfile->business_name)
                        <small class="text-muted d-block">{{ $investmentOpportunity->ownerProfile->business_name }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Status</label>
                <div class="mt-2">
                    @php
                        $color = \App\InvestmentStatusEnum::color($investmentOpportunity->status);
                        $label = \App\InvestmentStatusEnum::label($investmentOpportunity->status);
                    @endphp
                    <span class="badge badge-{{ $color }}">{{ $label }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Risk Level</label>
                <div class="mt-2">
                    @if($investmentOpportunity->risk_level)
                        @php
                            $color = \App\RiskLevelEnum::color($investmentOpportunity->risk_level);
                            $text = \App\RiskLevelEnum::text($investmentOpportunity->risk_level);
                        @endphp
                        <span class="badge badge-{{ $color }}">{{ $text }}</span>
                    @else
                        <span class="badge badge-light">Not Set</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Information -->
    <div class="separator separator-dashed my-10"></div>
    <h4 class="mb-7">Financial Information</h4>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Target Amount"
                name="target_amount"
                :value="'$' . number_format($investmentOpportunity->target_amount, 2)"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Price Per Share"
                name="share_price"
                :value="'$' . number_format($investmentOpportunity->share_price, 2)"
                readonly
            />
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Total Shares"
                name="reserved_shares"
                :value="number_format($investmentOpportunity->reserved_shares)"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Available Shares"
                name="available_shares"
                :value="number_format($investmentOpportunity->available_shares)"
                readonly
            />
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Min Investment"
                name="min_investment"
                :value="number_format($investmentOpportunity->min_investment)"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Max Investment"
                name="max_investment"
                :value="$investmentOpportunity->max_investment ? number_format($investmentOpportunity->max_investment) : 'No Limit'"
                readonly
            />
        </div>
    </div>

    <!-- Progress Information -->
    <div class="row mb-7">
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Completion Rate</label>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar {{ $investmentOpportunity->completion_rate >= 100 ? 'bg-success' : ($investmentOpportunity->completion_rate >= 75 ? 'bg-warning' : 'bg-danger') }}"
                         style="width: {{ $investmentOpportunity->completion_rate }}%">
                        {{ number_format($investmentOpportunity->completion_rate, 1) }}%
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Status</label>
                <div class="mt-2">
                    @if($investmentOpportunity->is_completed)
                        <span class="badge badge-success">Completed</span>
                    @elseif($investmentOpportunity->is_fundable)
                        <span class="badge badge-primary">Fundable</span>
                    @else
                        <span class="badge badge-secondary">Not Fundable</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Expected Returns -->
    @if($investmentOpportunity->expected_profit || $investmentOpportunity->expected_net_profit)
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">Expected Returns Per Share</h4>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Expected Profit Per Share"
                    name="expected_profit"
                    :value="$investmentOpportunity->expected_profit ? '$' . number_format($investmentOpportunity->expected_profit, 2) : 'N/A'"
                    readonly
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Expected Net Profit Per Share"
                    name="expected_net_profit"
                    :value="$investmentOpportunity->expected_net_profit ? '$' . number_format($investmentOpportunity->expected_net_profit, 2) : 'N/A'"
                    readonly
                />
            </div>
        </div>
    @endif

    <!-- Additional Information -->
    <div class="separator separator-dashed my-10"></div>
    <h4 class="mb-7">Additional Information</h4>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Investment Duration"
                name="investment_duration"
                :value="$investmentOpportunity->investment_duration ? $investmentOpportunity->investment_duration . ' days' : 'N/A'"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Shipping Fee Per Share"
                name="shipping_fee_per_share"
                :value="$investmentOpportunity->shipping_fee_per_share ? '$' . number_format($investmentOpportunity->shipping_fee_per_share, 2) : 'N/A'"
                readonly
            />
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Fund Goal</label>
                <div class="form-control form-control-solid">
                    {{ $investmentOpportunity->fund_goal ? \App\FundGoalEnum::label($investmentOpportunity->fund_goal) : 'N/A' }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Visibility</label>
                <div class="mt-2">
                    @if($investmentOpportunity->show)
                        <span class="badge badge-success">Visible to Users</span>
                    @else
                        <span class="badge badge-secondary">Hidden from Users</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($investmentOpportunity->guarantee)
        <div class="fv-row mb-7">
            <label class="fw-semibold fs-6 mb-2">Guarantee</label>
            <div class="form-control form-control-solid" style="min-height: 80px;">
                {{ $investmentOpportunity->guarantee }}
            </div>
        </div>
    @endif

    <!-- Actual Returns -->
    @if($investmentOpportunity->actual_profit_per_share || $investmentOpportunity->actual_net_profit_per_share || $investmentOpportunity->distributed_profit)
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">Actual Returns</h4>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Actual Profit Per Share"
                    name="actual_profit_per_share"
                    :value="$investmentOpportunity->actual_profit_per_share ? '$' . number_format($investmentOpportunity->actual_profit_per_share, 2) : 'N/A'"
                    readonly
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Actual Net Profit Per Share"
                    name="actual_net_profit_per_share"
                    :value="$investmentOpportunity->actual_net_profit_per_share ? '$' . number_format($investmentOpportunity->actual_net_profit_per_share, 2) : 'N/A'"
                    readonly
                />
            </div>
        </div>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Distributed Profit"
                    name="distributed_profit"
                    :value="$investmentOpportunity->distributed_profit ? '$' . number_format($investmentOpportunity->distributed_profit, 2) : 'N/A'"
                    readonly
                />
            </div>
        </div>
    @endif

    <!-- Delivery & Distribution Status -->
    <div class="separator separator-dashed my-10"></div>
    <h4 class="mb-7">Delivery & Distribution Status</h4>
    @php
        $countOfNotDistributedAuthorizeInvestments = $investmentOpportunity->countInvestmentsNotDistributedAuthorize();
        $countOfTotalAuthorizeInvestments = $investmentOpportunity->investmentsAuthorize()->count();

        $countOfNotArrivedMyselfInvestments = $investmentOpportunity->countInvestmentsNotArrivedMyself();
        $countOfTotalMyselfInvestments = $investmentOpportunity->investmentsMyself()->count();
    @endphp
    <!-- table for show all info total myself and total authorize and not distributed authorize and not arrived myself and all merchandise delivered and all returns distributed -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>استثمارات بنفسي</th>
                <th>استثمارات مفوضة</th>
                <th>لم يتم توزيع العوائد</th>
                <th>لم يتم تسليم البضائع</th>
                <th>تم التسليم</th>
                <th>تم التوزيع</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $countOfTotalMyselfInvestments }}</td>
                <td>{{ $countOfTotalAuthorizeInvestments }}</td>
                <td>{{ $countOfNotDistributedAuthorizeInvestments }}</td>
                <td>{{ $countOfNotArrivedMyselfInvestments }}</td>
                <td>{{ $investmentOpportunity->all_merchandise_delivered ? 'تم التسليم' : 'في الانتظار' }}</td>
                <td>{{ $investmentOpportunity->all_returns_distributed ? 'تم التوزيع' : 'في الانتظار' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Mixed Widgets for Distribution Status -->
    <div class="row g-5 g-xl-8 mb-7">
        <!-- Merchandise Delivery Widget -->
        <div class="col-md-6">
            <x-investment-action-button
                type="merchandise"
                :isActive="$countOfNotArrivedMyselfInvestments > 0 && !$investmentOpportunity->all_merchandise_delivered"
                :isCompleted="$investmentOpportunity->all_merchandise_delivered"
                :totalCount="$countOfTotalMyselfInvestments"
                :pendingCount="$countOfNotArrivedMyselfInvestments"
                actionUrl="{{ route('admin.investment-opportunities.process-merchandise-delivery', $investmentOpportunity->id) }}"
            />
        </div>

        <!-- Distributed Profit Widget -->
        <div class="col-md-6">

            <x-investment-action-button
                type="returns"
                :isActive="$countOfNotDistributedAuthorizeInvestments > 0 && !$investmentOpportunity->all_returns_distributed"
                :isCompleted="$investmentOpportunity->all_returns_distributed"
                :totalCount="$countOfTotalAuthorizeInvestments"
                :pendingCount="$countOfNotDistributedAuthorizeInvestments"
                actionUrl="{{ route('admin.investment-opportunities.distribute-returns', $investmentOpportunity->id) }}"
            />


            {{-- <x-mixed-widget
                title="العوائد الموزعة"
                description="متابعة توزيع الأرباح للمستثمرين في هذه الفرصة"
                button-text="{{ $investmentOpportunity->all_returns_distributed ? 'تم التوزيع' : 'توزيع العوائد' }}"
                button-class="{{ $investmentOpportunity->all_returns_distributed ? 'btn-success' : 'btn-primary' }}"
                button-action="#"
                :chart-height="250"
                chart-color="{{ $investmentOpportunity->all_returns_distributed ? 'success' : 'primary' }}"
                :legend-items="[
                    ['color' => 'success', 'label' => 'موزع: $' . number_format($investmentOpportunity->investments->sum('distributed_profit'), 0)],
                    ['color' => 'warning', 'label' => 'متبقي: $' . number_format($investmentOpportunity->investments->sum('total_investment') - $investmentOpportunity->investments->sum('distributed_profit'), 0)],
                    ['color' => 'info', 'label' => 'إجمالي: $' . number_format($investmentOpportunity->investments->sum('total_investment'), 0)]
                ]"
                :menu-items="[
                    'heading' => 'إدارة العوائد',
                    'items' => [
                        ['text' => 'تسجيل ربح فعلي', 'url' => '#'],
                        ['text' => 'توزيع العوائد', 'url' => '#'],
                        ['text' => 'عرض تقرير التوزيع', 'url' => '#'],
                        [
                            'text' => 'إعدادات التوزيع',
                            'url' => '#',
                            'submenu' => [
                                ['text' => 'جدولة التوزيع', 'url' => '#'],
                                ['text' => 'تحديد النسب', 'url' => '#'],
                                ['text' => 'إشعارات التوزيع', 'url' => '#']
                            ],
                            'switch' => [
                                'name' => 'auto_distribution',
                                'label' => 'توزيع تلقائي',
                                'checked' => false
                            ]
                        ],
                        ['text' => 'سجل التوزيعات', 'url' => '#', 'separator' => true]
                    ]
                ]"
            /> --}}
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Merchandise Delivered</label>
                <div class="mt-2">
                    @if($investmentOpportunity->all_merchandise_delivered)
                        <span class="badge badge-success">All Delivered</span>
                    @else
                        <span class="badge badge-warning">Pending Delivery</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-semibold fs-6 mb-2">Returns Distributed</label>
                <div class="mt-2">
                    @if($investmentOpportunity->all_returns_distributed)
                        <span class="badge badge-success">All Distributed</span>
                    @else
                        <span class="badge badge-warning">Pending Distribution</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Important Dates -->
    <div class="separator separator-dashed my-10"></div>
    <h4 class="mb-7">Important Dates</h4>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Show Date"
                name="show_date"
                :value="$investmentOpportunity->show_date ? $investmentOpportunity->show_date->format('Y-m-d H:i:s') : 'N/A'"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Offering Start Date"
                name="offering_start_date"
                :value="$investmentOpportunity->offering_start_date ? $investmentOpportunity->offering_start_date->format('Y-m-d H:i:s') : 'N/A'"
                readonly
            />
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Offering End Date"
                name="offering_end_date"
                :value="$investmentOpportunity->offering_end_date ? $investmentOpportunity->offering_end_date->format('Y-m-d H:i:s') : 'N/A'"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Profit Distribution Date"
                name="profit_distribution_date"
                :value="$investmentOpportunity->profit_distribution_date ? $investmentOpportunity->profit_distribution_date->format('Y-m-d H:i:s') : 'N/A'"
                readonly
            />
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Expected Delivery Date"
                name="expected_delivery_date"
                :value="$investmentOpportunity->expected_delivery_date ? $investmentOpportunity->expected_delivery_date->format('Y-m-d H:i:s') : 'N/A'"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Expected Distribution Date"
                name="expected_distribution_date"
                :value="$investmentOpportunity->expected_distribution_date ? $investmentOpportunity->expected_distribution_date->format('Y-m-d H:i:s') : 'N/A'"
                readonly
            />
        </div>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="Created At"
                name="created_at"
                :value="$investmentOpportunity->created_at->format('Y-m-d H:i:s')"
                readonly
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="Updated At"
                name="updated_at"
                :value="$investmentOpportunity->updated_at->format('Y-m-d H:i:s')"
                readonly
            />
        </div>
    </div>

    <!-- Admin Information & Actions -->
    {{-- @if(auth()->user()->hasRole('admin')) --}}
    @if(auth()->user())
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">معلومات إدارية عن الفرصة</h4>

        <!-- Opportunity Management Cards -->
        <div class="row g-5 g-xl-8 mb-7">
            <!-- Investment Lifecycle Status -->
            <x-admin-stats-card
                title="تفاصيل الاستثمارات"
                :value="$investmentOpportunity->investments->count()"
                subtitle="إجمالي الاستثمارات"
                :badges="[
                    ['text' => 'نفسي: ' . $investmentOpportunity->investments()->where('investment_type', 'myself')->count(), 'color' => 'success'],
                    ['text' => 'مفوض: ' . $investmentOpportunity->investments()->where('investment_type', 'authorize')->count(), 'color' => 'primary']
                ]"
                color="success"
            />

            <!-- Financial Summary -->
            <x-admin-stats-card
                title="الملخص المالي"
                :value="'$' . number_format($investmentOpportunity->investments->sum('total_investment'), 0)"
                subtitle="إجمالي الاستثمارات"
                :badges="[
                    ['text' => 'موزع: $' . number_format($investmentOpportunity->investments->sum('distributed_profit'), 0), 'color' => 'success']
                ]"
                color="primary"
            />

            <!-- Completion Rate -->
            <x-admin-stats-card
                title="معدل الإكمال"
                :value="number_format($investmentOpportunity->completion_rate, 1) . '%'"
                subtitle="نسبة الإكمال"
                :progress="$investmentOpportunity->completion_rate"
                :progressColor="$investmentOpportunity->completion_rate >= 100 ? 'success' : ($investmentOpportunity->completion_rate >= 75 ? 'warning' : 'danger')"
                color="warning"
            />
        </div>

        <!-- Admin Actions -->
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">إدارة الفرصة</h4>

        <!-- Test Modal Widgets Buttons -->
        <!-- <div class="row g-5 g-xl-8 mb-7">
            <div class="col-md-6">
                <button type="button" class="btn btn-primary" onclick="loadModalContent('{{ route('admin.modal.investment-widgets') }}?opportunity_id={{ $investmentOpportunity->id }}')">
                    <i class="ki-duotone ki-category fs-4 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    عرض Investment Widgets في Modal
                </button>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-success" onclick="loadModalContent('{{ route('admin.modal.mixed-widget-demo') }}?opportunity_id={{ $investmentOpportunity->id }}')">
                    <i class="ki-duotone ki-chart-simple fs-4 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    عرض Mixed Widget في Modal
                </button>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-7">
            <!-- Merchandise Management -->
            <!--  <x-admin-action-card
                title="إدارة البضائع"
                icon="ki-package"
                iconColor="primary"
                :status="!$investmentOpportunity->all_merchandise_delivered"
                :statusText="$investmentOpportunity->all_merchandise_delivered ? 'تم التسليم' : 'في الانتظار'"
                :statusColor="$investmentOpportunity->all_merchandise_delivered ? 'success' : 'warning'"
                :actions="array_merge(
                    !$investmentOpportunity->all_merchandise_delivered ? [
                        [
                            'text' => 'وضع علامة وصول البضائع',
                            'color' => 'primary',
                            'icon' => 'ki-check',
                            'type' => 'ajax',
                            'url' => route('admin.investment-opportunities.process-merchandise-delivery', $investmentOpportunity->id),
                            'method' => 'POST',
                            'confirm' => true,
                            'confirm_text' => 'هل أنت متأكد من وضع علامة وصول البضائع لهذه الفرصة؟'
                        ]
                    ] : [],
                    [
                        [
                            'text' => 'عرض حالة البضائع',
                            'color' => 'light-info',
                            'icon' => 'ki-eye',
                            'type' => 'modal',
                            'url' => route('admin.investment-opportunities.merchandise-status', $investmentOpportunity->id),
                            'modal_type' => 'show'
                        ]
                    ]
                )"
            /> -->

            <!-- Returns Management -->
            <!-- <x-admin-action-card
                title="إدارة العوائد"
                icon="ki-chart-simple"
                iconColor="success"
                :status="!$investmentOpportunity->all_returns_distributed"
                :statusText="$investmentOpportunity->all_returns_distributed ? 'تم التوزيع' : 'في الانتظار'"
                :statusColor="$investmentOpportunity->all_returns_distributed ? 'success' : 'warning'"
                :actions="array_merge(
                    !$investmentOpportunity->all_returns_distributed ? [
                        [
                            'text' => 'تسجيل الربح الفعلي',
                            'color' => 'success',
                            'icon' => 'ki-pencil',
                            'type' => 'modal',
                            'url' => route('admin.investment-opportunities.record-actual-profit', $investmentOpportunity->id),
                            'modal_type' => 'show'
                        ],
                        [
                            'text' => 'توزيع العوائد',
                            'color' => 'warning',
                            'icon' => 'ki-arrow-right',
                            'type' => 'ajax',
                            'url' => route('admin.investment-opportunities.distribute-returns', $investmentOpportunity->id),
                            'method' => 'POST',
                            'confirm' => true,
                            'confirm_text' => 'هل أنت متأكد من توزيع العوائد لهذه الفرصة؟'
                        ]
                    ] : [],
                    [
                        [
                            'text' => 'عرض حالة العوائد',
                            'color' => 'light-info',
                            'icon' => 'ki-eye',
                            'type' => 'modal',
                            'url' => route('admin.investment-opportunities.returns-status', $investmentOpportunity->id),
                            'modal_type' => 'show'
                        ]
                    ]
                )"
            /> -->
        </div>

        <!-- Investment Statistics -->
        <div class="row g-5 g-xl-8 mb-7">
            <x-investment-statistics
                :statistics="[
                    [
                        'label' => 'استثمارات البيع بنفسي',
                        'value' => $investmentOpportunity->investments()->where('investment_type', 'myself')->count(),
                        'textColor' => 'gray-900',
                        'badge' => [
                            'text' => '$' . number_format($investmentOpportunity->investments()->where('investment_type', 'myself')->sum('total_investment'), 0),
                            'color' => 'success',
                            'subtext' => 'إجمالي المبلغ'
                        ]
                    ],
                    [
                        'label' => 'استثمارات مفوضة',
                        'value' => $investmentOpportunity->investments()->where('investment_type', 'authorize')->count(),
                        'textColor' => 'gray-900',
                        'badge' => [
                            'text' => '$' . number_format($investmentOpportunity->investments()->where('investment_type', 'authorize')->sum('total_investment'), 0),
                            'color' => 'primary',
                            'subtext' => 'إجمالي المبلغ'
                        ]
                    ],
                    [
                        'label' => 'العوائد الموزعة',
                        'value' => '$' . number_format($investmentOpportunity->investments->sum('distributed_profit'), 0),
                        'textColor' => 'success',
                        'badge' => [
                            'text' => 'من إجمالي $' . number_format($investmentOpportunity->investments->sum('total_investment'), 0),
                            'color' => 'info'
                        ]
                    ],
                    [
                        'label' => 'المتبقي للتوزيع',
                        'value' => '$' . number_format($investmentOpportunity->investments->sum('total_investment') - $investmentOpportunity->investments->sum('distributed_profit'), 0),
                        'textColor' => 'warning',
                        'badge' => [
                            'text' => 'مبلغ معلق',
                            'color' => 'warning'
                        ]
                    ]
                ]"
            />
        </div>
    @endif

    <!-- Investments -->
    @if($investmentOpportunity->investments->count() > 0)
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">Investments ({{ $investmentOpportunity->investments->count() }})</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Investor</th>
                        <th>Amount</th>
                        <th>Shares</th>
                        <th>Invested At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($investmentOpportunity->investments as $investment)
                        <tr>
                            <td>{{ $investment->investorProfile->user->display_name ?? 'N/A' }}</td>
                            <td>${{ number_format($investment->getTotal(), 2) }}</td>

                            <td>{{ number_format($investment->shares) }}</td>
                            <td>{{ $investment->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @switch($investment->status)
                                    @case('active')
                                        <span class="badge badge-success">Active</span>
                                        @break
                                    @case('completed')
                                        <span class="badge badge-info">Completed</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                        @break
                                    @default
                                        <span class="badge badge-light">{{ ucfirst($investment->status) }}</span>
                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>

<!-- Actions -->
<div class="text-center pt-10">
    <button type="button" class="btn btn-light me-3 close" data-bs-dismiss="modal">
        {{ __('Close') }}
    </button>
    <a href="#"
       class="btn btn-primary has_action me-3"
       data-type="edit"
       data-action="{{ route('investment-opportunity.edit', $investmentOpportunity->id) }}">
        {{ __('Edit Opportunity') }}
    </a>
</div>

@push('scripts')
<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
@endpush
