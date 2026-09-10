<?php

namespace App\Domains\System\QueryUseCases;

use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\System\Repositories\PaymentAccountRepository;
use App\Models\PaymentAccount;
use Illuminate\Database\Eloquent\Collection;

/**
 * Rekening tujuan yang perlu dibacakan ke tamu untuk sebuah metode bayar.
 *
 * `PaymentMethod` cuma menyebut *caranya* membayar; nomor rekening, atas nama,
 * dan instruksinya ada di tabel `payment_accounts` yang diisi admin. Tanpa
 * jembatan ini kasir bisa memilih "Transfer" tapi tidak punya nomor rekening
 * yang bisa disebutkan.
 */
class GetPaymentAccountsQueryUseCase
{
    /**
     * Metode bayar → tipe rekening yang bisa menerimanya (kunci
     * `PaymentAccount::TYPES`).
     *
     * Metode yang tidak terdaftar di sini memang tidak punya rekening tujuan:
     * tunai diselesaikan di meja kasir, kartu debit/kredit lewat mesin EDC.
     */
    private const ACCOUNT_TYPES = [
        PaymentMethod::Transfer->value => ['bank'],
        PaymentMethod::Qris->value => ['qris'],
        PaymentMethod::Ewallet->value => ['ewallet'],
    ];

    public function __construct(private readonly PaymentAccountRepository $accounts) {}

    /**
     * Apakah metode ini memang butuh rekening tujuan. Dipakai untuk memisahkan
     * "tidak perlu rekening" (tunai) dari "perlu, tapi admin belum mengisinya"
     * — dua keadaan yang tampak sama kalau cuma daftar kosong yang dilihat.
     */
    public function expectsAccount(PaymentMethod|string|null $method): bool
    {
        return $this->typesFor($method) !== [];
    }

    /**
     * @return Collection<int, PaymentAccount>
     */
    public function forMethod(PaymentMethod|string|null $method): Collection
    {
        $types = $this->typesFor($method);

        return $types === []
            ? new Collection
            : $this->accounts->activeOfTypes($types);
    }

    /**
     * @return array<int, string>
     */
    private function typesFor(PaymentMethod|string|null $method): array
    {
        $value = $method instanceof PaymentMethod ? $method->value : (string) $method;

        return self::ACCOUNT_TYPES[$value] ?? [];
    }
}
