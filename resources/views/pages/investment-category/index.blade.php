<x-app-layout>

    <x-dynamic-table
        table-id="investment_categories_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('admin.investment-categories.create')"
    />

</x-app-layout>














