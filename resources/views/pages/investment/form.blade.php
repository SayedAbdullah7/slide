@php
    $isEdit = isset($model);
    $opportunityId = request()->get('opportunity_id', old('opportunity_id', $model->opportunity_id ?? ''));
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <form id="kt_modal_form" action="{{ $isEdit ? route('admin.investments.update', $model->id) : route('admin.investments.store') }}"
          data-method="{{ $isEdit ? 'PUT' : 'POST' }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <!-- Investment Opportunity Selection -->
        <div class="row mb-7">
            <div class="col-md-12">
                <x-select
                    label="Investment Opportunity"
                    name="opportunity_id"
                    :value="$opportunityId"
                    :options="$investmentOpportunities->mapWithKeys(function($opportunity) {
                        return [$opportunity->id => $opportunity->name . ' - $' . number_format($opportunity->target_amount, 2)];
                    })"
                    required
                />
            </div>
        </div>

        <!-- Investor Profile Selection -->
        <div class="row mb-7">
            <div class="col-md-12">
                <x-select
                    label="Investor Profile"
                    name="investor_id"
                    :value="old('investor_id', $model->investor_id ?? '')"
                    :options="$investorProfiles->mapWithKeys(function($profile) {
                        return [$profile->id => $profile->user->display_name . ' (' . $profile->user->email . ')'];
                    })"
                    required
                />
            </div>
        </div>

        <!-- Investment Type -->
        <div class="row mb-7">
            <div class="col-md-6">
                <x-select
                    label="Investment Type"
                    name="investment_type"
                    :value="old('investment_type', $model->investment_type ?? '')"
                    :options="[
                        'myself' => 'Myself (بيع بنفسي)',
                        'authorize' => 'Authorize (تفويض بالبيع)'
                    ]"
                    required
                />
            </div>
            <div class="col-md-6">
                <x-select
                    label="Status"
                    name="status"
                    :value="old('status', $model->status ?? 'active')"
                    :options="[
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ]"
                    required
                />
            </div>
        </div>

        <!-- Investment Details -->
        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Number of Shares"
                    name="shares"
                    type="number"
                    :value="old('shares', $model->shares ?? '')"
                    placeholder="Enter number of shares"
                    required
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Share Price"
                    name="share_price"
                    type="number"
                    step="0.01"
                    :value="old('share_price', $model->share_price ?? '')"
                    placeholder="0.00"
                    required
                />
            </div>
        </div>

        <!-- Investment Date -->
        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Investment Date"
                    name="investment_date"
                    type="datetime-local"
                    :value="old('investment_date', $model->investment_date ? $model->investment_date->format('Y-m-d\TH:i') : '')"
                    required
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Shipping Fee Per Share"
                    name="shipping_fee_per_share"
                    type="number"
                    step="0.01"
                    :value="old('shipping_fee_per_share', $model->shipping_fee_per_share ?? '')"
                    placeholder="0.00"
                />
            </div>
        </div>

        <!-- Expected Returns -->
        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Expected Profit Per Share"
                    name="expected_profit_per_share"
                    type="number"
                    step="0.01"
                    :value="old('expected_profit_per_share', $model->expected_profit_per_share ?? '')"
                    placeholder="0.00"
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Expected Net Profit Per Share"
                    name="expected_net_profit_per_share"
                    type="number"
                    step="0.01"
                    :value="old('expected_net_profit_per_share', $model->expected_net_profit_per_share ?? '')"
                    placeholder="0.00"
                />
            </div>
        </div>

        <!-- Merchandise Tracking -->
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">Merchandise Tracking</h4>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-select
                    label="Merchandise Status"
                    name="merchandise_status"
                    :value="old('merchandise_status', $model->merchandise_status ?? 'pending')"
                    :options="[
                        'pending' => 'Pending',
                        'arrived' => 'Arrived'
                    ]"
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Expected Delivery Date"
                    name="expected_delivery_date"
                    type="datetime-local"
                    :value="old('expected_delivery_date', $model->expected_delivery_date ? $model->expected_delivery_date->format('Y-m-d\TH:i') : '')"
                />
            </div>
        </div>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Expected Distribution Date"
                    name="expected_distribution_date"
                    type="datetime-local"
                    :value="old('expected_distribution_date', $model->expected_distribution_date ? $model->expected_distribution_date->format('Y-m-d\TH:i') : '')"
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Merchandise Arrived At"
                    name="merchandise_arrived_at"
                    type="datetime-local"
                    :value="old('merchandise_arrived_at', $model->merchandise_arrived_at ? $model->merchandise_arrived_at->format('Y-m-d\TH:i') : '')"
                />
            </div>
        </div>

        <!-- Actual Returns -->
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">Actual Returns</h4>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Actual Profit Per Share"
                    name="actual_profit_per_share"
                    type="number"
                    step="0.01"
                    :value="old('actual_profit_per_share', $model->actual_profit_per_share ?? '')"
                    placeholder="0.00"
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Actual Net Profit Per Share"
                    name="actual_net_profit_per_share"
                    type="number"
                    step="0.01"
                    :value="old('actual_net_profit_per_share', $model->actual_net_profit_per_share ?? '')"
                    placeholder="0.00"
                />
            </div>
        </div>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Actual Returns Recorded At"
                    name="actual_returns_recorded_at"
                    type="datetime-local"
                    :value="old('actual_returns_recorded_at', $model->actual_returns_recorded_at ? $model->actual_returns_recorded_at->format('Y-m-d\TH:i') : '')"
                />
            </div>
        </div>

        <!-- Distribution -->
        <div class="separator separator-dashed my-10"></div>
        <h4 class="mb-7">Distribution</h4>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-select
                    label="Distribution Status"
                    name="distribution_status"
                    :value="old('distribution_status', $model->distribution_status ?? 'pending')"
                    :options="[
                        'pending' => 'Pending',
                        'distributed' => 'Distributed'
                    ]"
                />
            </div>
            <div class="col-md-6">
                <x-group-input-text
                    label="Distributed Profit"
                    name="distributed_profit"
                    type="number"
                    step="0.01"
                    :value="old('distributed_profit', $model->distributed_profit ?? '')"
                    placeholder="0.00"
                />
            </div>
        </div>

        <div class="row mb-7">
            <div class="col-md-6">
                <x-group-input-text
                    label="Distributed At"
                    name="distributed_at"
                    type="datetime-local"
                    :value="old('distributed_at', $model->distributed_at ? $model->distributed_at->format('Y-m-d\TH:i') : '')"
                />
            </div>
        </div>

    </form>

</div>

<!-- Actions -->
<div class="text-center pt-10">
    <button type="button" class="btn btn-light me-3 close" data-bs-dismiss="modal">
        {{ __('Close') }}
    </button>
    <button type="submit" form="kt_modal_form" class="btn btn-primary">
        {{ $isEdit ? __('Update Investment') : __('Create Investment') }}
    </button>
</div>


