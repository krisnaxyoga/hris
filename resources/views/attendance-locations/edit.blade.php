<x-layouts.app title="Edit Attendance Zone">
    <x-ui.page-header title="Edit Attendance Zone" :subtitle="$location->name" />

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('attendance-locations.update', $location) }}">
                @csrf @method('PUT')
                @include('attendance-locations._form', ['submitLabel' => 'Update Zone'])
            </form>
        </div>
    </div>
</x-layouts.app>
