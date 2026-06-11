@props(['label', 'name', 'options' => [], 'selected' => null, 'required' => false, 'placeholder' => '— Select —'])

<fieldset class="fieldset">
    <legend class="fieldset-legend">{{ $label }} @if($required)<span class="text-error">*</span>@endif</legend>
    <select
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($errors->has($name) ? ' select-error' : '')]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected((string) old($name, $selected) === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="label text-error text-xs">{{ $message }}</p>
    @enderror
</fieldset>
