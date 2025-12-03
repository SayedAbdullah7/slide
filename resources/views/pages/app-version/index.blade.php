<x-app-layout>

    <x-dynamic-table
        table-id="app_versions_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('admin.app-versions.create')"
    />

</x-app-layout>

