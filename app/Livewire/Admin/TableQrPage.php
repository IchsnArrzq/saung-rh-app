<?php

namespace App\Livewire\Admin;

use App\Models\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TableQrPage extends Component
{
    public Table $table;

    public function mount(Table $table): void
    {
        if (empty($table->qr_token)) {
            $table->update(['qr_token' => (string) Str::random(24)]);
        }

        $this->table = $table;
    }

    public function render()
    {
        // QR now points to the check-in endpoint which binds a table session
        // (physical-presence validation) before forwarding to the menu.
        $menuUrl = route('checkin.show', ['token' => $this->table->qr_token]);

        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='.urlencode($menuUrl);

        return view('livewire.admin.table-qr-page', [
            'menuUrl' => $menuUrl,
            'qrImageUrl' => $qrImageUrl,
        ]);
    }
}
