<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{

    public ?Menu $menu = null;

    public string $menu_category_id = '';

    public string $name = '';

    public string $slug = '';

    public string $sku = '';

    public string $description = '';

    public string $price = '0';

    public string $image_url = '';

    public bool $is_available = true;

    public function mount(?Menu $menu = null): void
    {
        $this->menu = $menu;

        if ($this->menu) {

            $this->menu_category_id = (string) ($this->menu->menu_category_id ?? '');
            $this->name = (string) $this->menu->name;
            $this->slug = (string) $this->menu->slug;
            $this->sku = (string) ($this->menu->sku ?? '');
            $this->description = (string) ($this->menu->description ?? '');
            $this->price = (string) $this->menu->price;
            $this->image_url = (string) ($this->menu->image_url ?? '');
            $this->is_available = (bool) $this->menu->is_available;

            return;
        }

    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['menu_category_id'] = $this->menu_category_id ?: null;
        $validated['sku'] = $validated['sku'] ?: null;
        $validated['description'] = $validated['description'] ?: null;
        $validated['price'] = (float) $validated['price'];
        $validated['image_url'] = $validated['image_url'] ?: null;
        $validated['is_available'] = (bool) $this->is_available;

        if ($this->menu) {
            $this->menu->update($validated);
            session()->flash('success', 'Menu berhasil diperbarui.');
        } else {
            Menu::query()->create($validated);
            session()->flash('success', 'Menu berhasil ditambahkan.');
        }

        return $this->redirectRoute('menus.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $slugRule = Rule::unique('menus', 'slug');
        $skuRule = Rule::unique('menus', 'sku');

        if ($this->menu) {
            $slugRule = $slugRule->ignore($this->menu->id);
            $skuRule = $skuRule->ignore($this->menu->id);
        }

        return [
            'menu_category_id' => ['nullable', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', $slugRule],
            'sku' => ['nullable', 'string', 'max:60', $skuRule],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'is_available' => ['boolean'],
        ];
    }

    /**
     * @return Collection<int, MenuCategory>
     */
    public function categories(): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->when($this->menu?->menu_category_id, fn (Builder $query) => $query->orWhere('id', $this->menu->menu_category_id))
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.menus.form', [
            'categories' => $this->categories(),
        ]);
    }
}
