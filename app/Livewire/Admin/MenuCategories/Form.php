<?php

namespace App\Livewire\Admin\MenuCategories;

use App\Models\MenuCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ?MenuCategory $menuCategory = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $is_active = true;

    public function mount(?MenuCategory $menuCategory = null): void
    {
        $this->menuCategory = $menuCategory;

        if ($this->menuCategory) {
            $this->authorize('update', $this->menuCategory);

            $this->name = (string) $this->menuCategory->name;
            $this->slug = (string) $this->menuCategory->slug;
            $this->description = (string) ($this->menuCategory->description ?? '');
            $this->is_active = (bool) $this->menuCategory->is_active;

            return;
        }

        $this->authorize('create', MenuCategory::class);
    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['is_active'] = (bool) $this->is_active;

        if ($this->menuCategory) {
            $this->menuCategory->update($validated);
            session()->flash('success', 'Kategori berhasil diperbarui.');
        } else {
            MenuCategory::query()->create($validated);
            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        return $this->redirectRoute('menu-categories.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $slugRule = Rule::unique('menu_categories', 'slug');

        if ($this->menuCategory) {
            $slugRule = $slugRule->ignore($this->menuCategory->id);
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', $slugRule],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.menu-categories.form');
    }
}

