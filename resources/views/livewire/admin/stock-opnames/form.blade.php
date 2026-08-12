<div class="space-y-5">
    @include('admin.partials.flash')

    @if (! $opname)
        {{-- ============ CREATE: mulai draft (snapshot stok saat ini) ============ --}}
        <form wire:submit="startDraft" class="space-y-5">
            <x-card>
                <p class="mb-4 text-sm text-base-content/60">
                    Membuat opname akan <strong>menyimpan snapshot stok sistem saat ini</strong> untuk semua bahan aktif.
                    Setelah itu kamu bisa mengisi stok fisik hasil hitung dan mem-posting untuk menyesuaikan stok.
                </p>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-input label="Tanggal Opname" name="opname_date" type="date" :required="true"
                        wire:model.defer="opname_date" required />

                    <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="2"
                        wire:model.defer="notes" placeholder="Opsional" />
                </div>
            </x-card>

            <x-form-actions submit-label="Mulai Opname" :cancel-href="route('stock-opnames.index')"
                loading="startDraft" />
        </form>
    @else
        {{-- ============ EDIT: isi stok fisik ============ --}}
        @php($posted = $opname->status === 'posted')

        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-base-content/70">
                    <span class="font-semibold">{{ $opname->code }}</span>
                    &middot; {{ $opname->opname_date->format('d M Y') }}
                    @if ($opname->notes)
                        <span class="text-base-content/50">— {{ $opname->notes }}</span>
                    @endif
                </div>
                <x-badge :color="$posted ? 'success' : 'warning'">
                    {{ $posted ? 'Diposting' : 'Draft' }}
                </x-badge>
            </div>
        </x-card>

        <form wire:submit="save" class="space-y-4">
            <x-data-table :zebra="false">
                <x-slot:head>
                    <tr>
                        <th>Bahan</th>
                        <th class="text-right">Stok Sistem</th>
                        <th class="text-right">Stok Fisik</th>
                        <th class="text-right">Selisih</th>
                        <th>Catatan</th>
                    </tr>
                </x-slot:head>

                @forelse ($rows as $index => $row)
                    @php($diff = $row['physical_qty'] === '' ? null : (float) $row['physical_qty'] - (float) $row['system_qty'])
                    <tr wire:key="opname-row-{{ $row['id'] }}">
                        <td class="font-medium">
                            {{ $row['name'] }}
                            <span class="text-xs text-base-content/50">({{ $row['unit'] }})</span>
                        </td>
                        <td class="text-right">{{ number_format((float) $row['system_qty'], 3, ',', '.') }}</td>
                        <td class="text-right">
                            <x-input :bare="true" type="number" size="sm" step="0.001" min="0"
                                class="w-28 text-right" label="Stok fisik {{ $row['name'] }}"
                                wire:model.live.debounce.500ms="rows.{{ $index }}.physical_qty"
                                :disabled="$posted" />
                            @error("rows.{$index}.physical_qty")
                                <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="text-right">
                            @if ($diff === null)
                                <span class="text-base-content/30">—</span>
                            @else
                                <span class="font-semibold {{ abs($diff) < 0.0005 ? 'text-base-content/50' : ($diff > 0 ? 'text-success' : 'text-error') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <x-input :bare="true" size="sm" class="w-full" label="Catatan {{ $row['name'] }}"
                                wire:model.defer="rows.{{ $index }}.notes" placeholder="mis. rusak, tumpah..."
                                :disabled="$posted" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-base-content/50">Tidak ada bahan pada opname ini.</td>
                    </tr>
                @endforelse
            </x-data-table>

            @unless ($posted)
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <x-button variant="ghost" :href="route('stock-opnames.index')">Kembali</x-button>
                    <x-button type="submit" variant="outline" icon="ri-save-line" loading="save">
                        Simpan Draft
                    </x-button>
                    <x-button variant="primary" icon="ri-check-double-line" wire:click="post" loading="post"
                        data-confirm="Posting opname? Stok bahan akan disesuaikan sesuai hitungan fisik dan tidak bisa diubah lagi."
                        data-confirm-title="Posting Opname" data-confirm-yes="Ya, Posting">
                        Posting & Sesuaikan Stok
                    </x-button>
                </div>
            @else
                <div class="flex justify-end">
                    <x-button variant="ghost" :href="route('stock-opnames.index')">Kembali</x-button>
                </div>
            @endunless
        </form>
    @endif
</div>
