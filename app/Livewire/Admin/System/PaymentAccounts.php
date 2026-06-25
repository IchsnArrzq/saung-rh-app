<?php

namespace App\Livewire\Admin\System;

use App\Models\PaymentAccount;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class PaymentAccounts extends Component
{
    public ?string $editingId = null;

    public bool $showForm = false;

    // Form fields
    public string $label = '';

    public string $type = 'bank';

    public string $provider = '';

    public string $account_number = '';

    public string $account_holder = '';

    public string $instructions = '';

    public bool $is_active = true;

    public function create(): void
    {
        $this->reset(['editingId', 'label', 'provider', 'account_number', 'account_holder', 'instructions']);
        $this->type = 'bank';
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(string $id): void
    {
        $account = PaymentAccount::query()->findOrFail($id);

        $this->editingId = $account->id;
        $this->label = $account->label;
        $this->type = $account->type;
        $this->provider = (string) $account->provider;
        $this->account_number = (string) $account->account_number;
        $this->account_holder = (string) $account->account_holder;
        $this->instructions = (string) $account->instructions;
        $this->is_active = $account->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'label' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(array_keys(PaymentAccount::TYPES))],
            'provider' => ['nullable', 'string', 'max:80'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'account_holder' => ['nullable', 'string', 'max:120'],
            'instructions' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        PaymentAccount::query()->updateOrCreate(
            ['id' => $this->editingId],
            $data,
        );

        session()->flash('success', $this->editingId ? 'Akun pembayaran diperbarui.' : 'Akun pembayaran ditambahkan.');
        $this->showForm = false;
    }

    public function toggle(string $id): void
    {
        $account = PaymentAccount::query()->findOrFail($id);
        $account->update(['is_active' => ! $account->is_active]);
    }

    public function delete(string $id): void
    {
        PaymentAccount::query()->whereKey($id)->delete();
        session()->flash('success', 'Akun pembayaran dihapus.');
    }

    public function render(): View
    {
        return view('livewire.admin.system.payment-accounts', [
            'accounts' => PaymentAccount::query()->orderBy('sort_order')->orderBy('label')->get(),
            'types' => PaymentAccount::TYPES,
        ]);
    }
}
