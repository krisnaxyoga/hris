<x-layouts.app title="New Company">
    <x-ui.page-header title="New Company" subtitle="Create a tenant company" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('companies.store') }}">
                @csrf
                @include('companies._form', ['submitLabel' => 'Create Company'])
            </form>
        </div>
    </div>
</x-layouts.app>
