<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?Supplier $supplier = null;

    public string $code = '';

    public string $name = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $notes = '';

    public bool $is_active = true;

    public function mount(?Supplier $supplier = null): void
    {
        $this->supplier = $supplier?->exists ? $supplier : null;

        $this->authorizeWrite();

        if ($this->supplier) {
            $this->code = (string) ($this->supplier->code ?? '');
            $this->name = (string) $this->supplier->name;
            $this->contact_person = (string) ($this->supplier->contact_person ?? '');
            $this->phone = (string) ($this->supplier->phone ?? '');
            $this->email = (string) ($this->supplier->email ?? '');
            $this->address = (string) ($this->supplier->address ?? '');
            $this->notes = (string) ($this->supplier->notes ?? '');
            $this->is_active = (bool) $this->supplier->is_active;
        }
    }

    /**
     * Dipanggil di mount() untuk menutup halamannya dan diulang di save():
     * mount() jalan sekali, save() adalah request HTTP tersendiri sesudahnya.
     */
    private function authorizeWrite(): void
    {
        $this->supplier
            ? $this->authorize('update', $this->supplier)
            : $this->authorize('create', Supplier::class);
    }

    public function save()
    {
        $this->authorizeWrite();

        $validated = $this->validate($this->rules());

        $validated['code'] = $validated['code'] ?: null;
        $validated['contact_person'] = $validated['contact_person'] ?: null;
        $validated['phone'] = $validated['phone'] ?: null;
        $validated['email'] = $validated['email'] ?: null;
        $validated['address'] = $validated['address'] ?: null;
        $validated['notes'] = $validated['notes'] ?: null;
        $validated['is_active'] = (bool) $this->is_active;

        if ($this->supplier) {
            $this->supplier->update($validated);
            session()->flash('success', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::query()->create($validated);
            session()->flash('success', 'Supplier berhasil ditambahkan.');
        }

        return $this->redirectRoute('suppliers.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $codeRule = Rule::unique('suppliers', 'code');

        if ($this->supplier) {
            $codeRule = $codeRule->ignore($this->supplier->id);
        }

        return [
            'code' => ['nullable', 'string', 'max:40', $codeRule],
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.form');
    }
}
