<?php

namespace App\Livewire\Admin\Reservations;

use App\Models\Reservation;
use App\Models\Table;
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
    private const STATUS_OPTIONS = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

    public ?Reservation $reservation = null;

    public string $table_id = '';

    public string $customer_name = '';

    public string $phone = '';

    public string $pax = '1';

    public string $reservation_at = '';

    public string $status = 'pending';

    public string $notes = '';

    public function mount(?Reservation $reservation = null): void
    {
        $this->reservation = $reservation;

        if ($this->reservation) {
            $this->authorize('update', $this->reservation);

            $this->table_id = (string) ($this->reservation->table_id ?? '');
            $this->customer_name = (string) $this->reservation->customer_name;
            $this->phone = (string) ($this->reservation->phone ?? '');
            $this->pax = (string) $this->reservation->pax;
            $this->reservation_at = (string) ($this->reservation->reservation_at?->format('Y-m-d\TH:i') ?? '');
            $this->status = (string) ($this->reservation->status ?: 'pending');
            $this->notes = (string) ($this->reservation->notes ?? '');

            return;
        }

        $this->authorize('create', Reservation::class);
        $this->reservation_at = now()->addHour()->format('Y-m-d\TH:i');
    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $validated['table_id'] = $validated['table_id'] ?: null;
        $validated['phone'] = $validated['phone'] ?: null;
        $validated['notes'] = $validated['notes'] ?: null;

        if ($this->reservation) {
            $this->reservation->update($validated);
            session()->flash('success', 'Reservasi berhasil diperbarui.');
        } else {
            Reservation::query()->create($validated);
            session()->flash('success', 'Reservasi berhasil ditambahkan.');
        }

        return $this->redirectRoute('reservations.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pax' => ['required', 'integer', 'min:1'],
            'reservation_at' => ['required', 'date'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return Collection<int, Table>
     */
    public function tables(): Collection
    {
        return Table::query()->orderBy('code')->get();
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
        return view('livewire.admin.reservations.form', [
            'tables' => $this->tables(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }
}
