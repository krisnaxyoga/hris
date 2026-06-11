<x-layouts.app title="New Employee">
    <x-ui.page-header title="New Employee" subtitle="Create an employee and their login account" />

    <div class="card bg-base-100 shadow max-w-4xl">
        <div class="card-body">
            <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
                @csrf
                @include('employees._form', ['submitLabel' => 'Create Employee'])
            </form>
        </div>
    </div>
</x-layouts.app>
