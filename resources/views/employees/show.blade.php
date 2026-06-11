<x-layouts.app title="Employee Detail">
    <x-ui.page-header :title="$employee->full_name" :subtitle="$employee->employee_code">
        <x-slot:actions>
            @can('update', $employee)
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm">Edit</a>
            @endcan
            <a href="{{ route('employees.index') }}" class="btn btn-ghost btn-sm">Back</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile card --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body items-center text-center">
                <div class="avatar @if(! $employee->profile_photo) avatar-placeholder @endif">
                    <div class="w-24 rounded-full bg-primary text-primary-content">
                        @if ($employee->profile_photo)
                            <img src="{{ Storage::disk('public')->url($employee->profile_photo) }}" alt="photo" />
                        @else
                            <span class="text-2xl">{{ strtoupper(substr($employee->first_name, 0, 2)) }}</span>
                        @endif
                    </div>
                </div>
                <h2 class="card-title mt-2">{{ $employee->full_name }}</h2>
                <span class="badge {{ $employee->employment_status->color() }}">{{ $employee->employment_status->label() }}</span>
                <div class="text-sm text-base-content/60 mt-2 space-y-1">
                    <p>{{ $employee->position?->name ?? 'No position' }}</p>
                    <p>{{ $employee->department?->name ?? 'No department' }}</p>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="card bg-base-100 shadow lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title text-lg">Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 mt-2 text-sm">
                    @php
                        $fields = [
                            'National ID' => $employee->national_id,
                            'Login Email' => $employee->user?->email,
                            'Personal Email' => $employee->personal_email,
                            'Phone' => $employee->phone_number,
                            'Gender' => $employee->gender?->label(),
                            'Date of Birth' => $employee->date_of_birth?->format('d M Y'),
                            'Join Date' => $employee->join_date?->format('d M Y'),
                            'Manager' => $employee->manager?->full_name,
                            'Roles' => $employee->user?->getRoleNames()->implode(', '),
                        ];
                    @endphp
                    @foreach ($fields as $label => $value)
                        <div>
                            <dt class="text-base-content/50">{{ $label }}</dt>
                            <dd class="font-medium">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </div>

                @if ($employee->address)
                    <div class="divider"></div>
                    <h3 class="font-semibold">Address</h3>
                    <p class="text-sm">
                        {{ $employee->address->address }},
                        {{ $employee->address->city }}, {{ $employee->address->province }}
                        {{ $employee->address->postal_code }}, {{ $employee->address->country }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="card bg-base-100 shadow mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg">Documents</h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Type</th><th>Uploaded</th><th class="text-right">File</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($employee->documents as $document)
                            <tr>
                                <td>{{ $document->document_type->label() }}</td>
                                <td>{{ $document->uploaded_at?->format('d M Y H:i') }}</td>
                                <td class="text-right">
                                    <a href="{{ Storage::disk('public')->url($document->file_path) }}" target="_blank" class="btn btn-ghost btn-xs">Download</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-base-content/50 py-4">No documents uploaded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('update', $employee)
                <div class="divider"></div>
                <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <x-form.select label="Document Type" name="document_type" :options="$documentTypes" required class="select-sm" />
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">File (PDF, DOCX, PNG, JPG)</legend>
                        <input type="file" name="file" required class="file-input file-input-bordered file-input-sm" accept=".pdf,.docx,.png,.jpg,.jpeg" />
                    </fieldset>
                    <button class="btn btn-primary btn-sm">Upload</button>
                </form>
            @endcan
        </div>
    </div>
</x-layouts.app>
