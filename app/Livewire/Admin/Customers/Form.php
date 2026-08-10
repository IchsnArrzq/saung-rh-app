<?php

namespace App\Livewire\Admin\Customers;

use App\Domains\Customer\DTO\CustomerData;
use App\Domains\Customer\UseCases\CreateCustomerUseCase;
use App\Domains\Customer\UseCases\UpdateCustomerUseCase;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?Customer $customer = null;

    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $notes = '';

    public bool $is_active = true;

    public function mount(?Customer $customer = null): void
    {
        $this->customer = $customer?->exists ? $customer : null;

        if ($this->customer) {
            $this->code = (string) ($this->customer->code ?? '');
            $this->name = (string) $this->customer->name;
            $this->phone = (string) ($this->customer->phone ?? '');
            $this->email = (string) ($this->customer->email ?? '');
            $this->address = (string) ($this->customer->address ?? '');
            $this->notes = (string) ($this->customer->notes ?? '');
            $this->is_active = (bool) $this->customer->is_active;
        }
    }

    public function save(CreateCustomerUseCase $createCustomer, UpdateCustomerUseCase $updateCustomer)
    {
        $data = CustomerData::fromValidated($this->validate($this->rules()));

        if ($this->customer) {
            $updateCustomer->handle($this->customer, $data);
            session()->flash('success', 'Pelanggan berhasil diperbarui.');
        } else {
            $createCustomer->handle($data);
            session()->flash('success', 'Pelanggan berhasil ditambahkan.');
        }

        return $this->redirectRoute('customers.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $codeRule = Rule::unique('customers', 'code');

        if ($this->customer) {
            $codeRule = $codeRule->ignore($this->customer->id);
        }

        return [
            'code' => ['nullable', 'string', 'max:40', $codeRule],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.customers.form');
    }
}
