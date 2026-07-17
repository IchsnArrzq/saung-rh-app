@props(['disabled' => false])

{{-- Password field with an accessible show/hide toggle (Alpine + Remix Icon). --}}
<div class="relative" x-data="{ show: false }">
    <input
        @disabled($disabled)
        type="password"
        x-bind:type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'input input-bordered w-full pe-11']) }}>

    <button type="button" tabindex="-1" x-on:click="show = !show"
        class="absolute inset-y-0 end-0 flex items-center px-3 text-stone-400 transition hover:text-primary focus:outline-none focus-visible:text-primary"
        x-bind:aria-label="show ? '{{ __('Sembunyikan password') }}' : '{{ __('Tampilkan password') }}'"
        x-bind:aria-pressed="show ? 'true' : 'false'">
        <i class="text-lg" x-bind:class="show ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
    </button>
</div>
