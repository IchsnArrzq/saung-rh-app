<x-guest-layout>
    <div>
        <livewire:frontend.menu-catalog />
    </div>

    @if (\App\Support\TableSessionContext::current())
        <div class="border-t border-base-300/90 bg-base-100/95 px-4 py-6 md:px-8">
            <h2 class="mb-4 text-lg font-bold">
                <i class="ri-sparkling-2-line text-primary"></i> Ruang Sosial Meja
            </h2>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="card border border-base-300 bg-base-100 rounded-xl lg:col-span-1">
                    <div class="card-body p-4">
                        <livewire:frontend.table-chat />
                    </div>
                </div>
                <div class="card border border-base-300 bg-base-100 rounded-xl">
                    <div class="card-body p-4">
                        <livewire:frontend.song-request />
                    </div>
                </div>
                <div class="card border border-base-300 bg-base-100 rounded-xl">
                    <div class="card-body p-4">
                        <livewire:frontend.special-request-form />
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-guest-layout>
