<?php

namespace App\Livewire\Frontend;

use App\Domains\Social\QueryUseCases\GetTableChatDirectoryQueryUseCase;
use App\Domains\Social\Services\ChatService;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Events\ChatMessagePosted;
use App\Events\FloorActivity;
use App\Models\Table;
use App\Support\LiveUpdate;
use App\Support\TableSessionContext;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

/**
 * WhatsApp-style table chat. A list of conversations lets a guest open their own
 * table's private room (people seated together, plus replies from floor staff)
 * or a direct chat with any other table that is currently occupied.
 *
 * Tables are read through GetTableChatDirectoryQueryUseCase — never with
 * Table::query() in render() (AGENTS.md § Livewire Rules).
 */
class TableChat extends Component
{
    #[Validate('required|string|max:280', message: [
        'required' => 'Tulis pesan dulu.',
        'max' => 'Pesan maksimal 280 karakter.',
    ])]
    public string $body = '';

    /** Locked: the room a guest posts to is their session's table, never one they type in. */
    #[Locked]
    public ?string $tableId = null;

    #[Locked]
    public ?string $tableCode = null;

    /** Per-device identity so people sharing a table are distinguishable. */
    public string $senderId = '';

    public string $senderName = '';

    /**
     * Which conversation is open: null = the chat list, 'room' = my own table
     * room, otherwise the id of the other table for a DM.
     */
    public ?string $activeConversation = null;

    public function mount(): void
    {
        $context = TableSessionContext::current();
        $this->tableId = $context['table_id'] ?? null;
        $this->tableCode = $context['table_code'] ?? null;

        $id = session('chat.participant.id');
        if (! $id) {
            $id = (string) Str::uuid();
            session(['chat.participant.id' => $id]);
        }

        $this->senderId = (string) $id;
        $this->senderName = (string) session('chat.participant.name', '');
    }

    /**
     * Refresh whenever a message is broadcast to this table's channel — covers
     * both our own room and any DM we take part in. The re-render pulls the
     * latest messages from Redis; the panel lights the chat tab if it is closed.
     */
    #[On('echo:chat.table.{tableId},ChatMessagePosted')]
    public function onBroadcast(): void
    {
        $this->dispatch('table-panel-activity', tab: 'chat');
    }

    public function saveName(): void
    {
        $this->senderName = Str::limit(trim($this->senderName), 24, '');
        session(['chat.participant.name' => $this->senderName]);
    }

    public function openRoom(): void
    {
        $this->resetErrorBag();
        $this->reset('body');
        $this->activeConversation = 'room';
    }

    public function openDm(string $otherTableId, GetTableChatDirectoryQueryUseCase $directory): void
    {
        $this->resetErrorBag();
        $this->reset('body');

        if (! $this->tableId || $otherTableId === $this->tableId) {
            return;
        }

        // Only allow DMs to tables that are actually occupied right now.
        if (! $directory->isOccupied($otherTableId)) {
            $this->activeConversation = null;

            return;
        }

        $this->activeConversation = $otherTableId;
    }

    public function backToList(): void
    {
        $this->resetErrorBag();
        $this->reset('body');
        $this->activeConversation = null;
    }

    public function send(ChatService $chat, GetTableChatDirectoryQueryUseCase $directory, GetTableSessionQueryUseCase $sessions): void
    {
        if (! $this->tableId || $this->activeConversation === null) {
            return;
        }

        if (! $this->sessionOpen($sessions)) {
            $this->addError('body', 'Sesi meja Anda sudah berakhir. Scan QR di meja untuk mulai lagi.');

            return;
        }

        $this->validate();

        try {
            if ($this->activeConversation === 'room') {
                $message = $chat->post(
                    $this->tableId,
                    (string) $this->tableCode,
                    $this->body,
                    $this->senderId,
                    $this->senderName !== '' ? $this->senderName : null,
                );

                $participants = [$this->tableId];
            } else {
                $other = $directory->find($this->activeConversation);

                if (! $other) {
                    $this->addError('body', 'Meja tujuan tidak ditemukan.');

                    return;
                }

                $message = $chat->postDm(
                    $this->tableId,
                    (string) $this->tableCode,
                    (string) $other->id,
                    (string) $other->code,
                    $this->body,
                    $this->senderId,
                    $this->senderName !== '' ? $this->senderName : null,
                );

                $participants = [$this->tableId, (string) $other->id];
            }
        } catch (RuntimeException $e) {
            $this->addError('body', $e->getMessage());

            return;
        }

        // Broadcast immediately (ShouldBroadcastNow) — there is no queue worker,
        // and a queued broadcast never reached the other devices.
        LiveUpdate::send(new ChatMessagePosted($participants, $message));

        // The staff Panel meja watches table rooms (not private DMs) for guests
        // waiting on a reply.
        if ($this->activeConversation === 'room') {
            LiveUpdate::send(new FloorActivity($this->tableId, FloorActivity::CHAT, FloorActivity::NEW, $this->tableCode));
        }

        $this->reset('body');
    }

    public function render(ChatService $chat, GetTableChatDirectoryQueryUseCase $directory, GetTableSessionQueryUseCase $sessions): View
    {
        $available = $chat->available();
        $sessionOpen = $this->sessionOpen($sessions);

        $conversations = [];
        $messages = [];
        $activeType = null;
        $activeHeader = null;
        $roomPreview = null;

        if ($this->tableId && $sessionOpen && $available) {
            if ($this->activeConversation === null) {
                $roomPreview = $chat->roomLastMessage($this->tableId);
                $conversations = $directory->occupiedExcept($this->tableId)
                    ->map(fn (Table $t): array => [
                        'id' => (string) $t->id,
                        'code' => (string) $t->code,
                        'name' => (string) ($t->name ?? ''),
                        'capacity' => (int) $t->capacity,
                        'last' => $chat->dmLastMessage($this->tableId, (string) $t->id),
                    ])
                    ->all();
            } elseif ($this->activeConversation === 'room') {
                $messages = $chat->messages($this->tableId);
                $activeType = 'room';
                $activeHeader = 'Meja '.$this->tableCode;
            } else {
                $other = $directory->find($this->activeConversation);
                $messages = $other ? $chat->dmMessages($this->tableId, (string) $other->id) : [];
                $activeType = 'dm';
                $activeHeader = $other ? 'Meja '.$other->code : 'Meja';
            }
        }

        return view('livewire.frontend.table-chat', [
            'available' => $available,
            'sessionOpen' => $sessionOpen,
            'conversations' => $conversations,
            'roomPreview' => $roomPreview,
            'messages' => $messages,
            'activeType' => $activeType,
            'activeHeader' => $activeHeader,
        ]);
    }

    /**
     * The phone's session is approved, still open, and for the table this chat
     * was opened on. Read fresh each time — the panel may have been left open
     * long after staff closed the session.
     */
    private function sessionOpen(GetTableSessionQueryUseCase $sessions): bool
    {
        $session = $sessions->active(TableSessionContext::sessionId());

        return $session !== null && $session->table_id === $this->tableId;
    }
}
