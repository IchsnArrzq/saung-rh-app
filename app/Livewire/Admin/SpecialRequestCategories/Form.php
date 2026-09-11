<?php

namespace App\Livewire\Admin\SpecialRequestCategories;

use App\Domains\Social\DTO\SpecialRequestCategoryData;
use App\Domains\Social\UseCases\SaveSpecialRequestCategoryUseCase;
use App\Models\SpecialRequestCategory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    /**
     * Ikon yang bisa dipilih — dipilih dari gambar, bukan diketik nama kelasnya.
     * Kelas Remix Icon => keterangan singkat.
     */
    public const ICONS = [
        'ri-service-line' => 'Pelayanan',
        'ri-restaurant-2-line' => 'Dapur',
        'ri-cup-line' => 'Minuman',
        'ri-cake-3-line' => 'Perayaan',
        'ri-gift-line' => 'Hadiah',
        'ri-music-2-line' => 'Musik',
        'ri-temp-cold-line' => 'Suhu ruangan',
        'ri-lightbulb-line' => 'Pencahayaan',
        'ri-brush-line' => 'Kebersihan',
        'ri-parent-line' => 'Anak',
        'ri-wheelchair-line' => 'Aksesibilitas',
        'ri-heart-pulse-line' => 'Alergi',
        'ri-bill-line' => 'Tagihan',
        'ri-wifi-line' => 'Wi-Fi',
        'ri-car-line' => 'Parkir',
        'ri-more-line' => 'Lainnya',
    ];

    public ?SpecialRequestCategory $category = null;

    public string $name = '';

    public string $icon = SpecialRequestCategory::DEFAULT_ICON;

    public string $description = '';

    public bool $is_active = true;

    /** String supaya input angka yang dikosongkan tidak memicu error tipe. */
    public string $sort_order = '';

    public function mount(?SpecialRequestCategory $category = null): void
    {
        $this->category = $category?->exists ? $category : null;

        $this->authorizeWrite();

        if ($this->category) {
            $this->name = (string) $this->category->name;
            $this->icon = $this->category->iconClass();
            $this->description = (string) ($this->category->description ?? '');
            $this->is_active = (bool) $this->category->is_active;
            $this->sort_order = (string) $this->category->sort_order;
        }
    }

    /**
     * Dipanggil di mount() untuk menutup halamannya dan diulang di save():
     * mount() jalan sekali, save() adalah request HTTP tersendiri sesudahnya.
     */
    private function authorizeWrite(): void
    {
        $this->category
            ? $this->authorize('update', $this->category)
            : $this->authorize('create', SpecialRequestCategory::class);
    }

    public function save(SaveSpecialRequestCategoryUseCase $saveCategory)
    {
        $this->authorizeWrite();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'icon' => ['required', Rule::in(array_keys(self::ICONS))],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 80 karakter.',
            'icon.required' => 'Pilih salah satu ikon.',
            'icon.in' => 'Pilih ikon dari daftar yang tersedia.',
            'description.max' => 'Keterangan maksimal 255 karakter.',
            'sort_order.integer' => 'Urutan harus berupa angka bulat.',
            'sort_order.min' => 'Urutan tidak boleh negatif.',
            'sort_order.max' => 'Urutan maksimal 9999.',
        ]);

        try {
            $saveCategory->handle(new SpecialRequestCategoryData(
                name: $validated['name'],
                icon: $validated['icon'],
                description: $validated['description'] ?? null,
                isActive: (bool) $this->is_active,
                sortOrder: ($validated['sort_order'] ?? '') === '' ? null : (int) $validated['sort_order'],
            ), $this->category);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, (string) ($messages[0] ?? ''));
            }

            return null;
        }

        session()->flash('success', $this->category ? 'Kategori diperbarui.' : 'Kategori ditambahkan.');

        return $this->redirectRoute('special-request-categories.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.special-request-categories.form', [
            'icons' => self::ICONS,
        ]);
    }
}
