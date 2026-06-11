<x-layouts.app title="New Position">
    <x-ui.page-header title="New Position" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('positions.store') }}">
                @csrf
                @include('positions._form', ['submitLabel' => 'Create Position'])
            </form>
        </div>
    </div>
</x-layouts.app>
