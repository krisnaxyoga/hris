@props(['title', 'subtitle' => null])

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-base-content/60">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex gap-2">{{ $actions }}</div>
    @endisset
</div>
