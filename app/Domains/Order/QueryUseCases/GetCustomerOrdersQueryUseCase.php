<?php

namespace App\Domains\Order\QueryUseCases;

use App\Domains\Order\Repositories\OrderRepository;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Auth;

/**
 * Pesanan milik satu akun pelanggan: yang sedang berjalan, dan riwayatnya.
 *
 * Tinggal di domain Order — bukan di domain Customer — supaya portal pelanggan
 * membacanya lewat QueryUseCase alih-alih menjangkau OrderRepository dari
 * domain sebelah, kesalahan yang sudah ditandai `@todo Fase D` di
 * GetCustomerDashboardQueryUseCase (ARCHITECTURE.md § Domain Dependencies).
 */
class GetCustomerOrdersQueryUseCase
{
    public function __construct(private readonly OrderRepository $orders) {}

    /**
     * Pesanan yang masih berjalan — dikonfirmasi sampai disajikan.
     *
     * @return Collection<int, Order>
     */
    public function active(?string $customerId = null): Collection
    {
        $customerId ??= (string) Auth::id();

        return $customerId === ''
            ? new Collection
            : $this->orders->activeForCustomer($customerId);
    }

    /**
     * Seluruh riwayat, terbaru dulu.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function history(?string $customerId = null, int $perPage = 10): LengthAwarePaginator
    {
        $customerId ??= (string) Auth::id();

        // Tamu tanpa akun tidak punya riwayat; paginator kosong menjaga view
        // tetap bisa memanggil links() tanpa cabang khusus.
        return $customerId === ''
            ? new Paginator([], 0, $perPage)
            : $this->orders->paginateForCustomer($customerId, $perPage);
    }
}
