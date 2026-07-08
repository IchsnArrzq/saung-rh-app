<div class="space-y-5">
    @include('admin.partials.flash')

    @if (! $opname)
        {{-- ============ CREATE: mulai draft (snapshot stok saat ini) ============ --}}
        <form wire:submit="startDraft" class="space-y-5">
            <div class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
                <p class="mb-4 text-sm text-stone-500">
                    Membuat opname akan <strong>menyimpan snapshot stok sistem saat ini</strong> untuk semua bahan aktif.
                    Setelah itu kamu bisa mengisi stok fisik hasil hitung dan mem-posting untuk menyesuaikan stok.
                </p>

                <div class="grid gap-4 md:grid-cols-2">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Tanggal Opname</legend>
                        <input type="date" class="input input-bordered w-full" wire:model.defer="opname_date" required>
                        @error('opname_date')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset class="fieldset md:col-span-2">
                        <legend class="fieldset-legend">Catatan</legend>
                        <textarea class="textarea textarea-bordered w-full" rows="2" wire:model.defer="notes"
                            placeholder="Opsional"></textarea>
                        @error('notes')
                            <p class="label text-error">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                    <i class="ri-camera-lens-line"></i> Mulai Opname
                </button>
                <a href="{{ route('stock-opnames.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    @else
        {{-- ============ EDIT: isi stok fisik ============ --}}
        @php($posted = $opname->status === 'posted')

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white p-4">
            <div class="text-sm text-stone-600">
                <span class="font-semibold">{{ $opname->code }}</span>
                &middot; {{ $opname->opname_date->format('d M Y') }}
                @if ($opname->notes)
                    <span class="text-stone-400">— {{ $opname->notes }}</span>
                @endif
            </div>
            @if ($posted)
                <span class="badge badge-success">Diposting</span>
            @else
                <span class="badge badge-warning">Draft</span>
            @endif
        </div>

        <form wire:submit="save" class="space-y-4">
            <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bahan</th>
                            <th class="text-right">Stok Sistem</th>
                            <th class="text-right">Stok Fisik</th>
                            <th class="text-right">Selisih</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            @php($diff = $row['physical_qty'] === '' ? null : (float) $row['physical_qty'] - (float) $row['system_qty'])
                            <tr wire:key="opname-row-{{ $row['id'] }}">
                                <td class="font-medium">
                                    {{ $row['name'] }}
                                    <span class="text-xs text-stone-400">({{ $row['unit'] }})</span>
                                </td>
                                <td class="text-right">{{ number_format((float) $row['system_qty'], 3, ',', '.') }}</td>
                                <td class="text-right">
                                    <input type="number" step="0.001" min="0"
                                        class="input input-bordered input-sm w-28 text-right"
                                        wire:model.live.debounce.500ms="rows.{{ $index }}.physical_qty"
                                        @disabled($posted)>
                                    @error("rows.{$index}.physical_qty")
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="text-right">
                                    @if ($diff === null)
                                        <span class="text-stone-300">—</span>
                                    @else
                                        <span class="{{ abs($diff) < 0.0005 ? 'text-stone-400' : ($diff > 0 ? 'text-success' : 'text-error') }} font-semibold">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <input type="text" class="input input-bordered input-sm w-full"
                                        wire:model.defer="rows.{{ $index }}.notes" placeholder="mis. rusak, tumpah..."
                                        @disabled($posted)>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-stone-400 py-6">Tidak ada bahan pada opname ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @unless ($posted)
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <a href="{{ route('stock-opnames.index') }}" class="btn btn-ghost">Kembali</a>
                    <button type="submit" class="btn btn-outline">
                        <i class="ri-save-line"></i> Simpan Draft
                    </button>
                    <button type="button" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                        data-confirm="Posting opname? Stok bahan akan disesuaikan sesuai hitungan fisik dan tidak bisa diubah lagi."
                        wire:click="post">
                        <i class="ri-check-double-line"></i> Posting & Sesuaikan Stok
                    </button>
                </div>
            @else
                <div class="flex justify-end">
                    <a href="{{ route('stock-opnames.index') }}" class="btn btn-ghost">Kembali</a>
                </div>
            @endunless
        </form>
    @endif
</div>
