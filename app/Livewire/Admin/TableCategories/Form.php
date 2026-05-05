<?php

namespace App\Livewire\Admin\TableCategories;

use App\Models\TableCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ?TableCategory $tableCategory = null;

    public string $name = '';

    public string $slug = '';

    public string $sort_order = '0';

    public bool $is_active = true;

    public string $description = '';

    public function mount(?TableCategory $tableCategory = null): void
    {
        $this->tableCategory = $tableCategory;

        if ($this->tableCategory) {
            $this->authorize('update', $this->tableCategory);

            $this->name = (string) $this->tableCategory->name;
            $this->slug = (string) $this->tableCategory->slug;
            $this->sort_order = (string) $this->tableCategory->sort_order;
            $this->is_active = (bool) $this->tableCategory->is_active;
            $this->description = (string) ($this->tableCategory->description ?? '');

            return;
        }

        $this->authorize('create', TableCategory::class);
    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['sort_order'] = (int) $validated['sort_order'];
        $validated['is_active'] = (bool) $this->is_active;
        $validated['description'] = $validated['description'] ?: null;

        if ($this->tableCategory) {
            $this->tableCategory->update($validated);
            session()->flash('success', 'Kategori meja berhasil diperbarui.');
        } else {
            TableCategory::query()->create($validated);
            session()->flash('success', 'Kategori meja berhasil ditambahkan.');
        }

        return $this->redirectRoute('table-categories.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $slugRule = Rule::unique('table_categories', 'slug');

        if ($this->tableCategory) {
            $slugRule = $slugRule->ignore($this->tableCategory->id);
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', $slugRule],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.table-categories.form');
    }
}
