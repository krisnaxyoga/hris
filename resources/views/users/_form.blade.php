@php($currentRoles = old('roles', isset($user) ? $user->getRoleNames()->all() : []))

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.input label="Name" name="name" :value="$user->name ?? null" required />
    <x-form.input label="Email" name="email" type="email" :value="$user->email ?? null" required />
    <x-form.input label="Password" name="password" type="password" :required="! isset($user)" />
    <div class="flex items-end">
        <label class="label cursor-pointer justify-start gap-2">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $user->is_active ?? true)) />
            <span class="label-text">Active</span>
        </label>
    </div>
</div>

@isset($user)
    <p class="text-xs text-base-content/50 mt-1">Leave password blank to keep the current password.</p>
@endisset

<fieldset class="fieldset mt-4">
    <legend class="fieldset-legend">Roles</legend>
    <div class="flex flex-wrap gap-3">
        @foreach ($roles as $role)
            <label class="label cursor-pointer justify-start gap-2 border border-base-300 rounded-lg px-3 py-2">
                <input type="checkbox" name="roles[]" value="{{ $role }}" class="checkbox checkbox-sm" @checked(in_array($role, $currentRoles, true)) />
                <span class="label-text">{{ $role }}</span>
            </label>
        @endforeach
    </div>
    @error('roles') <p class="label text-error text-xs">{{ $message }}</p> @enderror
</fieldset>

<div class="flex gap-2 mt-6">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
</div>
