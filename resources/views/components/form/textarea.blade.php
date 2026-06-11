@props(['label', 'name', 'value' => null, 'required' => false, 'rows' => 3])

<fieldset class="fieldset">
    <legend class="fieldset-legend">{{ $label }} @if($required)<span class="text-error">*</span>@endif</legend>
    <textarea
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full' . ($errors->has($name) ? ' textarea-error' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="label text-error text-xs">{{ $message }}</p>
    @enderror
</fieldset>
