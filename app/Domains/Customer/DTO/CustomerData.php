<?php

namespace App\Domains\Customer\DTO;

/**
 * Customer master-data payload, shared by create and update. Seven fields, so
 * it earns a DTO rather than a parameter list (AGENTS.md § DTO).
 *
 * Empty optional strings are normalised to null here — one place, instead of
 * the six `?: null` lines that used to sit in the Livewire form.
 */
final readonly class CustomerData
{
    public function __construct(
        public string $name,
        public ?string $code = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $notes = null,
        public bool $isActive = true,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $blankToNull = static fn (?string $value): ?string => trim((string) $value) !== '' ? trim((string) $value) : null;

        return new self(
            name: trim((string) $validated['name']),
            code: $blankToNull($validated['code'] ?? null),
            phone: $blankToNull($validated['phone'] ?? null),
            email: $blankToNull($validated['email'] ?? null),
            address: $blankToNull($validated['address'] ?? null),
            notes: $blankToNull($validated['notes'] ?? null),
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
        ];
    }
}
