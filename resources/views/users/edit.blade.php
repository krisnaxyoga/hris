<x-layouts.app title="Edit User">
    <x-ui.page-header title="Edit User" :subtitle="$user->email" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf @method('PUT')
                @include('users._form', ['submitLabel' => 'Update User'])
            </form>
        </div>
    </div>
</x-layouts.app>
