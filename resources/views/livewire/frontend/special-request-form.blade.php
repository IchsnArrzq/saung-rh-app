{{-- Polling 30 dtk hanya cadangan saat Reverb tidak tersambung; `.visible` membuatnya
     diam selama panel meja tertutup. Perubahan normal datang lewat siaran FloorActivity. --}}
<div wire:key="special-request-form" wire:poll.30s.visible class="space-y-4">
    @if (! $sessionId)
        <x-alert type="info" icon="ri-qr-scan-2-line">Pindai QR di meja Anda untuk mengirim permintaan.</x-alert>
    @else
        @if (session('special_status'))
            <x-alert type="success">{{ session('special_status') }}</x-alert>
        @endif

        <form wire:submit="submit" class="space-y-4">
            <fieldset>
                <legend class="mb-2 text-sm font-medium">
                    Jenis permintaan<span class="text-error" aria-hidden="true"> *</span>
                </legend>

                @if ($categories->isEmpty())
                    <x-empty-state icon="ri-customer-service-2-line" title="Belum ada jenis permintaan"
                        description="Untuk sementara, panggil pelayan langsung dari meja Anda." />
                @else
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($categories as $category)
                            <label wire:key="request-category-{{ $category->id }}"
                                class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-base-200 px-3 py-2 text-sm ring-1 ring-transparent transition-colors hover:bg-base-300 has-checked:bg-primary/10 has-checked:text-primary has-checked:ring-primary has-focus-visible:ring-2 has-focus-visible:ring-primary/50">
                                <input type="radio" name="categoryId" value="{{ $category->id }}"
                                    wire:model="categoryId" class="sr-only">
                                <i class="{{ $category->iconClass() }} shrink-0 text-lg" aria-hidden="true"></i>
                                <span class="truncate font-medium">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @error('categoryId')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <x-textarea label="Isi permintaan" name="description" :rows="3" maxlength="280" required
                placeholder="Mis. tolong siapkan lilin ulang tahun, atau minta tambahan sendok."
                wire:model="description" />

            <x-button type="submit" variant="primary" :block="true" icon="ri-send-plane-2-line" loading="submit"
                class="min-h-11">
                Kirim permintaan
            </x-button>
        </form>

        @if ($mine->isNotEmpty())
            <section>
                <h3 class="mb-2 text-sm font-semibold">Permintaan Anda</h3>

                <ul class="divide-y divide-base-300 rounded-xl bg-base-200">
                    @foreach ($mine as $req)
                        <li class="flex items-start gap-3 px-3 py-2.5" wire:key="my-request-{{ $req->id }}">
                            <i class="{{ $req->category?->iconClass() ?? \App\Models\SpecialRequestCategory::DEFAULT_ICON }} mt-0.5 text-lg text-base-content/60"
                                aria-hidden="true"></i>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm">{{ $req->description }}</p>
                                <p class="mt-0.5 text-xs text-base-content/60">
                                    {{ $req->category?->name ?? 'Tanpa kategori' }} ·
                                    <span class="tabular-nums">{{ $req->created_at?->format('H:i') }}</span>
                                </p>

                                @if ($req->staff_note)
                                    <p class="mt-1.5 flex gap-1.5 text-sm text-base-content/80">
                                        <i class="ri-chat-quote-line mt-0.5 shrink-0" aria-hidden="true"></i>
                                        <span><span class="sr-only">Catatan pelayan: </span>{{ $req->staff_note }}</span>
                                    </p>
                                @endif
                            </div>

                            <x-status-badge :status="$req->status" size="sm" class="shrink-0" />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
</div>
