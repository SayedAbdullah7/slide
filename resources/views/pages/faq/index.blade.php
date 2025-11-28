<x-app-layout>

    {{-- FAQ Management DataTable --}}
    <x-dynamic-table
        table-id="faqs_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('admin.faqs.create')"
    />

</x-app-layout>
