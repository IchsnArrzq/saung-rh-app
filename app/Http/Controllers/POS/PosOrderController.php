<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Dua layar kasir. Keduanya cuma pembungkus Blade — isinya Livewire
 * (`Pos\OrderCard` dan `Pos\TableBills`), sesuai aturan controller tipis.
 */
class PosOrderController extends Controller
{
    /**
     * Layar transaksi kasir: pilih menu, susun keranjang, buat pesanan.
     */
    public function index(): View
    {
        return view('pos.order.index');
    }

    /**
     * Daftar kerja kasir: tagihan dine-in yang masih menunggu pelunasan.
     */
    public function bills(): View
    {
        return view('pos.bills');
    }
}
