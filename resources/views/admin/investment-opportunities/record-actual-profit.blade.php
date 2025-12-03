<form id="kt_modal_form" action="{{ route('admin.investment-opportunities.record-actual-profit.store', $opportunity->id) }}" data-method="POST">
    @csrf

    <div class="mb-7">
        <h3 class="fw-bold text-gray-900 mb-4">تسجيل الربح الفعلي للفرصة: {{ $opportunity->name }}</h3>
    </div>

    <div class="row mb-7">
        <div class="col-md-6">
            <x-group-input-text
                label="الربح الفعلي لكل سهم"
                name="actual_profit_per_share"
                type="number"
                step="0.01"
                placeholder="0.00"
                required
            />
        </div>
        <div class="col-md-6">
            <x-group-input-text
                label="صافي الربح الفعلي لكل سهم"
                name="actual_net_profit_per_share"
                type="number"
                step="0.01"
                placeholder="0.00"
                required
            />
        </div>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary">
            <i class="ki-duotone ki-check fs-2 me-1">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            تسجيل الربح الفعلي
        </button>
    </div>
</form>
