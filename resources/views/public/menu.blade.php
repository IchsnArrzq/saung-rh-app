<x-guest-layout>
    <div>
        <livewire:frontend.menu-catalog />
    </div>

    @if (\App\Support\TableSessionContext::current())
        <div class="border-t border-base-300/90 bg-base-100/95 px-4 py-6 md:px-8">
            <h2 class="mb-4 text-lg font-bold">
                <i class="ri-sparkling-2-line text-primary"></i> Ruang Sosial Meja
            </h2>

            <div class="card border border-base-300 bg-base-100 rounded-xl">
                <div role="tablist" class="tabs tabs-lifted tabs-lg p-2">
                    <input type="radio" name="social_tabs" role="tab" class="tab font-semibold"
                        aria-label="💬 Chat" checked />
                    <div role="tabpanel" class="tab-content border-base-300 bg-base-100 p-4">
                        <livewire:frontend.table-chat />
                    </div>

                    <input type="radio" name="social_tabs" role="tab" class="tab font-semibold"
                        aria-label="🎵 Lagu" />
                    <div role="tabpanel" class="tab-content border-base-300 bg-base-100 p-4">
                        <livewire:frontend.song-request />
                    </div>

                    <input type="radio" name="social_tabs" role="tab" class="tab font-semibold"
                        aria-label="✨ Permintaan" />
                    <div role="tabpanel" class="tab-content border-base-300 bg-base-100 p-4">
                        <livewire:frontend.special-request-form />
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-guest-layout>
