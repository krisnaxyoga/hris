<x-layouts.app title="Edit Company">
    <x-ui.page-header title="Edit Company" :subtitle="$company->name" />

    <div class="card bg-base-100 shadow max-w-3xl">
        <div class="card-body">
            <form method="POST" action="{{ route('companies.update', $company) }}">
                @csrf @method('PUT')
                @include('companies._form', ['submitLabel' => 'Update Company'])
            </form>
        </div>
    </div>
</x-layouts.app>
