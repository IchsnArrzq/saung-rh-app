<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Meja</h2>
            <a href="{{ route('tables.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Meja
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kapasitas</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tables as $table)
                    <tr>
                        <td class="font-semibold">{{ $table->code }}</td>
                        <td>{{ $table->name ?: '-' }}</td>
                        <td>{{ $table->capacity }} orang</td>
                        <td>
                            <span class="badge badge-outline">{{ str_replace('_', ' ', $table->status) }}</span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('tables.edit', $table) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('tables.destroy', $table) }}" method="POST" onsubmit="return confirm('Hapus meja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada data meja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tables->links() }}</div>
</x-app-layout>
