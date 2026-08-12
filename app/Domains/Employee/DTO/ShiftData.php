<?php

namespace App\Domains\Employee\DTO;

/**
 * One rostered shift. Six fields, so it earns a DTO over a parameter list
 * (the old ShiftService::schedule took all six positionally).
 */
final readonly class ShiftData
{
    public function __construct(
        public string $userId,
        public string $date,
        public string $startsAt,
        public string $endsAt,
        public ?string $position = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $blankToNull = static fn (?string $value): ?string => trim((string) $value) !== '' ? trim((string) $value) : null;

        return new self(
            userId: (string) $validated['userId'],
            date: (string) $validated['shiftDate'],
            startsAt: (string) $validated['startsAt'],
            endsAt: (string) $validated['endsAt'],
            position: $blankToNull($validated['position'] ?? null),
            notes: $blankToNull($validated['notes'] ?? null),
        );
    }
}
