<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Admin\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(): View
    {
        return view('admin.orders.index', [
            'orders' => $this->orderService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.orders.create', [
            'tables' => $this->orderService->tables(),
            'menus' => $this->orderService->availableMenus(),
            'statusOptions' => $this->orderService->statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->orderService->create($request);

        return redirect()->route('orders.index')->with('success', 'Order berhasil dibuat.');
    }

    public function edit(Order $order): View
    {
        return view('admin.orders.edit', [
            'order' => $this->orderService->withItems($order),
            'tables' => $this->orderService->tables(),
            'menus' => $this->orderService->availableMenus(),
            'statusOptions' => $this->orderService->statusOptions(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->orderService->update($request, $order);

        return redirect()->route('orders.index')->with('success', 'Order berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->orderService->delete($order);

        return redirect()->route('orders.index')->with('success', 'Order berhasil dihapus.');
    }
}
