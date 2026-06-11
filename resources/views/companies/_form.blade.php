<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.input label="Company Name" name="name" :value="$company->name ?? null" required />
    <x-form.input label="Email" name="email" type="email" :value="$company->email ?? null" />
    <x-form.input label="Phone" name="phone" :value="$company->phone ?? null" />
    <x-form.input label="Subscription Plan" name="subscription_plan" :value="$company->subscription_plan ?? 'free'" />
    <div class="md:col-span-2">
        <x-form.textarea label="Address" name="address" :value="$company->address ?? null" />
    </div>
    <label class="label cursor-pointer justify-start gap-2">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $company->is_active ?? true)) />
        <span class="label-text">Active</span>
    </label>
</div>

<div class="flex gap-2 mt-6">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('companies.index') }}" class="btn btn-ghost">Cancel</a>
</div>
