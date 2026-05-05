<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    /**
     * @var array<int, string>
     */
    private const METHOD_OPTIONS = ['cash', 'qris', 'debit_card', 'credit_card', 'transfer', 'ewallet'];

    /**
     * @var array<int, string>
     */
    private const STATUS_OPTIONS = ['pending', 'paid', 'failed', 'refunded'];

    public ?Payment $payment = null;

    public string $order_id = '';

    public string $method = 'cash';

    public string $type = 'full';

    public string $status = 'paid';

    public string $amount = '0';

    public string $reference = '';

    public string $paid_at = '';

    public string $notes = '';

    public function mount(?Payment $payment = null): void
    {
        $this->payment = $payment;

        if ($this->payment) {
            $this->authorize('update', $this->payment);

            $this->order_id = (string) $this->payment->order_id;
            $this->method = (string) $this->payment->method;
            $this->type = (string) $this->payment->type;
            $this->status = (string) $this->payment->status;
            $this->amount = (string) $this->payment->amount;
            $this->reference = (string) ($this->payment->reference ?? '');
            $this->paid_at = (string) ($this->payment->paid_at?->format('Y-m-d\TH:i') ?? '');
            $this->notes = (string) ($this->payment->notes ?? '');

            return;
        }

        $this->authorize('create', Payment::class);
    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $validated['paid_at'] = $validated['paid_at'] ?: ($validated['status'] === 'paid' ? now() : null);
        $validated['reference'] = $validated['reference'] ?: null;
        $validated['notes'] = $validated['notes'] ?: null;

        if ($this->payment) {
            $this->payment->update($validated);
            session()->flash('success', 'Pembayaran berhasil diperbarui.');
        } else {
            Payment::query()->create($validated);
            session()->flash('success', 'Pembayaran berhasil ditambahkan.');
        }

        return $this->redirectRoute('payments.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', Rule::in(self::METHOD_OPTIONS)],
            'type' => ['required', Rule::in(['full'])],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return Collection<int, Order>
     */
    public function orders(): Collection
    {
        return Order::query()->orderByDesc('created_at')->get();
    }

    /**
     * @return array<int, string>
     */
    public function methodOptions(): array
    {
        return self::METHOD_OPTIONS;
    }

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function render(): View
    {
        return view('livewire.admin.payments.form', [
            'orders' => $this->orders(),
            'methodOptions' => $this->methodOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }
}
