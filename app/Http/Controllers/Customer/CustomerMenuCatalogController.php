<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\OrderCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerMenuCatalogController extends Controller
{
    public function __construct(private readonly OrderCartService $orderCartService) {}

    public function tables(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        return view('customer.menus.tables', [
            'search' => $search,
            ...$this->orderCartService->tableSelectionData($search),
        ]);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $tableId = $request->string('table_id')->toString();

        if ($tableId === '') {
            return redirect()->route('customer.menus.tables');
        }

        $search = trim($request->string('search')->toString());

        return view('customer.menus.index', [
            'search' => $search,
            ...$this->orderCartService->menuCatalogData($tableId, $search),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $this->orderCartService->addToCart($request);

        $tableId = $request->string('table_id')->toString();

        return redirect()
            ->route('customer.menus.index', ['table_id' => $tableId])
            ->with('success', 'Menu ditambahkan ke cart.');
    }

    public function cart(Request $request): View|RedirectResponse
    {
        $tableId = $request->string('table_id')->toString();

        if ($tableId === '') {
            return redirect()->route('customer.menus.tables');
        }

        return view('customer.menus.cart', $this->orderCartService->cartData($tableId));
    }

    public function updateCart(Request $request, string $menuId): RedirectResponse
    {
        $this->orderCartService->updateCartItem($request, $menuId);

        $tableId = $request->string('table_id')->toString();

        return redirect()
            ->route('customer.menus.cart.index', ['table_id' => $tableId])
            ->with('success', 'Item cart diperbarui.');
    }

    public function removeCart(Request $request, string $menuId): RedirectResponse
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
        ]);
        $tableId = (string) $validated['table_id'];

        $this->orderCartService->removeCartItem($tableId, $menuId);

        return redirect()
            ->route('customer.menus.cart.index', ['table_id' => $tableId])
            ->with('success', 'Item cart dihapus.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $order = $this->orderCartService->checkout($request);

        return redirect()
            ->route('customer.dashboard')
            ->with('success', 'Pesanan berhasil dibuat dengan nomor '.$order->order_number.'.');
    }
}
