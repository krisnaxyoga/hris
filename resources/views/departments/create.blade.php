<x-layouts.app title="New Department">
    <x-ui.page-header title="New Department" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('departments.store') }}">
                @csrf
                @include('departments._form', ['submitLabel' => 'Create Department'])
            </form>
        </div>
    </div>
</x-layouts.app>
