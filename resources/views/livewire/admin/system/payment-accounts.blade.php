<div class="space-y-4">
    @if (session('success'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="flex items-center justify-between">
        <span class="text-sm font-semibold"><i class="ri-bank-card-line text-primary"></i> Akun Penerima Pembayaran</span>
        <button wire:click="create" class="btn btn-primary btn-sm"><i class="ri-add-line"></i> Tambah Akun</button>
    </div>

    <div class="overflow-x-auto card border border-base-300 bg-base-100 rounded-xl">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Label</th><th>Tipe</th><th>Nomor</th><th>Atas Nama</th><th>Status</th><th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td class="font-semibold">{{ $account->label }}<div class="text-xs text-secondary">{{ $account->provider }}</div></td>
                        <td><span class="badge badge-ghost badge-sm">{{ $types[$account->type] ?? $account->type }}</span></td>
                        <td class="font-mono text-sm">{{ $account->account_number ?? '-' }}</td>
                        <td class="text-sm">{{ $account->account_holder ?? '-' }}</td>
                        <td>
                            <button wire:click="toggle('{{ $account->id }}')"
                                class="badge badge-sm {{ $account->is_active ? 'badge-success' : 'badge-ghost' }}">
                                {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <button wire:click="edit('{{ $account->id }}')" class="btn btn-xs btn-ghost"><i class="ri-pencil-line"></i></button>
                                <button wire:click="delete('{{ $account->id }}')" data-confirm="Hapus akun ini?" class="btn btn-xs btn-ghost text-error"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-secondary text-sm">Belum ada akun pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="pa-modal">
            <div class="card w-full max-w-md bg-base-100 shadow-xl">
                <div class="card-body gap-3">
                    <h3 class="card-title text-base">{{ $editingId ? 'Edit' : 'Tambah' }} Akun Pembayaran</h3>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="form-control sm:col-span-2">
                            <span class="label-text mb-1">Label</span>
                            <input type="text" wire:model="label" class="input input-bordered input-sm">
                            @error('label') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-1">Tipe</span>
                            <select wire:model="type" class="select select-bordered select-sm">
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-1">Provider</span>
                            <input type="text" wire:model="provider" class="input input-bordered input-sm" placeholder="BCA, GoPay...">
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-1">Nomor Akun</span>
                            <input type="text" wire:model="account_number" class="input input-bordered input-sm">
                        </label>
                        <label class="form-control">
                            <span class="label-text mb-1">Atas Nama</span>
                            <input type="text" wire:model="account_holder" class="input input-bordered input-sm">
                        </label>
                        <label class="form-control sm:col-span-2">
                            <span class="label-text mb-1">Instruksi (opsional)</span>
                            <textarea wire:model="instructions" rows="2" class="textarea textarea-bordered textarea-sm"></textarea>
                        </label>
                        <label class="label cursor-pointer justify-start gap-2 sm:col-span-2">
                            <input type="checkbox" wire:model="is_active" class="checkbox checkbox-sm checkbox-success">
                            <span class="label-text">Aktif</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button wire:click="$set('showForm', false)" class="btn btn-ghost btn-sm">Batal</button>
                        <button wire:click="save" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
