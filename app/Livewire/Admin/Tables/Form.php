<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table as DiningTable;
use App\Models\TableCategory;
use App\Models\TableStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{

    public ?DiningTable $table = null;

    public string $code = '';

    public string $name = '';

    public string $capacity = '4';

    public string $table_status_id = '';

    public string $table_category_id = '';

    public string $notes = '';

    public function mount(?DiningTable $table = null): void
    {
        $this->table = $table;

        if ($this->table) {

            $this->code = (string) $this->table->code;
            $this->name = (string) ($this->table->name ?? '');
            $this->capacity = (string) ($this->table->capacity ?? 4);
            $this->table_status_id = (string) ($this->table->table_status_id ?? '');
            $this->table_category_id = (string) ($this->table->table_category_id ?? '');
            $this->notes = (string) ($this->table->notes ?? '');

            return;
        }


        $defaultStatus = TableStatus::query()
            ->where('is_default', true)
            ->first()
            ?? TableStatus::query()->orderBy('sort_order')->orderBy('name')->first();

        $this->table_status_id = (string) ($defaultStatus?->id ?? '');
    }

    public function save()
    {
        $validated = $this->validate($this->rules());

        $status = TableStatus::query()->find($validated['table_status_id']);
        if (! $status) {
            throw ValidationException::withMessages([
                'table_status_id' => 'Status meja tidak valid.',
            ]);
        }

        $payload = [
            'code' => $validated['code'],
            'name' => $validated['name'] ?: null,
            'capacity' => (int) $validated['capacity'],
            'table_status_id' => $validated['table_status_id'],
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
            'table_status_id' => ['required', 'exists:table_statuses,id'],
            'table_category_id' => ['nullable', 'exists:table_categories,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return Collection<int, TableStatus>
     */
    public function statusOptions(): Collection
    {
        return TableStatus::query()
            ->where(function ($query): void {
                $query->where('is_active', true);

                if ($this->table?->table_status_id) {
                    $query->orWhere('id', $this->table->table_status_id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
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
