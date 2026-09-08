<?php

namespace App\Support;

/**
 * Menampilkan kuantitas stok seperti orang menulisnya.
 *
 * Kolom `ingredients.stock`, `min_stock`, dan kolom qty di riwayat stok adalah
 * `decimal(10,3)`, jadi `number_format($x, 3, ',', '.')` selalu mencetak tiga
 * angka di belakang koma: 15 muncul sebagai "15,000" dan terbaca seperti lima
 * belas ribu. Presisinya tetap dipertahankan — nol di ekor saja yang dibuang,
 * sehingga 15 tetap "15", 0,5 tetap "0,5", dan 1.234,25 tetap "1.234,25".
 */
class Quantity
{
    /** Sebanyak desimal yang ditampung kolomnya. */
    public const SCALE = 3;

    /** Untuk dibaca di layar: pemisah ribuan titik, desimal koma. */
    public static function format(int|float|string|null $value, int $maxDecimals = self::SCALE): string
    {
        $formatted = number_format(self::round($value, $maxDecimals), $maxDecimals, ',', '.');

        return self::trim($formatted, ',', $maxDecimals);
    }

    /** Sama, tapi selisih positif diberi tanda "+" — untuk kolom perubahan stok. */
    public static function signed(int|float|string|null $value, int $maxDecimals = self::SCALE): string
    {
        $number = self::round($value, $maxDecimals);

        return ($number > 0 ? '+' : '').self::format($number, $maxDecimals);
    }

    /**
     * Untuk `value` di `<input type="number">`: desimal titik, tanpa pemisah
     * ribuan, dan tanpa nol di ekor — kolom decimal(10,3) mengembalikan
     * "15.000" dari database, yang tampil aneh di kolom isian.
     */
    public static function input(int|float|string|null $value, int $maxDecimals = self::SCALE): string
    {
        $formatted = number_format(self::round($value, $maxDecimals), $maxDecimals, '.', '');

        return self::trim($formatted, '.', $maxDecimals);
    }

    private static function round(int|float|string|null $value, int $maxDecimals): float
    {
        return round((float) ($value ?? 0), $maxDecimals);
    }

    private static function trim(string $formatted, string $separator, int $maxDecimals): string
    {
        if ($maxDecimals > 0 && str_contains($formatted, $separator)) {
            $formatted = rtrim(rtrim($formatted, '0'), $separator);
        }

        return $formatted;
    }
}
