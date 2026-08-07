<div class="space-y-4">
    @if (session('success'))
        <x-alert type="success" class="py-2 text-sm">{{ session('success') }}</x-alert>
    @endif

    <div class="flex items-center justify-between">
        <span class="text-sm font-semibold"><i class="ri-bank-card-line text-primary"></i> Akun Penerima Pembayaran</span>
        <x-button variant="primary" size="sm" icon="ri-add-line" wire:click="create">Tambah Akun</x-button>
    </div>

    <x-data-table :zebra="false" size="sm">
        <x-slot:head>
            <tr>
                <th>Label</th><th>Tipe</th><th>Nomor</th><th>Atas Nama</th><th>Status</th><th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($accounts as $account)
            <tr>
                <td class="font-semibold">{{ $account->label }}<div class="text-xs text-base-content/60">{{ $account->provider }}</div></td>
                <td><x-badge color="ghost" size="sm">{{ $types[$account->type] ?? $account->type }}</x-badge></td>
                <td class="font-mono text-sm">{{ $account->account_number ?? '-' }}</td>
                <td class="text-sm">{{ $account->account_holder ?? '-' }}</td>
                <td>
                    <x-button size="xs" :outline="true" :variant="$account->is_active ? 'success' : 'ghost'"
                        wire:click="toggle('{{ $account->id }}')">
                        {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-button>
                </td>
                <td>
                    <div class="flex justify-end gap-1">
                        <x-button variant="ghost" size="xs" shape="square" icon="ri-pencil-line"
                            label="Edit akun {{ $account->label }}" wire:click="edit('{{ $account->id }}')" />
                        <x-button variant="ghost" size="xs" shape="square" icon="ri-delete-bin-line"
                            class="text-error" label="Hapus akun {{ $account->label }}"
                            data-confirm="Hapus akun ini?"
                            wire:click="delete('{{ $account->id }}')"
                            loading="delete('{{ $account->id }}')" />
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-8 text-center text-sm text-base-content/50">Belum ada akun pembayaran.</td></tr>
        @endforelse
    </x-data-table>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="pa-modal">
            <div class="card w-full max-w-md bg-base-100 shadow-xl">
                <div class="card-body gap-3">
                    <h3 class="card-title text-base">{{ $editingId ? 'Edit' : 'Tambah' }} Akun Pembayaran</h3>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <x-input field-class="sm:col-span-2" label="Label" name="label" size="sm"
                            wire:model="label" />

                        <x-select label="Tipe" name="type" size="sm" wire:model="type" :options="$types" />

                        <x-input label="Provider" name="provider" size="sm" wire:model="provider"
                            placeholder="BCA, GoPay..." />

                        <x-input label="Nomor Akun" name="account_number" size="sm" wire:model="account_number" />

                        <x-input label="Atas Nama" name="account_holder" size="sm" wire:model="account_holder" />

                        <x-textarea field-class="sm:col-span-2" label="Instruksi (opsional)" name="instructions"
                            size="sm" :rows="2" wire:model="instructions" />

                        <div class="sm:col-span-2">
                            <x-checkbox label="Aktif" name="is_active" size="sm" variant="success"
                                wire:model="is_active" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <x-button variant="ghost" size="sm" wire:click="$set('showForm', false)">Batal</x-button>
                        <x-button variant="primary" size="sm" wire:click="save" loading="save">Simpan</x-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
