<?php

namespace App\Livewire\Admin\Menus;

use App\Domains\Menu\Enums\MenuAvailability;
use App\Domains\Menu\Services\MediaService;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Menu $menu = null;

    public string $menu_category_id = '';

    public string $name = '';

    public string $slug = '';

    public string $sku = '';

    public string $description = '';

    public string $price = '0';

    public string $image_url = '';

    public bool $is_available = true;

    /**
     * Staged image uploads (persisted on save). Kept separate from existing
     * media so the same form works on both the create and edit routes.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $newImages = [];

    public $newVideo = null;

    public function mount(?Menu $menu = null): void
    {
        $this->menu = $menu?->exists ? $menu : null;

        // Satu form melayani dua ability: `update` pada menu yang sudah ada,
        // `create` pada yang baru.
        $this->menu
            ? $this->authorize('update', $this->menu)
            : $this->authorize('create', Menu::class);

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

    public function updatedNewImages(): void
    {
        $this->validate(
            ['newImages.*' => ['image', 'max:4096']],
            [],
            ['newImages.*' => 'gambar'],
        );
    }

    public function updatedNewVideo(): void
    {
        $this->validate(
            ['newVideo' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200']],
            [],
            ['newVideo' => 'video'],
        );
    }

    public function removeNewImage(int $index): void
    {
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
    }

    public function removeNewVideo(): void
    {
        $this->newVideo = null;
    }

    /**
     * Delete an already-saved media item immediately (edit route only).
     */
    public function deleteMedia(string $mediaId, MediaService $mediaService): void
    {
        if (! $this->menu) {
            return;
        }

        $this->authorize('update', $this->menu);

        $media = $this->menu->media()->find($mediaId);

        if ($media) {
            $mediaService->delete($media);
            session()->flash('success', 'Media berhasil dihapus.');
        }
    }

    public function setPrimary(string $mediaId, MediaService $mediaService): void
    {
        if (! $this->menu) {
            return;
        }

        $this->authorize('update', $this->menu);

        $mediaService->setPrimaryImage($this->menu, $mediaId);
        session()->flash('success', 'Gambar utama diperbarui.');
    }

    public function save(MediaService $mediaService)
    {
        // Diulang di sini, bukan hanya di mount(): mount() dijalankan sekali
        // saat komponen pertama kali dirender, sedangkan save() adalah request
        // HTTP tersendiri sesudahnya.
        $this->menu
            ? $this->authorize('update', $this->menu)
            : $this->authorize('create', Menu::class);

        $validated = $this->validate($this->rules());
        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['menu_category_id'] = $this->menu_category_id ?: null;
        $validated['sku'] = $validated['sku'] ?: null;
        $validated['description'] = $validated['description'] ?: null;
        $validated['price'] = (float) $validated['price'];
        $validated['image_url'] = $validated['image_url'] ?: null;
        $validated['status'] = MenuAvailability::fromToggle($this->is_available)->value;

        // Media fields are handled separately, not stored on the menu row.
        unset($validated['newImages'], $validated['newVideo']);

        if ($this->menu) {
            $this->menu->update($validated);
            $menu = $this->menu;
            $message = 'Menu berhasil diperbarui.';
        } else {
            $menu = Menu::query()->create($validated);
            $message = 'Menu berhasil ditambahkan.';
        }

        foreach ($this->newImages as $file) {
            $mediaService->addImage($menu, $file);
        }

        if ($this->newVideo) {
            $mediaService->addVideo($menu, $this->newVideo);
        }

        $this->newImages = [];
        $this->newVideo = null;

        session()->flash('success', $message);

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
            'newImages' => ['nullable', 'array'],
            'newImages.*' => ['image', 'max:4096'],
            'newVideo' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
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
            'existingImages' => $this->menu ? $this->menu->images()->get() : collect(),
            'existingVideos' => $this->menu ? $this->menu->videos()->get() : collect(),
        ]);
    }
}
