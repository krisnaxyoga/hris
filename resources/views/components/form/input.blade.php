@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false])

<fieldset class="fieldset">
    <legend class="fieldset-legend">{{ $label }} @if($required)<span class="text-error">*</span>@endif</legend>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($errors->has($name) ? ' input-error' : '')]) }}
    />
    @error($name)
        <p class="label text-error text-xs">{{ $message }}</p>
    @enderror
</fieldset>
