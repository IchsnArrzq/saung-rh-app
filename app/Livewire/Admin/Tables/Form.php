<?php

namespace App\Livewire\Admin\Tables;

use App\Domains\Table\Enums\TableStatus;
use App\Models\Table as DiningTable;
use App\Models\TableCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?DiningTable $table = null;

    public string $code = '';

    public string $name = '';

    public string $capacity = '4';

    public string $status = '';

    public string $table_category_id = '';

    public string $notes = '';

    public function mount(?DiningTable $table = null): void
    {
        $this->table = $table?->exists ? $table : null;

        $this->authorizeWrite();

        if ($this->table) {

            $this->code = (string) $this->table->code;
            $this->name = (string) ($this->table->name ?? '');
            $this->capacity = (string) ($this->table->capacity ?? 4);
            $this->status = (string) ($this->table->status ?? '');
            $this->table_category_id = (string) ($this->table->table_category_id ?? '');
            $this->notes = (string) ($this->table->notes ?? '');

            return;
        }

        $this->status = TableStatus::default()->value;
    }

    /**
     * Dipanggil di mount() untuk menutup halamannya dan diulang di save():
     * mount() jalan sekali, save() adalah request HTTP tersendiri sesudahnya.
     */
    private function authorizeWrite(): void
    {
        $this->table
            ? $this->authorize('update', $this->table)
            : $this->authorize('create', DiningTable::class);
    }

    public function save()
    {
        $this->authorizeWrite();

        $validated = $this->validate($this->rules());

        $payload = [
            'code' => $validated['code'],
            'name' => $validated['name'] ?: null,
            'capacity' => (int) $validated['capacity'],
            'status' => $validated['status'],
            'table_category_id' => $validated['table_category_id'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->table) {
            $this->table->update($payload);
            session()->flash('success', 'Meja berhasil diperbarui.');
        } else {
            DiningTable::query()->create($payload);
            session()->flash('success', 'Meja berhasil ditambahkan.');
        }

        return $this->redirectRoute('tables.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $codeRule = Rule::unique('tables', 'code');

        if ($this->table) {
            $codeRule = $codeRule->ignore($this->table->id);
        }

        return [
            'code' => ['required', 'string', 'max:40', $codeRule],
            'name' => ['nullable', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(TableStatus::values())],
            'table_category_id' => ['nullable', 'exists:table_categories,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return SupportCollection<int, TableStatus>
     */
    public function statusOptions(): SupportCollection
    {
        return collect(TableStatus::cases())
            ->sortBy(fn (TableStatus $status) => $status->sortOrder())
            ->values();
    }

    /**
     * @return Collection<int, TableCategory>
     */
    public function categoryOptions(): Collection
    {
        return TableCategory::query()
            ->where(function ($query): void {
                $query->where('is_active', true);

                if ($this->table?->table_category_id) {
                    $query->orWhere('id', $this->table->table_category_id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.tables.form', [
            'statusOptions' => $this->statusOptions(),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }
}
