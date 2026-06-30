<?php

namespace App\Livewire\Frontend;

use App\Events\ChatMessagePosted;
use App\Services\Chat\ChatService;
use App\Support\TableSessionContext;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

class TableChat extends Component
{
    #[Validate('required|string|max:280')]
    public string $body = '';

    public ?string $tableId = null;

    public ?string $tableCode = null;

    public function mount(): void
    {
        $context = TableSessionContext::current();
        $this->tableId = $context['table_id'] ?? null;
        $this->tableCode = $context['table_code'] ?? null;
    }

    /**
     * Refresh the lobby whenever any table broadcasts a message (public channel).
     */
    #[On('echo:chat.lobby,ChatMessagePosted')]
    public function onBroadcast(): void
    {
        // Re-render pulls the latest messages from Redis.
    }

    public function send(ChatService $chat): void
    {
        if (! $this->tableId) {
            return;
        }

        $this->validate();

        try {
            $message = $chat->post($this->tableId, (string) $this->tableCode, $this->body);
        } catch (RuntimeException $e) {
            $this->addError('body', $e->getMessage());

            return;
        }

        ChatMessagePosted::dispatch($message);

        $this->reset('body');
    }

    public function report(ChatService $chat, string $messageId): void
    {
        if (! $this->tableId) {
            return;
        }

        $result = $chat->report($messageId, $this->tableId);

        session()->flash('chat_status', $result['blocked']
            ? 'Pesan dilaporkan. Meja pelapor telah diblokir otomatis.'
            : 'Pesan dilaporkan ('.$result['reports'].' laporan).');
    }

    public function render(ChatService $chat): View
    {
        $available = $chat->available();

        return view('livewire.frontend.table-chat', [
            'available' => $available,
            'messages' => $this->tableId && $available ? $chat->messages() : [],
            'blocked' => $this->tableId && $available ? $chat->isBlocked($this->tableId) : false,
        ]);
    }
}
