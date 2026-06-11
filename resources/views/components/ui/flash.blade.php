@if (session('success'))
    <div role="alert" class="alert alert-success mb-4">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="alert alert-error mb-4">
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="alert alert-error mb-4">
        <div>
            <p class="font-semibold">Please fix the following:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
