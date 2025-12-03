<x-app-layout>

    <x-dynamic-table
        table-id="contact_messages_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('admin.contact-messages.create')"
    />

</x-app-layout>













