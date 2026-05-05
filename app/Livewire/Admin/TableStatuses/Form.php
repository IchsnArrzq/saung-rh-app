<?php

namespace App\Livewire\Admin\TableStatuses;

use App\Models\TableStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    /**
     * @var array<int, string>
     */
    private const RESERVED_KEYS = ['available', 'occupied', 'order_in', 'cleaning'];

    public ?TableStatus $tableStatus = null;

    public string $name = '';

    public string $key = '';

    public string $color = 'neutral';

    public string $sort_order = '0';

    public bool $is_active = true;

    public bool $is_default = false;

    public function mount(?TableStatus $tableStatus = null): void
    {
        $this->tableStatus = $tableStatus;

        if ($this->tableStatus) {
            $this->authorize('update', $this->tableStatus);

            $this->name = (string) $this->tableStatus->name;
            $this->key = (string) $this->tableStatus->key;
            $this->color = (string) ($this->tableStatus->color ?: 'neutral');
            $this->sort_order = (string) $this->tableStatus->sort_order;
            $this->is_active = (bool) $this->tableStatus->is_active;
            $this->is_default = (bool) $this->tableStatus->is_default;

            return;
        }

        $this->authorize('create', TableStatus::class);
    }

    public function save()
    {
        $validated = $this->validate($this->rules());

        $normalizedKey = Str::of((string) $validated['key'])
            ->trim()
            ->replace(['-', ' '], '_')
            ->lower()
            ->snake()
            ->toString();

        if ($this->tableStatus
            && in_array($this->tableStatus->key, self::RESERVED_KEYS, true)
            && $normalizedKey !== $this->tableStatus->key
        ) {
            throw ValidationException::withMessages([
                'key' => 'Key untuk status sistem tidak boleh diubah.',
            ]);
        }

        $payload = [
            'name' => $validated['name'],
            'key' => $normalizedKey,
            'color' => $validated['color'] ?: null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) $this->is_default,
        ];

        if ($this->tableStatus) {
            $this->tableStatus->update($payload);
            $status = $this->tableStatus;
            session()->flash('success', 'Status meja berhasil diperbarui.');
        } else {
            $status = TableStatus::query()->create($payload);
            session()->flash('success', 'Status meja berhasil ditambahkan.');
        }

        $this->syncDefaultFlag($status, $payload['is_default']);

        return $this->redirectRoute('table-statuses.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $keyRule = Rule::unique('table_statuses', 'key');

        if ($this->tableStatus) {
            $keyRule = $keyRule->ignore($this->tableStatus->id);
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'key' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9_\-\s]+$/', $keyRule],
            'color' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ];
    }

    private function syncDefaultFlag(TableStatus $tableStatus, bool $isDefault): void
    {
        if ($isDefault) {
            TableStatus::query()
                ->where('id', '!=', $tableStatus->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            if (! $tableStatus->is_default) {
                $tableStatus->update(['is_default' => true]);
            }

            return;
        }

        $hasDefault = TableStatus::query()->where('is_default', true)->exists();

        if (! $hasDefault) {
            $tableStatus->update(['is_default' => true]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.table-statuses.form', [
            'isReservedStatus' => $this->tableStatus && in_array($this->tableStatus->key, self::RESERVED_KEYS, true),
        ]);
    }
}
