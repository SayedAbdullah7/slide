<x-app-layout>
@php
    $investmentOpportunity = \App\Models\InvestmentOpportunity::first();
@endphp

    <x-dynamic-table
        table-id="investment_opportunities_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('investment-opportunity.create')"
    />



</x-app-layout>

