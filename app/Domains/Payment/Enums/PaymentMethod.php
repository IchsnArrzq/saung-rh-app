<?php

namespace App\Domains\Payment\Enums;

/**
 * How the guest paid.
 *
 * The Indonesian labels used to be hardcoded twice — once implicitly in the
 * admin form's option list, once as a literal map at the top of
 * livewire/pos/table-bills.blade.php. Both now read from here.
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qris = 'qris';
    case DebitCard = 'debit_card';
    case CreditCard = 'credit_card';
    case Transfer = 'transfer';
    case Ewallet = 'ewallet';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Qris => 'QRIS',
            self::DebitCard => 'Kartu Debit',
            self::CreditCard => 'Kartu Kredit',
            self::Transfer => 'Transfer',
            self::Ewallet => 'E-Wallet',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Cash is settled at the counter; the rest clear through a provider. */
    public function isCash(): bool
    {
        return $this === self::Cash;
    }
}
