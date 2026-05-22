<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-12rem)] min-h-[700px]">
    
    <div class="w-full lg:w-1/4 xl:w-1/5 flex flex-col bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden">
        <div class="flex p-2 gap-2 border-b border-gray-200 bg-gray-50">
            <button wire:click="setActiveTab('ongoing')" class="flex-1 py-2.5 rounded-md text-sm font-semibold transition-all {{ $activeTab === 'ongoing' ? 'bg-white text-gray-800 shadow border border-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                On-Going <span class="ml-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">{{ $this->ongoingOrders->count() }}</span>
            </button>
            <button wire:click="setActiveTab('completed')" class="flex-1 py-2.5 rounded-md text-sm font-semibold transition-all {{ $activeTab === 'completed' ? 'bg-white text-gray-800 shadow border border-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                Selesai <span class="ml-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">{{ $this->completedOrders->count() }}</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50">
            @php $sidebarData = $activeTab === 'ongoing' ? $this->ongoingOrders : $this->completedOrders; @endphp
            
            @forelse($sidebarData as $order)
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
                        <div x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', timeString: '0m 0s' }"
                             x-init="setInterval(() => { let diff = Math.floor((new Date() - new Date(start)) / 1000); timeString = Math.floor(diff / 60) + 'm ' + (diff % 60) + 's'; }, 1000)"
                             class="font-medium text-gray-700 bg-gray-100 px-2 py-1 rounded" x-text="timeString">
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-gray-400">
                    Belum ada data pesanan.
                </div>
            @endforelse
        </div>
    </div>

    <div class="w-full lg:w-3/4 xl:w-4/5 flex flex-col bg-white shadow-sm sm:rounded-lg border border-gray-200 overflow-hidden">
        
        <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Pesanan Aktif Dapur</h3>
                <p class="text-sm text-gray-500">Urutan prioritas berdasarkan waktu pemesanan</p>
            </div>
            <div x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID'), 1000)" class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</span>
                <div class="bg-gray-800 text-white font-mono px-3 py-1.5 rounded-md text-sm font-bold shadow-inner tracking-widest" x-text="time"></div>
            </div>
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden p-6 bg-gray-100/50">
            @if($this->ongoingOrders->isEmpty())
                <div class="flex h-full items-center justify-center">
                    <div class="text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <p class="text-lg font-medium text-gray-900">Dapur Kosong</p>
                        <p class="text-sm">Tidak ada pesanan yang harus diproses.</p>
                    </div>
                </div>
            @else
                <div class="flex gap-5 h-full">
                    @foreach($this->ongoingOrders as $order)
                        <div class="w-[380px] flex-shrink-0 bg-white rounded-lg shadow border border-gray-200 flex flex-col overflow-hidden h-full">
                            
                            <div class="p-4 border-b border-gray-200 bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">{{ $order->table ? $order->table->name : 'Takeaway' }}</h2>
                                        <p class="text-xs font-semibold text-gray-500 mt-1">#{{ $order->order_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between mt-2 pt-3 border-t border-gray-200/60"
                                     x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', timeString: '0m 0s' }"
                                     x-init="setInterval(() => { let diff = Math.floor((new Date() - new Date(start)) / 1000); timeString = Math.floor(diff / 60) + 'm ' + (diff % 60) + 's'; }, 1000)">
                                    <span class="text-xs font-medium text-gray-500">Waktu Order: {{ $order->ordered_at->format('H:i') }}</span>
                                    <span class="text-sm font-bold text-gray-900 bg-white px-2 py-1 rounded shadow-sm border border-gray-100" x-text="timeString"></span>
                                </div>
                            </div>

                            <div class="p-3 bg-white border-b border-gray-100 flex gap-2">
                                <button wire:click="markAsReady('{{ $order->id }}')" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white font-medium py-2 rounded-md text-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                                    Tandai Siap
                                </button>
                                <button wire:click="cancelOrder('{{ $order->id }}')" wire:confirm="Batalkan pesanan ini?" class="px-3 bg-white border border-gray-300 hover:bg-red-50 text-red-600 font-medium py-2 rounded-md text-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Batal
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-white">
                                @foreach($order->items as $item)
                                    <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 {{ $item->status === 'ready' ? 'bg-green-50/50 opacity-75' : 'bg-gray-50' }}">
                                        
                                        <div class="flex-shrink-0 text-center">
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-md bg-white border text-gray-900 font-bold text-sm shadow-sm {{ $item->status === 'ready' ? 'border-green-200 text-green-700' : 'border-gray-200' }}">
                                                {{ $item->qty }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold truncate {{ $item->status === 'ready' ? 'text-green-800 line-through' : 'text-gray-900' }}">
                                                {{ $item->menu_name_snapshot }}
                                            </p>
                                            @if($item->notes)
                                                <p class="text-xs text-red-600 mt-1 font-medium bg-red-50 inline-block px-1.5 py-0.5 rounded">
                                                    Note: {{ $item->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex-shrink-0 flex items-center gap-2">
                                            @if($item->status !== 'ready')
                                                <button wire:click="markItemAsServed('{{ $order->id }}', '{{ $item->id }}')" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded text-xs font-bold border border-indigo-100 transition-colors">
                                                    Dikerjakan
                                                </button>
                                                <button wire:click="voidItem('{{ $order->id }}', '{{ $item->id }}')" wire:confirm="Hapus item ini dari pesanan?" class="text-red-500 hover:text-red-700 bg-red-50 px-2 py-1 rounded text-xs font-bold border border-red-100 transition-colors">
                                                    Void
                                                </button>
                                            @else
                                                <span class="text-green-600 font-bold text-xs px-2 py-1 bg-green-100 rounded border border-green-200">Selesai</span>
                                            @endif
                                        </div>
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
