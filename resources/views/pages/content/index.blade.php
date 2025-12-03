<x-app-layout>

    {{-- Content Management DataTable --}}
    <x-dynamic-table
        table-id="contents_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('admin.contents.create')"
    />

</x-app-layout>
