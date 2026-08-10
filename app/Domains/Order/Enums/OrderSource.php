<?php

namespace App\Domains\Order\Enums;

/**
 * Where an order came from.
 *
 * Three separate flows create orders and each stamps its own prefix into
 * `notes` ("Sumber: POS", "Sumber: DINE-IN QR", …). Those literals lived in the
 * three UseCases while the cashier screen sniffed them back out with
 * `str_contains($notes, 'QR')` — two halves of one vocabulary, free to drift.
 * Both halves now read from here.
 *
 * The source also decides how the floor reacts to an order: a counter sale only
 * claims a table nobody is sitting at, while a dine-in ticket may advance a
 * table that is already occupied.
 */
enum OrderSource: string
{
    case Pos = 'pos';
    case DineInQr = 'qr';
    case CustomerPortal = 'portal';

    public function label(): string
    {
        return match ($this) {
            self::Pos => 'POS',
            self::DineInQr => 'QR',
            self::CustomerPortal => 'App',
        };
    }

    /** The tag written into `orders.notes` so the source survives in the record. */
    public function notesTag(): string
    {
        return match ($this) {
            self::Pos => 'Sumber: POS',
            self::DineInQr => 'Sumber: DINE-IN QR',
            self::CustomerPortal => 'Sumber: CUSTOMER ORDER',
        };
    }

    /** Prefixes the note a user typed with this source's tag. */
    public function composeNotes(?string $notes): string
    {
        $notes = trim((string) $notes);

        return $notes !== '' ? $this->notesTag().' | '.$notes : $this->notesTag();
    }

    /**
     * Recover the source from a stored order note.
     *
     * Longest tag first, so "Sumber: DINE-IN QR" is not mistaken for something
     * shorter that happens to be a prefix of it.
     */
    public static function fromNotes(?string $notes): self
    {
        $notes = (string) $notes;

        foreach (self::casesByTagLength() as $case) {
            if (str_starts_with($notes, $case->notesTag())) {
                return $case;
            }
        }

        // Older rows predate the tags; the app is where orders came from before
        // the POS and QR flows existed.
        return self::CustomerPortal;
    }

    /**
     * A guest already seated may keep ordering, so a dine-in ticket advances an
     * occupied table too. The cashier is not on the floor and must not
     * overwrite what the receptionist set, so a counter sale claims free tables
     * only.
     */
    public function claimsOccupiedTable(): bool
    {
        return $this !== self::Pos;
    }

    /**
     * @return array<int, self>
     */
    private static function casesByTagLength(): array
    {
        $cases = self::cases();
        usort($cases, fn (self $a, self $b) => strlen($b->notesTag()) <=> strlen($a->notesTag()));

        return $cases;
    }
}
