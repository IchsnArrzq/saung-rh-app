@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'rounded-box bg-base-100 p-2 shadow'])

@php
$alignmentClasses = match ($align) {
    'left' => 'dropdown-start',
    'top' => 'dropdown-top',
    default => 'dropdown-end',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="dropdown {{ $alignmentClasses }}" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition.opacity.scale.duration.200ms
            class="dropdown-content z-50 mt-2 {{ $width }}"
            style="display: none;"
            @click="open = false">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
