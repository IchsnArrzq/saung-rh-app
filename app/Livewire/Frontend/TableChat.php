<?php

namespace App\Livewire\Frontend;

use App\Events\ChatMessagePosted;
use App\Models\Table;
use App\Services\Chat\ChatServiceInterface;
use App\Support\TableSessionContext;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

/**
 * WhatsApp-style table chat. A list of conversations lets a guest open their own
 * table's private room (people seated together) or a direct chat with any other
 * table that is currently occupied.
 */
class TableChat extends Component
{
    /** Table statuses that count as "occupied" and thus reachable for a DM. */
    private const OCCUPIED_STATUS_KEYS = ['occupied', 'order_in'];

    #[Validate('required|string|max:280')]
    public string $body = '';

    public ?string $tableId = null;

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
     * both our own room and any DM we take part in.
     */
    #[On('echo:chat.table.{tableId},ChatMessagePosted')]
    public function onBroadcast(): void
    {
        // Re-render pulls the latest messages from Redis.
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

    public function openDm(string $otherTableId): void
    {
        $this->resetErrorBag();
        $this->reset('body');

        if (! $this->tableId || $otherTableId === $this->tableId) {
            return;
        }

        // Only allow DMs to tables that are actually occupied right now.
        $reachable = Table::query()
            ->whereKey($otherTableId)
            ->whereHas('tableStatus', fn ($q) => $q->whereIn('key', self::OCCUPIED_STATUS_KEYS))
            ->exists();

        if (! $reachable) {
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

    public function send(ChatServiceInterface $chat): void
    {
        if (! $this->tableId || $this->activeConversation === null) {
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
                $other = Table::query()->find($this->activeConversation);

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

        ChatMessagePosted::dispatch($participants, $message);

        $this->reset('body');
    }

    public function render(ChatServiceInterface $chat): View
    {
        $available = $chat->available();

        $conversations = [];
        $messages = [];
        $activeType = null;
        $activeHeader = null;
        $roomPreview = null;

        if ($this->tableId && $available) {
            if ($this->activeConversation === null) {
                $roomPreview = $chat->roomLastMessage($this->tableId);
                $conversations = $this->occupiedTables()
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
                $other = Table::query()->find($this->activeConversation);
                $messages = $other ? $chat->dmMessages($this->tableId, (string) $other->id) : [];
                $activeType = 'dm';
                $activeHeader = $other ? 'Meja '.$other->code : 'Meja';
            }
        }

        return view('livewire.frontend.table-chat', [
            'available' => $available,
            'conversations' => $conversations,
            'roomPreview' => $roomPreview,
            'messages' => $messages,
            'activeType' => $activeType,
            'activeHeader' => $activeHeader,
        ]);
    }

    /**
     * Other tables that are currently occupied (reachable for a DM).
     *
     * @return \Illuminate\Support\Collection<int, Table>
     */
    private function occupiedTables()
    {
        return Table::query()
            ->with('tableStatus')
            ->whereKeyNot($this->tableId)
            ->whereHas('tableStatus', fn ($q) => $q->whereIn('key', self::OCCUPIED_STATUS_KEYS))
            ->orderBy('code')
            ->get();
    }
}
