<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-12rem)] min-h-[700px]">
    
    <div class="w-full lg:w-1/4 xl:w-1/5 flex flex-col bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden">
        <div class="flex p-2 gap-2 border-b border-gray-200 bg-gray-50">
            <button wire:click="setActiveTab('ongoing')" class="flex-1 py-2.5 rounded-md text-[11px] font-bold transition-all {{ $activeTab === 'ongoing' ? 'bg-white text-gray-800 shadow border border-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                ON-GOING <span class="ml-0.5 bg-gray-100 px-1.5 py-0.5 rounded-full text-[10px]">{{ $this->ongoingOrders->count() }}</span>
            </button>
            <button wire:click="setActiveTab('ready')" class="flex-1 py-2.5 rounded-md text-[11px] font-bold transition-all {{ $activeTab === 'ready' ? 'bg-white text-gray-800 shadow border border-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                READY <span class="ml-0.5 bg-gray-100 px-1.5 py-0.5 rounded-full text-[10px]">{{ $this->readyOrders->count() }}</span>
            </button>
            <button wire:click="setActiveTab('completed')" class="flex-1 py-2.5 rounded-md text-[11px] font-bold transition-all {{ $activeTab === 'completed' ? 'bg-white text-gray-800 shadow border border-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                SELESAI <span class="ml-0.5 bg-gray-100 px-1.5 py-0.5 rounded-full text-[10px]">{{ $this->completedOrders->count() }}</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50">
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
                <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow transition-shadow relative overflow-hidden">
                    @if($order->status === 'confirmed')
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-yellow-400"></div>
                    @elseif($order->status === 'preparing')
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                    @else
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>
                    @endif

                    <div class="flex justify-between items-start pl-2">
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $order->table ? $order->table->name : 'Takeaway' }}</h4>
                            <p class="text-xs font-medium text-gray-500 mt-0.5">#{{ $order->order_number }}</p>
                        </div>
                        <span class="px-2 py-1 text-[10px] font-bold rounded-md uppercase tracking-wider {{ in_array($order->status, ['confirmed', 'preparing']) ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-green-50 text-green-700 border border-green-100' }}">
                            {{ $order->status }}
                        </span>
                    </div>

                    <div class="pl-2 mt-3 flex justify-between items-center text-xs">
                        <span class="text-gray-500">{{ $order->ordered_at->format('H:i') }}</span>
                        @if(in_array($order->status, ['confirmed', 'preparing']))
                            <div x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', timeString: '0m 0s' }"
                                 x-init="setInterval(() => { let diff = Math.floor((new Date() - new Date(start)) / 1000); if (diff < 0) diff = 0; let d = Math.floor(diff / 86400); let h = Math.floor((diff % 86400) / 3600); let m = Math.floor((diff % 3600) / 60); let s = diff % 60; timeString = d > 0 ? d + 'd ' + h + 'h ' + m + 'm ' + s + 's' : (h > 0 ? h + 'h ' + m + 'm ' + s + 's' : m + 'm ' + s + 's'); }, 1000)"
                                 class="font-medium text-gray-700 bg-gray-100 px-2 py-1 rounded" x-text="timeString">
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-gray-400">
                    Belum ada data.
                </div>
            @endforelse
        </div>
    </div>

    <div class="w-full lg:w-3/4 xl:w-4/5 flex flex-col bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden">
        
        <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    @if($activeTab === 'ongoing')
                        Pesanan Aktif Dapur
                    @elseif($activeTab === 'ready')
                        Makanan Siap Diantar
                    @else
                        Riwayat Pesanan Selesai
                    @endif
                </h3>
                <p class="text-sm text-gray-500">
                    @if($activeTab === 'ongoing')
                        Urutan prioritas berdasarkan waktu pemesanan
                    @elseif($activeTab === 'ready')
                        Klik "Sudah Diantar" jika telah menyerahkan ke pelanggan
                    @else
                        Pesanan yang telah diselesaikan hari ini
                    @endif
                </p>
            </div>
            <div x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID'), 1000)" class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</span>
                <div class="bg-gray-800 text-white font-mono px-3 py-1.5 rounded-md text-sm font-bold shadow-inner tracking-widest" x-text="time"></div>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden p-6 bg-gray-100/50">
            @if($mainData->isEmpty())
                <div class="flex h-full items-center justify-center">
                    <div class="text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <p class="text-lg font-medium text-gray-900">
                            @if($activeTab === 'ongoing') Dapur Kosong @elseif($activeTab === 'ready') Semua Sudah Diambil @else Belum Ada Selesai @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="flex gap-5 h-full">
                    @foreach($mainData as $order)
                        <div class="w-[380px] flex-shrink-0 bg-white rounded-lg shadow border border-gray-200 flex flex-col overflow-hidden h-full opacity-100 {{ in_array($order->status, ['ready', 'served']) ? 'border-green-300 shadow-green-100' : '' }}">
                            
                            <div class="p-4 border-b border-gray-200 bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">{{ $order->table ? $order->table->name : 'Takeaway' }}</h2>
                                        <p class="text-xs font-semibold text-gray-500 mt-1">#{{ $order->order_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ in_array($order->status, ['confirmed', 'preparing']) ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-green-100 text-green-800 border border-green-200' }}">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between mt-2 pt-3 border-t border-gray-200/60">
                                    <span class="text-xs font-medium text-gray-500">Waktu Order: {{ $order->ordered_at->format('H:i') }}</span>
                                    @if(in_array($order->status, ['confirmed', 'preparing']))
                                        <div x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', timeString: '0m 0s' }"
                                             x-init="setInterval(() => { let diff = Math.floor((new Date() - new Date(start)) / 1000); if (diff < 0) diff = 0; let d = Math.floor(diff / 86400); let h = Math.floor((diff % 86400) / 3600); let m = Math.floor((diff % 3600) / 60); let s = diff % 60; timeString = d > 0 ? d + 'd ' + h + 'h ' + m + 'm ' + s + 's' : (h > 0 ? h + 'h ' + m + 'm ' + s + 's' : m + 'm ' + s + 's'); }, 1000)">
                                            <span class="text-sm font-bold text-gray-900 bg-white px-2 py-1 rounded shadow-sm border border-gray-100" x-text="timeString"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($activeTab === 'ongoing')
                                <div class="p-3 bg-white border-b border-gray-100 flex gap-2">
                                    <button wire:click="markAsReady('{{ $order->id }}')" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white font-medium py-2 rounded-md text-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                                        Semua Selesai Dimasak
                                    </button>
                                    <button wire:click="cancelOrder('{{ $order->id }}')" wire:confirm="Batalkan pesanan ini?" class="px-3 bg-white border border-gray-300 hover:bg-red-50 text-red-600 font-medium py-2 rounded-md text-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Batal
                                    </button>
                                </div>
                            @elseif($activeTab === 'ready')
                                <div class="p-3 bg-green-50 border-b border-green-100 flex gap-2">
                                    <button wire:click="markOrderAsServed('{{ $order->id }}')" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-md text-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-green-900">
                                        Sudah Diantar
                                    </button>
                                </div>
                            @endif

                            <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-white">
                                @foreach($order->items as $item)
                                    <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 {{ in_array($item->status, ['ready', 'served']) ? 'bg-green-50/50' : 'bg-gray-50' }}">
                                        
                                        <div class="flex-shrink-0 text-center">
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-white border text-gray-900 font-bold text-sm shadow-sm {{ in_array($item->status, ['ready', 'served']) ? 'border-green-200 text-green-700' : 'border-gray-200' }}">
                                                {{ $item->qty }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold truncate {{ in_array($item->status, ['ready', 'served']) ? 'text-green-800 line-through opacity-70' : 'text-gray-900' }}">
                                                {{ $item->menu_name_snapshot }}
                                            </p>
                                            @if($item->notes)
                                                <p class="text-xs text-red-600 mt-1 font-medium bg-red-50 inline-block px-1.5 py-0.5 rounded">
                                                    Note: {{ $item->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        @if($activeTab === 'ongoing')
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                @if(!in_array($item->status, ['ready', 'served']))
                                                    <button wire:click="markItemAsReady('{{ $order->id }}', '{{ $item->id }}')" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded text-xs font-bold border border-indigo-100 transition-colors">
                                                        Selesai
                                                    </button>
                                                    <button wire:click="voidItem('{{ $order->id }}', '{{ $item->id }}')" wire:confirm="Hapus item ini dari pesanan?" class="text-red-500 hover:text-red-700 bg-red-50 px-2 py-1 rounded text-xs font-bold border border-red-100 transition-colors">
                                                        Void
                                                    </button>
                                                @else
                                                    <span class="text-green-600 font-bold text-xs px-2 py-1 bg-green-100 rounded border border-green-200">Selesai</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($order->notes)
                            <div class="p-3 bg-yellow-50/50 border-t border-yellow-100 text-xs text-yellow-800">
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
