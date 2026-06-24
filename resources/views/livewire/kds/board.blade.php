<div class="flex flex-col lg:flex-row gap-4 lg:h-[calc(100vh-9.5rem)]">
    
    <div class="w-full lg:w-1/4 flex flex-col bg-base-100 shadow-sm rounded-2xl border border-base-300 overflow-hidden h-[400px] lg:h-full">
        <div class="flex p-2 gap-2 border-b border-base-300 bg-base-200">
            <button wire:click="setActiveTab('ongoing')" class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'ongoing' ? 'bg-primary text-primary-content shadow' : 'text-secondary hover:text-base-content hover:bg-base-300' }}">
                ON-GOING <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] {{ $activeTab === 'ongoing' ? 'bg-base-100 text-primary' : 'bg-base-300' }}">{{ $this->ongoingOrders->count() }}</span>
            </button>
            <button wire:click="setActiveTab('ready')" class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'ready' ? 'bg-primary text-primary-content shadow' : 'text-secondary hover:text-base-content hover:bg-base-300' }}">
                READY <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] {{ $activeTab === 'ready' ? 'bg-base-100 text-primary' : 'bg-base-300' }}">{{ $this->readyOrders->count() }}</span>
            </button>
            <button wire:click="setActiveTab('completed')" class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'completed' ? 'bg-primary text-primary-content shadow' : 'text-secondary hover:text-base-content hover:bg-base-300' }}">
                SELESAI <span class="ml-1 px-2 py-0.5 rounded-full text-[11px] {{ $activeTab === 'completed' ? 'bg-base-100 text-primary' : 'bg-base-300' }}">{{ $this->completedOrders->count() }}</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-base-200 min-h-0">
            @php 
                if ($activeTab === 'ongoing') {
                    $mainData = $this->ongoingOrders;
                } elseif ($activeTab === 'ready') {
                    $mainData = $this->readyOrders;
                } else {
                    $mainData = $this->completedOrders;
                }
            @endphp
            
            @forelse($mainData as $order)
                <div class="bg-base-100 rounded-xl p-3 shadow-sm relative overflow-hidden {{ $order->is_vip ? 'border-2 border-warning ring-1 ring-warning/40' : 'border border-base-300' }}">
                    @if($order->is_vip)
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-warning"></div>
                    @elseif($order->status === 'confirmed')
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-warning"></div>
                    @elseif($order->status === 'preparing')
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-info"></div>
                    @else
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-success"></div>
                    @endif

                    <div class="flex justify-between items-start pl-3">
                        <div>
                            <h4 class="font-bold text-base-content text-base flex items-center gap-1.5">
                                {{ $order->table ? $order->table->name : 'Takeaway' }}
                                @if($order->is_vip)
                                    <i class="ri-vip-crown-2-fill text-warning" title="Prioritas VIP"></i>
                                @endif
                            </h4>
                            <p class="text-xs font-medium text-secondary mt-0.5">#{{ $order->order_number }}</p>
                        </div>
                        <span class="badge {{ in_array($order->status, ['confirmed', 'preparing']) ? 'badge-info' : 'badge-success' }} badge-sm font-bold uppercase tracking-wider">
                            {{ $order->status }}
                        </span>
                    </div>

                    <div class="pl-3 mt-3 flex justify-between items-center text-sm">
                        <span class="text-secondary">{{ $order->ordered_at->format('H:i') }}</span>
                        @if(in_array($order->status, ['confirmed', 'preparing']))
                            <div x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', timeString: '0m 0s' }"
                                 x-init="setInterval(() => { let diff = Math.floor((new Date() - new Date(start)) / 1000); if (diff < 0) diff = 0; let d = Math.floor(diff / 86400); let h = Math.floor((diff % 86400) / 3600); let m = Math.floor((diff % 3600) / 60); let s = diff % 60; timeString = d > 0 ? d + 'd ' + h + 'h ' + m + 'm ' + s + 's' : (h > 0 ? h + 'h ' + m + 'm ' + s + 's' : m + 'm ' + s + 's'); }, 1000)"
                                 class="font-medium text-base-content bg-base-300 px-2 py-1 rounded" x-text="timeString">
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-secondary">
                    Belum ada data.
                </div>
            @endforelse
        </div>
    </div>

    <div class="w-full lg:w-3/4 flex flex-col bg-base-100 shadow-sm rounded-2xl border border-base-300 overflow-hidden h-[600px] lg:h-full">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center p-4 border-b border-base-300 bg-base-200 gap-3">
            <div>
                <h3 class="text-lg font-bold text-base-content">
                    @if($activeTab === 'ongoing')
                        Pesanan Aktif Dapur
                    @elseif($activeTab === 'ready')
                        Makanan Siap Diantar
                    @else
                        Riwayat Pesanan Selesai
                    @endif
                </h3>
                <p class="text-sm text-secondary mt-0.5">
                    @if($activeTab === 'ongoing')
                        Urutan prioritas berdasarkan waktu pemesanan
                    @elseif($activeTab === 'ready')
                        Klik "Sudah Diantar" jika telah menyerahkan ke pelanggan
                    @else
                        Pesanan yang telah diselesaikan hari ini
                    @endif
                </p>
            </div>
            <div x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID'), 1000)" class="flex items-center justify-between sm:justify-end space-x-4">
                <span class="text-sm font-medium text-secondary">{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</span>
                <div class="bg-success text-success-content font-mono px-3 py-1.5 rounded-xl text-sm font-bold shadow-inner tracking-widest" x-text="time"></div>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden p-5 bg-base-200 min-h-0">
            @if($mainData->isEmpty())
                <div class="flex h-full items-center justify-center py-12">
                    <div class="text-center text-secondary">
                        <svg class="mx-auto h-12 w-12 text-secondary mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <p class="text-lg font-medium text-base-content">
                            @if($activeTab === 'ongoing') Dapur Kosong @elseif($activeTab === 'ready') Semua Sudah Diambil @else Belum Ada Selesai @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="flex gap-4 h-full items-stretch w-max pb-2">
                    @foreach($mainData as $order)
                        <div class="w-[380px] flex-shrink-0 bg-base-100 rounded-2xl shadow-sm flex flex-col overflow-hidden h-full {{ $order->is_vip ? 'border-2 border-warning ring-2 ring-warning/30' : (in_array($order->status, ['ready', 'served']) ? 'border border-success' : 'border border-base-300') }}">

                            @if($order->is_vip)
                                <div class="flex items-center gap-1.5 bg-warning text-warning-content px-4 py-1.5 text-xs font-bold uppercase tracking-wider">
                                    <i class="ri-vip-crown-2-fill"></i> Prioritas VIP
                                </div>
                            @endif

                            <div class="p-4 border-b border-base-300 bg-base-200">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h2 class="text-xl font-bold text-base-content">{{ $order->table ? $order->table->name : 'Takeaway' }}</h2>
                                        <p class="text-xs font-semibold text-secondary mt-1">#{{ $order->order_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge {{ in_array($order->status, ['confirmed', 'preparing']) ? 'badge-info' : 'badge-success' }} font-bold uppercase tracking-wider">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between mt-2 pt-3 border-t border-base-300">
                                    <span class="text-xs font-medium text-secondary">Waktu Order: {{ $order->ordered_at->format('H:i') }}</span>
                                    @if(in_array($order->status, ['confirmed', 'preparing']))
                                        <div x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', timeString: '0m 0s' }"
                                             x-init="setInterval(() => { let diff = Math.floor((new Date() - new Date(start)) / 1000); if (diff < 0) diff = 0; let d = Math.floor(diff / 86400); let h = Math.floor((diff % 86400) / 3600); let m = Math.floor((diff % 3600) / 60); let s = diff % 60; timeString = d > 0 ? d + 'd ' + h + 'h ' + m + 'm ' + s + 's' : (h > 0 ? h + 'h ' + m + 'm ' + s + 's' : m + 'm ' + s + 's'); }, 1000)">
                                            <span class="text-sm font-bold text-base-content bg-base-100 px-2 py-1 rounded shadow-sm border border-base-300" x-text="timeString"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($activeTab === 'ongoing')
                                <div class="p-3 bg-base-100 border-b border-base-300 flex gap-2">
                                    <button wire:click="markAsReady('{{ $order->id }}')" class="btn btn-neutral btn-sm h-auto py-2.5 flex-1 font-semibold text-sm">
                                        Semua Selesai Dimasak
                                    </button>
                                    <button wire:click="cancelOrder('{{ $order->id }}')" data-confirm="Batalkan pesanan ini?" class="btn btn-outline btn-error btn-sm h-auto py-2.5 font-semibold text-sm">
                                        Batal
                                    </button>
                                </div>
                            @elseif($activeTab === 'ready')
                                <div class="p-3 bg-base-200 border-b border-base-300 flex gap-2">
                                    <button wire:click="markOrderAsServed('{{ $order->id }}')" class="btn btn-success btn-sm h-auto py-2.5 flex-1 font-semibold text-sm">
                                        Sudah Diantar
                                    </button>
                                </div>
                            @endif

                            <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-base-100 min-h-0">
                                @foreach($order->items as $item)
                                    <div class="flex flex-col gap-2.5 p-3 rounded-xl border {{ in_array($item->status, ['ready', 'served']) ? 'border-success bg-base-100' : 'border-base-300 bg-base-200' }}">
                                        
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="flex-shrink-0 text-center">
                                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-base-100 border text-base-content font-bold text-sm shadow-sm {{ in_array($item->status, ['ready', 'served']) ? 'border-success text-success' : 'border-base-300' }}">
                                                    {{ $item->qty }}
                                                </span>
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold leading-snug {{ in_array($item->status, ['ready', 'served']) ? 'text-success line-through opacity-70' : 'text-base-content' }}">
                                                    {{ $item->menu_name_snapshot }}
                                                </p>
                                            </div>

                                            @if($activeTab === 'ongoing')
                                                <div class="flex-shrink-0 flex items-center gap-1.5">
                                                    @if(!in_array($item->status, ['ready', 'served']))
                                                        <button wire:click="markItemAsReady('{{ $order->id }}', '{{ $item->id }}')" class="btn btn-xs btn-outline btn-info">
                                                            Selesai
                                                        </button>
                                                        <button wire:click="voidItem('{{ $order->id }}', '{{ $item->id }}')" data-confirm="Hapus item ini dari pesanan?" class="btn btn-xs btn-outline btn-error">
                                                            Void
                                                        </button>
                                                    @else
                                                        <span class="badge badge-success badge-outline badge-sm font-bold">Selesai</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        @if($item->notes)
                                            <div class="w-full">
                                                <p class="w-full text-xs text-error font-medium border border-error bg-base-100 px-3 py-2 rounded-md whitespace-normal break-words">
                                                    Note: {{ $item->notes }}
                                                </p>
                                            </div>
                                        @endif                                       
                                    </div>
                                @endforeach
                            </div>

                            @if($order->notes)
                                <div class="p-4 bg-warning text-warning-content border-t border-base-300 text-sm">
                                    <span class="font-bold">Catatan:</span> {{ $order->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
