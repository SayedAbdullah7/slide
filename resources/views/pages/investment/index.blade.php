<x-app-layout>
    {{-- Opportunity Header & Summary --}}
    @if(isset($opportunity) && $opportunity)
        @include('pages.investment.partials.opportunity-header')
        @include('pages.investment.partials.opportunity-summary')
    {{-- Investor Header --}}
    @elseif(isset($investor) && $investor)
        @include('pages.investment.partials.investor-header')
    {{-- General Header --}}
    @else
        @include('pages.investment.partials.general-header')
    @endif

    {{-- Investments DataTable --}}
    <x-dynamic-table
        table-id="investments_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="isset($opportunityId) && $opportunityId ? route('admin.investments.create', ['opportunity_id' => $opportunityId]) : (isset($investorId) && $investorId ? route('admin.investments.create', ['investor_id' => $investorId]) : route('admin.investments.create'))"
    />
</x-app-layout>
