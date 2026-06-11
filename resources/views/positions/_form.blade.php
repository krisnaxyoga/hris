<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.select label="Department" name="department_id" :options="$departments" :selected="$position->department_id ?? null" required />
    <x-form.input label="Code" name="code" :value="$position->code ?? null" required />
    <x-form.input label="Name" name="name" :value="$position->name ?? null" required />
    <div class="md:col-span-2">
        <x-form.textarea label="Description" name="description" :value="$position->description ?? null" />
    </div>
</div>

<div class="flex gap-2 mt-6">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('positions.index') }}" class="btn btn-ghost">Cancel</a>
</div>
