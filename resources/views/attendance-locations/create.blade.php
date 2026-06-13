<x-layouts.app title="New Attendance Zone">
    <x-ui.page-header title="New Attendance Zone" subtitle="Set the office location and check-in radius" />

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('attendance-locations.store') }}">
                @csrf
                @include('attendance-locations._form', ['submitLabel' => 'Create Zone'])
            </form>
        </div>
    </div>
</x-layouts.app>
