<?php

namespace App\Livewire\Admin\MenuStatuses;

use App\Models\MenuStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    /**
     * @var array<int, string>
     */
    private const RESERVED_KEYS = ['available', 'unavailable', 'sold_out', 'seasonal'];

    public ?MenuStatus $menuStatus = null;

    public string $name = '';

    public string $key = '';

    public string $color = 'neutral';

    public string $sort_order = '0';

    public bool $is_active = true;

    public bool $is_default = false;

    public function mount(?MenuStatus $menuStatus = null): void
    {
        $this->menuStatus = $menuStatus;

        if ($this->menuStatus) {
            $this->name = (string) $this->menuStatus->name;
            $this->key = (string) $this->menuStatus->key;
            $this->color = (string) ($this->menuStatus->color ?: 'neutral');
            $this->sort_order = (string) $this->menuStatus->sort_order;
            $this->is_active = (bool) $this->menuStatus->is_active;
            $this->is_default = (bool) $this->menuStatus->is_default;

            return;
        }
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

        if ($this->menuStatus
            && in_array($this->menuStatus->key, self::RESERVED_KEYS, true)
            && $normalizedKey !== $this->menuStatus->key
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

        if ($this->menuStatus) {
            $this->menuStatus->update($payload);
            $status = $this->menuStatus;
            session()->flash('success', 'Status menu berhasil diperbarui.');
        } else {
            $status = MenuStatus::query()->create($payload);
            session()->flash('success', 'Status menu berhasil ditambahkan.');
        }

        $this->syncDefaultFlag($status, $payload['is_default']);

        return $this->redirectRoute('menu-statuses.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $keyRule = Rule::unique('menu_statuses', 'key');

        if ($this->menuStatus) {
            $keyRule = $keyRule->ignore($this->menuStatus->id);
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

    private function syncDefaultFlag(MenuStatus $menuStatus, bool $isDefault): void
    {
        if ($isDefault) {
            MenuStatus::query()
                ->where('id', '!=', $menuStatus->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            if (! $menuStatus->is_default) {
                $menuStatus->update(['is_default' => true]);
            }

            return;
        }

        $hasDefault = MenuStatus::query()->where('is_default', true)->exists();

        if (! $hasDefault) {
            $menuStatus->update(['is_default' => true]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.menu-statuses.form', [
            'isReservedStatus' => $this->menuStatus && in_array($this->menuStatus->key, self::RESERVED_KEYS, true),
        ]);
    }
}
