<div class="space-y-4">
    @if (session('shift_status'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('shift_status') }}</span></div>
    @endif

    <div class="grid gap-4 lg:grid-cols-4">
        {{-- Form --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl">
            <div class="card-body gap-2 p-4">
                <h3 class="card-title text-sm"><i class="ri-calendar-schedule-line text-primary"></i> Jadwalkan Shift</h3>
                <form wire:submit="save" class="space-y-2">
                    <select wire:model="userId" class="select select-bordered select-sm w-full">
                        <option value="">Pilih staf...</option>
                        @foreach ($staff as $person)
                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                        @endforeach
                    </select>
                    @error('userId') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    <input type="date" wire:model="shiftDate" class="input input-bordered input-sm w-full">
                    <div class="flex gap-2">
                        <input type="time" wire:model="startsAt" class="input input-bordered input-sm grow">
                        <input type="time" wire:model="endsAt" class="input input-bordered input-sm grow">
                    </div>
                    @error('endsAt') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    <input type="text" wire:model="position" placeholder="Posisi (opsional)" class="input input-bordered input-sm w-full">
                    <button type="submit" class="btn btn-primary btn-sm w-full"><i class="ri-add-line"></i> Tambah Shift</button>
                </form>
            </div>
        </div>

        {{-- Week grid --}}
        <div class="lg:col-span-3 card border border-base-300 bg-base-100 rounded-xl">
            <div class="card-body gap-3 p-4">
                <div class="flex items-center justify-between">
                    <h3 class="card-title text-sm">Jadwal Mingguan</h3>
                    <div class="join">
                        <button wire:click="previousWeek" class="btn btn-xs join-item btn-ghost"><i class="ri-arrow-left-s-line"></i></button>
                        <button wire:click="nextWeek" class="btn btn-xs join-item btn-ghost"><i class="ri-arrow-right-s-line"></i></button>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                    @foreach ($days as $day)
                        @php $key = $day->toDateString(); $list = $shiftsByDay[$key] ?? collect(); @endphp
                        <div class="rounded-lg border border-base-300 p-2 min-h-28 {{ $day->isToday() ? 'border-primary/50 bg-primary/5' : '' }}">
                            <div class="text-xs font-semibold mb-1">{{ $day->translatedFormat('D') }} <span class="text-secondary">{{ $day->format('d/m') }}</span></div>
                            <div class="space-y-1">
                                @forelse ($list as $shift)
                                    <div class="group rounded bg-base-200 px-1.5 py-1 text-[11px] leading-tight">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-medium truncate">{{ $shift->user->name }}</span>
                                            <button wire:click="deleteShift('{{ $shift->id }}')" class="text-error/70 opacity-0 group-hover:opacity-100"><i class="ri-close-line"></i></button>
                                        </div>
                                        <span class="text-secondary">{{ \Illuminate\Support\Str::of($shift->starts_at)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($shift->ends_at)->substr(0,5) }}</span>
                                    </div>
                                @empty
                                    <span class="text-[11px] text-secondary/60">—</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
