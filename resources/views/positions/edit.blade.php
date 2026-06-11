<x-layouts.app title="Edit Position">
    <x-ui.page-header title="Edit Position" :subtitle="$position->name" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('positions.update', $position) }}">
                @csrf @method('PUT')
                @include('positions._form', ['submitLabel' => 'Update Position'])
            </form>
        </div>
    </div>
</x-layouts.app>
