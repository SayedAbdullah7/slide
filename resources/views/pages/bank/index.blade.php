<x-app-layout>

    <x-dynamic-table
        table-id="banks_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('admin.banks.create')"
    />

</x-app-layout>














