<x-layouts.app title="Edit Department">
    <x-ui.page-header title="Edit Department" :subtitle="$department->name" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('departments.update', $department) }}">
                @csrf @method('PUT')
                @include('departments._form', ['submitLabel' => 'Update Department'])
            </form>
        </div>
    </div>
</x-layouts.app>
