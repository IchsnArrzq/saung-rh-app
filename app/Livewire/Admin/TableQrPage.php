<?php

namespace App\Livewire\Admin;

use App\Models\Table;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TableQrPage extends Component
{
    public Table $table;

    public function mount(Table $table): void
    {
        $this->table = $table;
    }

    public function render()
    {
        $menuUrl = route('public.menu.index', [
            'mode' => 'offline',
            'table_id' => $this->table->id,
        ]);

        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='.urlencode($menuUrl);

        return view('livewire.admin.table-qr-page', [
            'menuUrl' => $menuUrl,
            'qrImageUrl' => $qrImageUrl,
        ]);
    }
}
