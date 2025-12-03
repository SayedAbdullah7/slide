@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('investment-opportunity.update', [$model->id])
        : route('investment-opportunity.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

    {{-- Basic Information --}}
    <div class="card card-flush mb-7">
        <div class="card-header mb-7">
            <h3 class="card-title">Basic Information</h3>
        </div>
        <div class="card-body">
            <div class="row mb-7">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Opportunity Name"
                        name="name"
                        :value="$isEdit ? $model->name : (old('name') ?? '')"
                        required
                    />
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Location"
                        name="location"
                        :value="$isEdit ? $model->location : (old('location') ?? '')"
                    />
                </div>
            </div>

            <x-group-input-textarea
                label="Description"
                name="description"
                :value="$isEdit ? $model->description : (old('description') ?? '')"
                rows="4"
                placeholder="Enter opportunity description..."
            />

            <div class="row mb-7">
                <div class="col-md-6">
                    <x-select
                        label="Category"
                        name="category_id"
                        :options="collect($categories)->mapWithKeys(fn($category) => [$category->name => $category->id])->toArray()"
                        :old="$isEdit ? $model->category_id : (old('category_id') ?? '')"
                        required
                    />
                </div>
                <div class="col-md-6">
                    <x-select
                        label="Owner Profile"
                        name="owner_profile_id"
                        :options="collect($ownerProfiles)->mapWithKeys(fn($ownerProfile) => [$ownerProfile->user->display_name . ' (' . $ownerProfile->business_name . ')' => $ownerProfile->id])->toArray()"
                        :old="$isEdit ? $model->owner_profile_id : (old('owner_profile_id') ?? '')"
                        required
                    />
                </div>
            </div>

            <div class="row mb-0">
                <div class="col-md-6">
                    <x-select
                        label="Risk Level"
                        name="risk_level"
                        :options="collect(\App\RiskLevelEnum::cases())->mapWithKeys(fn($case) => [\App\RiskLevelEnum::text($case->value) => $case->value])->toArray()"
                        :old="$isEdit ? $model->risk_level : (old('risk_level') ?? '')"
                    />
                </div>
                <div class="col-md-6">
                    <x-select
                        label="Fund Goal"
                        name="fund_goal"
                        :options="array_flip(\App\FundGoalEnum::labels())"
                        :old="$isEdit ? $model->fund_goal : (old('fund_goal') ?? '')"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Details --}}
    <div class="card card-flush mb-7">
        <div class="card-header">
            <h3 class="card-title">Financial Details</h3>
        </div>
        <div class="card-body">
            <div class="row mb-7">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Target Amount"
                        name="target_amount"
                        type="number"
                        step="0.01"
                        :value="$isEdit ? $model->target_amount : (old('target_amount') ?? '')"
                        required
                    />
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Price Per Share"
                        name="share_price"
                        type="number"
                        step="0.01"
                        :value="$isEdit ? $model->share_price : (old('share_price') ?? '')"
                        required
                    />
                </div>
            </div>

            @if($isEdit)
                <div class="row mb-7">
                    <div class="col-md-12">
                        <div class="alert alert-light d-flex align-items-center p-5">
                            <i class="ki-duotone ki-information-5 fs-2hx text-info me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-column flex-grow-1">
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="fw-bold text-gray-800">Reserved Shares:</span>
                                        <span class="fs-6 text-gray-600 ms-2">{{ number_format($model->reserved_shares) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="fw-bold text-gray-800">Total Shares:</span>
                                        <span class="fs-6 text-gray-600 ms-2">{{ number_format($model->total_shares) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="fw-bold text-gray-800">Available Shares:</span>
                                        <span class="fs-6 text-gray-600 ms-2">{{ number_format($model->available_shares) }}</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-2">These values are automatically calculated and updated when investments are made.</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row mb-7">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Min Investment (Shares)"
                        name="min_investment"
                        type="number"
                        :value="$isEdit ? $model->min_investment : (old('min_investment') ?? '')"
                        required
                    />
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Max Investment (Shares)"
                        name="max_investment"
                        type="number"
                        :value="$isEdit ? $model->max_investment : (old('max_investment') ?? '')"
                    />
                </div>
            </div>

            <div class="row mb-0">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Investment Duration (Days)"
                        name="investment_duration"
                        type="number"
                        :value="$isEdit ? $model->investment_duration : (old('investment_duration') ?? '')"
                    />
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Shipping Fee Per Share"
                        name="shipping_fee_per_share"
                        type="number"
                        step="0.01"
                        :value="$isEdit ? $model->shipping_fee_per_share : (old('shipping_fee_per_share') ?? '')"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Expected Profits --}}
    <div class="card card-flush mb-7">
        <div class="card-header">
            <h3 class="card-title">Expected Profits Per Share</h3>
        </div>
        <div class="card-body">
            <div class="row mb-0">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Expected Profit Per Share"
                        name="expected_profit"
                        type="number"
                        step="0.01"
                        :value="$isEdit ? $model->expected_profit : (old('expected_profit') ?? '')"
                    />
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Expected Net Profit Per Share"
                        name="expected_net_profit"
                        type="number"
                        step="0.01"
                        :value="$isEdit ? $model->expected_net_profit : (old('expected_net_profit') ?? '')"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Actual Profits (Set Later) --}}
    @if($isEdit)
        @php
            $canEditActualProfits = $model->canEditActualProfits();
            $hasActualProfits = $model->actual_profit_per_share !== null || $model->actual_net_profit_per_share !== null;
        @endphp

        <div class="card card-flush mb-7 {{ $hasActualProfits ? 'border-warning' : '' }}">
            <div class="card-header {{ $hasActualProfits ? 'bg-light-warning' : '' }}">
                <h3 class="card-title">Actual Profits Per Share</h3>
                <div class="card-toolbar">
                    @if($hasActualProfits)
                        <span class="badge badge-warning">Read Only - Already Set</span>
                    @else
                        <span class="badge badge-light-primary">Can Be Set Once</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($hasActualProfits)
                    <div class="alert alert-warning d-flex align-items-center p-5 mb-7">
                        <i class="ki-duotone ki-shield-tick fs-2hx text-warning me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">These values were set after the investment period ended and cannot be modified.</span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-primary d-flex align-items-center p-5 mb-7">
                        <i class="ki-duotone ki-information-5 fs-2hx text-primary me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">These fields can be set once after the investment period ends.</span>
                            <span class="text-muted fs-7">Once set, they cannot be modified to ensure data integrity.</span>
                        </div>
                    </div>
                @endif

                <div class="row mb-0">
                    <div class="col-md-6">
                        <x-group-input-text
                            label="Actual Profit Per Share"
                            name="actual_profit_per_share"
                            type="number"
                            step="0.01"
                            :value="$isEdit ? $model->actual_profit_per_share : (old('actual_profit_per_share') ?? '')"
                            :readonly="!$canEditActualProfits"
                            :disabled="!$canEditActualProfits"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-group-input-text
                            label="Actual Net Profit Per Share"
                            name="actual_net_profit_per_share"
                            type="number"
                            step="0.01"
                            :value="$isEdit ? $model->actual_net_profit_per_share : (old('actual_net_profit_per_share') ?? '')"
                            :readonly="!$canEditActualProfits"
                            :disabled="!$canEditActualProfits"
                        />
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Dates & Timeline --}}
    <div class="card card-flush mb-7">
        <div class="card-header">
            <h3 class="card-title">Dates & Timeline</h3>
        </div>
        <div class="card-body">
            <div class="row mb-7">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Show Date"
                        name="show_date"
                        type="date"
                        :value="$isEdit ? $model->show_date?->format('Y-m-d\TH:i') : (old('show_date') ?? '')"
                    />
                    <div class="form-text">When the opportunity becomes visible to users</div>
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Offering Start Date"
                        name="offering_start_date"
                        type="date"
                        :value="$isEdit ? $model->offering_start_date?->format('Y-m-d\TH:i') : (old('offering_start_date') ?? '')"
                    />
                    <div class="form-text">When investments can start</div>
                </div>
            </div>

            <div class="row mb-7">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Offering End Date"
                        name="offering_end_date"
                        type="date"
                        :value="$isEdit ? $model->offering_end_date?->format('Y-m-d\TH:i') : (old('offering_end_date') ?? '')"
                    />
                    <div class="form-text">When investments close</div>
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Profit Distribution Date"
                        name="profit_distribution_date"
                        type="date"
                        :value="$isEdit ? $model->profit_distribution_date?->format('Y-m-d\TH:i') : (old('profit_distribution_date') ?? '')"
                    />
                    <div class="form-text">When profits will be distributed</div>
                </div>
            </div>

            <div class="row mb-0">
                <div class="col-md-6">
                    <x-group-input-text
                        label="Expected Delivery Date"
                        name="expected_delivery_date"
                        type="date"
                        :value="$isEdit ? $model->expected_delivery_date?->format('Y-m-d\TH:i') : (old('expected_delivery_date') ?? '')"
                    />
                    <div class="form-text">For merchandise-based investments</div>
                </div>
                <div class="col-md-6">
                    <x-group-input-text
                        label="Expected Distribution Date"
                        name="expected_distribution_date"
                        type="date"
                        :value="$isEdit ? $model->expected_distribution_date?->format('Y-m-d\TH:i') : (old('expected_distribution_date') ?? '')"
                    />
                    <div class="form-text">When returns are expected to be distributed</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Guarantee & Visibility --}}
    <div class="card card-flush mb-7">
        <div class="card-header">
            <h3 class="card-title">Additional Settings</h3>
        </div>
        <div class="card-body">
            <x-select
                label="Guarantee Type"
                name="guarantee"
                :options="array_flip(\App\GuaranteeTypeEnum::labels())"
                :old="$isEdit ? $model->guarantee : (old('guarantee') ?? '')"
            />

            <x-group-input-checkbox
                label="Show to Users"
                name="show"
                :value="$isEdit ? $model->show : (old('show', false))"
            />
            <div class="form-text">Enable to make this opportunity visible to investors</div>
        </div>
    {{-- </div> --}}

</x-form>
