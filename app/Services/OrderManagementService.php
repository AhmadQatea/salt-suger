<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class OrderManagementService
{
    /**
     * Allowed next statuses for each current status.
     *
     * @var array<string, list<string>>
     */
    protected array $transitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * @param  array{search?: string, status?: string|null, date?: string|null}  $filters
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = OrderStatus::tryFromInput($filters['status'] ?? null);
        $date = $this->parseDate($filters['date'] ?? null);

        return Order::query()
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('order_number', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like);
                });
            })
            ->when($status instanceof OrderStatus, function ($query) use ($status) {
                $query->where('status', $status->value);
            })
            ->when($date instanceof Carbon, function ($query) use ($date) {
                $query->whereDate('created_at', $date->toDateString());
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<OrderStatus>
     */
    public function allowedTransitions(OrderStatus $from): array
    {
        $values = $this->transitions[$from->value] ?? [];

        return array_values(array_filter(
            array_map(fn (string $value) => OrderStatus::tryFrom($value), $values)
        ));
    }

    public function canTransition(OrderStatus $from, OrderStatus $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, $this->allowedTransitions($from), true);
    }

    /**
     * @throws ValidationException
     */
    public function updateStatus(Order $order, OrderStatus $to): Order
    {
        $from = $order->status;

        if (! $from instanceof OrderStatus) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تغيير حالة الطلب إلى الحالة المحددة.',
            ]);
        }

        if ($from === $to) {
            return $order;
        }

        if (! $this->canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تغيير حالة الطلب إلى الحالة المحددة.',
            ]);
        }

        $order->forceFill([
            'status' => $to,
        ])->save();

        Cache::forget('admin.pending_orders_count');

        return $order->refresh();
    }

    /**
     * Compact dashboard counters.
     *
     * @return array{today: int, pending: int, preparing: int, completed_today: int}
     */
    public function dashboardSummary(): array
    {
        $today = now()->toDateString();

        return [
            'today' => Order::query()->whereDate('created_at', $today)->count(),
            'pending' => Order::query()->where('status', OrderStatus::Pending)->count(),
            'preparing' => Order::query()->where('status', OrderStatus::Preparing)->count(),
            'completed_today' => Order::query()
                ->where('status', OrderStatus::Completed)
                ->whereDate('created_at', $today)
                ->count(),
        ];
    }

    public function pendingCount(): int
    {
        return Order::query()->where('status', OrderStatus::Pending)->count();
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value, config('app.timezone'))->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
