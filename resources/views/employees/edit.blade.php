<x-layouts.app title="Edit Employee">
    <x-ui.page-header title="Edit Employee" :subtitle="$employee->full_name" />

    <div class="card bg-base-100 shadow max-w-4xl">
        <div class="card-body">
            <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('employees._form', ['submitLabel' => 'Update Employee'])
            </form>
        </div>
    </div>
</x-layouts.app>
