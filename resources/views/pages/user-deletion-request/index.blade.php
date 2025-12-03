<x-app-layout>

    <x-dynamic-table
        table-id="user_deletion_requests_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="null"
    />

</x-app-layout>

