<x-layouts.app title="New User">
    <x-ui.page-header title="New User" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form', ['submitLabel' => 'Create User'])
            </form>
        </div>
    </div>
</x-layouts.app>
