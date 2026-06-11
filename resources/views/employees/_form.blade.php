@php
    $isEdit = isset($employee) && $employee->exists;
    $account = $employee->user ?? null;
    $address = $employee->address ?? null;
@endphp

{{-- Account --}}
<div class="divider divider-start text-sm font-semibold">Login Account</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Account Email <span class="text-error">*</span></legend>
        <input type="email" name="account[email]" value="{{ old('account.email', $account->email ?? '') }}" required
            class="input input-bordered w-full @error('account.email') input-error @enderror" />
        @error('account.email') <p class="label text-error text-xs">{{ $message }}</p> @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Password @unless($isEdit)<span class="text-error">*</span>@endunless</legend>
        <input type="password" name="account[password]" @unless($isEdit) required @endunless
            class="input input-bordered w-full @error('account.password') input-error @enderror" />
        @error('account.password') <p class="label text-error text-xs">{{ $message }}</p> @enderror
        @if($isEdit)<p class="label text-xs">Leave blank to keep current password.</p>@endif
    </fieldset>

    <x-form.select label="Role" name="account[role]" :options="$roles"
        :selected="old('account.role', $isEdit ? $account?->getRoleNames()->first() : 'Employee')" placeholder="Employee" />

    <label class="label cursor-pointer justify-start gap-2 mt-6">
        <input type="hidden" name="account[is_active]" value="0" />
        <input type="checkbox" name="account[is_active]" value="1" class="checkbox" @checked(old('account.is_active', $account->is_active ?? true)) />
        <span class="label-text">Account Active</span>
    </label>
</div>

{{-- Personal --}}
<div class="divider divider-start text-sm font-semibold mt-6">Personal Information</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.input label="Employee Code" name="employee_code" :value="$employee->employee_code ?? null" required />
    <x-form.input label="National ID" name="national_id" :value="$employee->national_id ?? null" />
    <x-form.input label="First Name" name="first_name" :value="$employee->first_name ?? null" required />
    <x-form.input label="Last Name" name="last_name" :value="$employee->last_name ?? null" />
    <x-form.select label="Gender" name="gender" :options="$genders" :selected="$employee->gender?->value ?? null" />
    <x-form.input label="Date of Birth" name="date_of_birth" type="date" :value="$employee->date_of_birth?->toDateString() ?? null" />
    <x-form.input label="Phone Number" name="phone_number" :value="$employee->phone_number ?? null" />
    <x-form.input label="Personal Email" name="personal_email" type="email" :value="$employee->personal_email ?? null" />
</div>

{{-- Employment --}}
<div class="divider divider-start text-sm font-semibold mt-6">Employment</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.input label="Join Date" name="join_date" type="date" :value="$employee->join_date?->toDateString() ?? null" required />
    <x-form.select label="Employment Status" name="employment_status" :options="$statuses" :selected="$employee->employment_status?->value ?? 'probation'" required />
    <x-form.select label="Work Arrangement" name="work_arrangement" :options="$workArrangements" :selected="$employee->work_arrangement?->value ?? 'office'" />
    <x-form.select label="Department" name="department_id" :options="$departments" :selected="$employee->department_id ?? null" />
    <x-form.select label="Position" name="position_id" :options="$positions" :selected="$employee->position_id ?? null" />
    <x-form.select label="Manager" name="manager_id" :options="$managers" :selected="$employee->manager_id ?? null" />
    <x-form.input label="Profile Photo" name="profile_photo" type="file" accept="image/*" />
</div>

{{-- Address --}}
<div class="divider divider-start text-sm font-semibold mt-6">Address</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <fieldset class="fieldset">
            <legend class="fieldset-legend">Street Address</legend>
            <textarea name="address[address]" rows="2" class="textarea textarea-bordered w-full @error('address.address') textarea-error @enderror">{{ old('address.address', $address->address ?? '') }}</textarea>
            @error('address.address') <p class="label text-error text-xs">{{ $message }}</p> @enderror
        </fieldset>
    </div>
    <fieldset class="fieldset">
        <legend class="fieldset-legend">City</legend>
        <input type="text" name="address[city]" value="{{ old('address.city', $address->city ?? '') }}" class="input input-bordered w-full" />
    </fieldset>
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Province</legend>
        <input type="text" name="address[province]" value="{{ old('address.province', $address->province ?? '') }}" class="input input-bordered w-full" />
    </fieldset>
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Postal Code</legend>
        <input type="text" name="address[postal_code]" value="{{ old('address.postal_code', $address->postal_code ?? '') }}" class="input input-bordered w-full" />
    </fieldset>
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Country</legend>
        <input type="text" name="address[country]" value="{{ old('address.country', $address->country ?? 'Indonesia') }}" class="input input-bordered w-full" />
    </fieldset>
</div>

<div class="flex gap-2 mt-6">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
</div>
