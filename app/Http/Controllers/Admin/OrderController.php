<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderManagementService $orders,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $statusInput = $request->query('status');
        $dateInput = $request->query('date');

        $status = OrderStatus::tryFromInput($statusInput);
        $date = is_string($dateInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput)
            ? $dateInput
            : '';

        $orders = $this->orders->paginate([
            'search' => $search,
            'status' => $status?->value,
            'date' => $date !== '' ? $date : null,
        ]);

        $hasFilters = $search !== '' || $status instanceof OrderStatus || $date !== '';

        return view('admin.orders.index', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status?->value ?? '',
            'date' => $date,
            'hasFilters' => $hasFilters,
            'statusOptions' => OrderStatus::options(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.show', [
            'order' => $order,
            'statusOptions' => OrderStatus::options(),
            'allowedTransitions' => $this->orders->allowedTransitions($order->status),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orders->updateStatus($order, $request->status());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }
}
