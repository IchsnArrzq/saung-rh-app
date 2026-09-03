@props(['item'])

{{--
    Satu item daun sidebar. Dipakai oleh dua pohon navigasi di
    `livewire/layout/sidebar.blade.php`: accordion (state terbuka) dan panel
    flyout (state tertutup). Markup-nya harus identik supaya kedua state
    memberi target navigasi yang sama.
--}}

<li>
    <a wire:navigate href="{{ $item['url'] }}" @if ($item['is_active']) aria-current="page" @endif
        class="{{ $item['is_active'] ? 'text-primary font-semibold' : 'text-stone-700' }}">
        <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
        <span class="grow">{{ $item['label'] }}</span>
        @if (!empty($item['badge_value']))
            <x-badge color="primary" size="sm">{{ $item['badge_value'] }}</x-badge>
        @endif
    </a>
</li>
